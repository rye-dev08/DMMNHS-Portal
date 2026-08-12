<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherApproval;
use App\Models\User;
use App\Rules\PasswordPolicy;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = DB::table('users as u')
            ->leftJoin('students as s', 's.user_id', '=', 'u.id')
            ->leftJoin('teachers as t', 't.user_id', '=', 'u.id')
            ->select(
                'u.id',
                'u.name',
                'u.username',
                'u.email',
                'u.role',
                'u.status',
                's.grade_level',
                't.advisory_class',
                DB::raw('(SELECT MAX(t2.advisory_class) FROM enrollment_requests er JOIN teachers t2 ON t2.id = er.teacher_id WHERE er.student_id = s.id AND er.status = \'approved\') as section')
            );

        if ($role !== 'all') {
            $query->where('u.role', $role);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                    ->orWhere('u.username', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('u.role')->orderBy('u.name')->paginate(15)->withQueryString();

        return view('admin.accounts', [
            'users' => $users,
            'role' => $role,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.create_account');
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = $validated['role'];
        $status = 'active';
        $advisoryClass = trim((string) ($validated['advisory_class'] ?? ''));

        if ($role === 'teacher' && $advisoryClass !== '' && $this->advisoryClassExists($advisoryClass)) {
            return back()->withErrors([
                'advisory_class' => "Advisory class '{$advisoryClass}' already exists.",
            ])->withInput();
        }

        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'role' => $role,
                'status' => $status,
            ]);

            if ($role === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'sex' => $validated['sex'] ?? null,
                    'birthday' => $validated['birthday'] ?? null,
                    'age' => $validated['age'] ?? null,
                    'grade_level' => $validated['grade_level'] ?? null,
                    'status' => 'active',
                ]);
            }

            if ($role === 'teacher') {
                $settings = Setting::find(1);
                $maxStudents = (int) ($validated['max_students'] ?? $settings->max_students_per_class ?? 30);
                $maxSubjects = (int) ($validated['max_subjects'] ?? $settings->max_subjects_per_teacher ?? 8);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'advisory_class' => $advisoryClass !== '' ? $advisoryClass : null,
                    'max_subjects' => $maxSubjects,
                    'max_students' => $maxStudents,
                    'status' => 'active',
                ]);

                TeacherApproval::create([
                    'teacher_id' => $teacher->id,
                    'max_students' => $maxStudents,
                    'max_subjects' => $maxSubjects,
                    'status' => 'approved',
                ]);
            }

            // Welcome message goes out once at account creation, not on every
            // login. Admins never receive notification emails.
            if (in_array($role, ['student', 'teacher'], true)) {
                app(NotificationService::class)->welcomeEmail($user);
            }
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to create the account. The username may already exist.', 'error');

            return back()->withInput();
        }

        flash_notice('User created', 'success');

        return redirect()->route('admin.accounts');
    }

    public function edit(User $user): View
    {
        $user->load(['student', 'teacher']);

        return view('admin.edit_account', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->role === 'student') {
            $profile = Student::firstOrNew(['user_id' => $user->id]);
            $profile->fill([
                'sex' => $validated['sex'] ?? null,
                'birthday' => $validated['birthday'] ?? null,
                'age' => $validated['age'] ?? null,
                'grade_level' => $validated['grade_level'] ?? null,
            ]);
            $profile->save();
        }

        if ($user->role === 'teacher') {
            $profile = Teacher::firstOrNew(['user_id' => $user->id]);
            $advisoryClass = trim((string) ($validated['advisory_class'] ?? ''));

            if ($advisoryClass !== '' && $this->advisoryClassExists($advisoryClass, $user->id)) {
                return back()->withErrors([
                    'advisory_class' => "Advisory class '{$advisoryClass}' already exists.",
                ])->withInput();
            }

            $profile->fill([
                'advisory_class' => $advisoryClass !== '' ? $advisoryClass : null,
                'max_students' => $validated['max_students'] ?? $profile->max_students,
                'max_subjects' => $validated['max_subjects'] ?? $profile->max_subjects,
            ]);
            $profile->save();
        }

        if ($user->id !== auth()->id()) {
            app(NotificationService::class)->accountUpdated($user);
        }

        flash_notice('Account updated', 'success');

        return redirect()->route('admin.accounts.edit', $user);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            flash_notice('You cannot deactivate your own account.', 'error');

            return redirect()->route('admin.accounts');
        }

        if ($user->isSystemAdmin() && $user->status === 'active' && $this->activeSystemAdminCount() <= 1) {
            flash_notice('Cannot deactivate the last active system administrator.', 'error');

            return redirect()->route('admin.accounts');
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        flash_notice("Account is now {$user->status}.", 'success');

        return redirect()->route('admin.accounts');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'confirmed', new PasswordPolicy],
        ]);

        $user->update(['password_hash' => Hash::make($validated['password'])]);

        $studentId = (int) DB::table('students')->where('user_id', $user->id)->value('id');
        if ($studentId > 0) {
            app(NotificationService::class)->passwordReset($studentId);
        }

        flash_notice("Password reset for {$user->name}.", 'success');

        return redirect()->route('admin.accounts.edit', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            flash_notice('You cannot delete your own account.', 'error');

            return redirect()->route('admin.accounts');
        }

        if ($user->isSystemAdmin() && $this->activeSystemAdminCount() <= 1) {
            flash_notice('Cannot delete the last active system administrator.', 'error');

            return redirect()->route('admin.accounts');
        }

        try {
            DB::transaction(function () use ($user) {
                $userId = $user->id;

                $studentId = (int) DB::table('students')->where('user_id', $userId)->value('id');
                $teacherId = (int) DB::table('teachers')->where('user_id', $userId)->value('id');

                if ($studentId > 0) {
                    DB::table('grades')->where('student_id', $studentId)->delete();
                    DB::table('subjects')->where('student_id', $studentId)->delete();
                    DB::table('enrollment_requests')->where('student_id', $studentId)->delete();
                    DB::table('assessment_scores')->where('student_id', $studentId)->delete();
                    DB::table('previous_term_grades')->where('student_id', $studentId)->delete();
                    DB::table('previous_term_subjects')->where('student_id', $studentId)->delete();
                }

                if ($teacherId > 0) {
                    $teacherSubjectIds = DB::table('subjects')->where('teacher_id', $teacherId)->pluck('id');
                    if ($teacherSubjectIds->isNotEmpty()) {
                        DB::table('grades')->whereIn('subject_id', $teacherSubjectIds)->delete();
                    }
                    DB::table('subjects')->where('teacher_id', $teacherId)->delete();
                    DB::table('enrollment_requests')->where('teacher_id', $teacherId)->delete();
                    DB::table('teacher_approval')->where('teacher_id', $teacherId)->delete();
                    DB::table('assessment_scores')->where('teacher_id', $teacherId)->delete();
                    DB::table('previous_term_subjects')->where('teacher_id', $teacherId)->delete();
                    DB::table('teacher_subjects')->where('teacher_id', $teacherId)->delete();
                }

                DB::table('students')->where('user_id', $userId)->delete();
                DB::table('teachers')->where('user_id', $userId)->delete();
                DB::table('graduated_students')->where('user_id', $userId)->delete();
                DB::table('sessions')->where('user_id', $userId)->delete();
                DB::table('users')->where('id', $userId)->delete();
            });
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to delete the account. Please try again.', 'error');

            return redirect()->route('admin.accounts');
        }

        flash_notice('User deleted', 'success');

        return redirect()->route('admin.accounts');
    }

    private function activeSystemAdminCount(): int
    {
        return DB::table('users')->where('role', 'system_admin')->where('status', 'active')->count();
    }

    private function advisoryClassExists(string $advisoryClass, ?int $excludeUserId = null): bool
    {
        // Extract section from advisory_class format "Grade X-Section (Track)".
        // Section names are unique across ALL grade levels, so only the
        // section part matters for the duplicate check.
        if (preg_match('/^Grade\s+(\d+)-(.+?)\s*(\(.+\))?$/i', $advisoryClass, $matches)) {
            $section = trim($matches[2]);

            return TeacherAssignmentController::sectionExists($section, $excludeUserId);
        }

        // Fallback for non-standard format - exact match (case-insensitive, trimmed)
        $normalized = function_exists('mb_strtolower') ? mb_strtolower(trim($advisoryClass)) : strtolower(trim($advisoryClass));

        $query = DB::table('teachers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->whereRaw('LOWER(t.advisory_class) = ?', [$normalized])
            ->whereNotNull('t.advisory_class');

        if ($excludeUserId !== null) {
            $query->where('u.id', '!=', $excludeUserId);
        }

        return $query->exists();
    }
}
