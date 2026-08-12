<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class StudentInfoController extends Controller
{
    public function index(): View
    {
        $user_id = auth()->id();

        $student = Student::where('user_id', $user_id)->with('user')->first();

        if ($student) {
            $hasProfile = true;
            $info = $student;
        } else {
            $hasProfile = false;
            $user = User::find($user_id);
            $info = (object) [
                'name' => $user->name ?? 'N/A',
                'sex' => 'N/A',
                'birthday' => 'N/A',
                'age' => 'N/A',
                'grade_level' => 'N/A',
                'username' => $user->username ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'status' => $user->status ?? 'N/A',
            ];
        }

        $advisory = DB::selectOne(
            'SELECT tu.name AS teacher_name, t.advisory_class, t.max_subjects
             FROM enrollment_requests er
             JOIN students s ON s.id = er.student_id
             JOIN teachers t ON t.id = er.teacher_id
             JOIN users tu ON tu.id = t.user_id
             WHERE s.user_id = ? AND er.status = \'approved\'
             ORDER BY er.id DESC
             LIMIT 1',
            [$user_id]
        );

        $advisory = $advisory ?: (object) ['teacher_name' => 'N/A', 'advisory_class' => 'N/A', 'max_subjects' => 'N/A'];

        if ($advisory->advisory_class === '' || $advisory->advisory_class === null) {
            $advisory->advisory_class = 'Not set';
        }
        if ((int) $advisory->max_subjects <= 0) {
            $advisory->max_subjects = 'Not set';
        }

        return view('student.student_info', [
            'student' => $info,
            'hasProfile' => $hasProfile,
            'advisory' => $advisory,
        ]);
    }

    public function edit(): View
    {
        $student = Student::where('user_id', auth()->id())->with('user')->first();

        return view('student.edit_info', ['student' => $student]);
    }

    public function update(Request $request): RedirectResponse
    {
        $student = Student::where('user_id', auth()->id())->first();
        $user = User::findOrFail(auth()->id());

        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'sex' => ['nullable', 'in:M,F'],
            'birthday' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:1', 'max:99'],
        ])->validate();

        try {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($student) {
                $student->update([
                    'sex' => $validated['sex'] ?? null,
                    'birthday' => $validated['birthday'] ?? null,
                    'age' => $validated['age'] ?? null,
                ]);
            }

            app(NotificationService::class)->profileUpdated($user);

            flash_notice('Your personal information has been updated.', 'success');
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to update your information. Please try again.', 'error');
        }

        return redirect()->route('student.info');
    }
}
