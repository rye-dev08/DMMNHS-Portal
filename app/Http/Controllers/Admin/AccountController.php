<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherApproval;
use App\Models\User;
use App\Rules\PasswordPolicy;
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
            $advisoryClass = trim((string) ($validated['advisory_class'] ?? ''));

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
            $profile->fill([
                'advisory_class' => $validated['advisory_class'] ?? null,
                'max_students' => $validated['max_students'] ?? 0,
                'max_subjects' => $validated['max_subjects'] ?? 0,
            ]);
            $profile->save();
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

        if ($user->role === 'admin' && $user->status === 'active' && $this->activeAdminCount() <= 1) {
            flash_notice('Cannot deactivate the last active admin.', 'error');

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

        flash_notice("Password reset for {$user->name}.", 'success');

        return redirect()->route('admin.accounts.edit', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            flash_notice('You cannot delete your own account.', 'error');

            return redirect()->route('admin.accounts');
        }

        if ($user->role === 'admin' && $this->activeAdminCount() <= 1) {
            flash_notice('Cannot delete the last active admin.', 'error');

            return redirect()->route('admin.accounts');
        }

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

        flash_notice('User deleted', 'success');

        return redirect()->route('admin.accounts');
    }

    private function activeAdminCount(): int
    {
        return DB::table('users')->where('role', 'admin')->where('status', 'active')->count();
    }
}
