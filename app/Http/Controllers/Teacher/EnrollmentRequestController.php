<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Setting;
use App\Models\Teacher;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentRequestController extends Controller
{
    private function teacherId(): int
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        return (int) ($teacher->id ?? 0);
    }

    private function teacherName(): string
    {
        return auth()->user()->name ?? '';
    }

    public function index(): View
    {
        $teacherId = $this->teacherId();

        $requests = EnrollmentRequest::where('teacher_id', $teacherId)
            ->join('students', 'students.id', '=', 'enrollment_requests.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->select('enrollment_requests.*', 'students.id as student_id', 'users.name as student_name')
            ->orderByRaw("enrollment_requests.status = 'pending' DESC")
            ->orderByDesc('enrollment_requests.date_requested')
            ->get();

        $pendingCount = EnrollmentRequest::where('teacher_id', $teacherId)
            ->where('status', 'pending')
            ->count();

        return view('teacher.enrollment_requests', [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function approve(Request $request): RedirectResponse
    {
        $teacherId = $this->teacherId();
        $requestId = (int) $request->integer('request_id');

        try {
            $cap = \DB::selectOne(
                'SELECT COALESCE(ta.max_students, t.max_students, 30) as max_limit
                 FROM teachers t
                 LEFT JOIN teacher_approval ta ON ta.teacher_id = t.id AND ta.status = \'approved\'
                 WHERE t.id = ?
                 LIMIT 1',
                [$teacherId]
            );
            $limit = (int) ($cap->max_limit ?? 30);

            $current = \DB::table('enrollment_requests')
                ->where('teacher_id', $teacherId)
                ->where('status', 'approved')
                ->count();

            if ($current >= $limit) {
                flash_notice('Class full! Cannot approve.', 'error');

                return redirect()->route('teacher.enrollment-requests');
            }

            $updated = EnrollmentRequest::where('id', $requestId)
                ->where('teacher_id', $teacherId)
                ->where('status', 'pending')
                ->update(['status' => 'approved']);

            if ($updated === 0) {
                flash_notice('This enrollment request is no longer pending.', 'info');

                return redirect()->route('teacher.enrollment-requests');
            }

            $enrollment = EnrollmentRequest::where('id', $requestId)->first();
            $studentId = (int) ($enrollment->student_id ?? 0);

            if ($studentId === 0) {
                flash_notice('Could not get student ID.', 'error');

                return redirect()->route('teacher.enrollment-requests');
            }

            $teacherSubjects = \DB::table('teacher_subjects')->where('teacher_id', $teacherId)->get()
                ->map(function ($item) {
                    return (array) $item;
                });

            foreach ($teacherSubjects as $ts) {
                $exists = \DB::table('subjects')
                    ->where('student_id', $studentId)
                    ->where('teacher_id', $teacherId)
                    ->where('subject_name', $ts['subject_name'])
                    ->exists();

                if (! $exists) {
                    \DB::table('subjects')->insert([
                        'teacher_id' => $teacherId,
                        'student_id' => $studentId,
                        'subject_name' => $ts['subject_name'],
                        'course_code' => $ts['course_code'],
                        'teacher_code' => $ts['teacher_code'],
                        'room_no' => $ts['room_no'],
                    ]);
                }
            }

            $service = app(NotificationService::class);
            $schoolYear = (string) (Setting::find(1)->current_school_year ?? '');
            $service->enrollmentApproved($studentId, $this->teacherName(), $schoolYear);

            flash_notice($teacherSubjects->isEmpty()
                ? 'Enrollment approved. No teacher subjects to apply yet.'
                : 'Approved and subjects auto-applied!', 'success');
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to approve the enrollment request. Please try again.', 'error');
        }

        return redirect()->route('teacher.enrollment-requests');
    }

    public function reject(Request $request): RedirectResponse
    {
        $teacherId = $this->teacherId();
        $requestId = (int) $request->integer('request_id');

        try {
            $updated = EnrollmentRequest::where('id', $requestId)
                ->where('teacher_id', $teacherId)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            if ($updated === 0) {
                flash_notice('This enrollment request is no longer pending.', 'info');

                return redirect()->route('teacher.enrollment-requests');
            }

            $enrollment = EnrollmentRequest::where('id', $requestId)->first();
            $studentId = (int) ($enrollment->student_id ?? 0);

            if ($studentId > 0) {
                app(NotificationService::class)->enrollmentRejected($studentId, $this->teacherName());
            }

            flash_notice('Enrollment request rejected.', 'success');
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to reject the enrollment request. Please try again.', 'error');
        }

        return redirect()->route('teacher.enrollment-requests');
    }
}
