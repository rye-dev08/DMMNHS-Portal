<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (! $student) {
            flash_notice('Student profile not found. Contact admin.', 'error');

            return redirect()->route('student.dashboard');
        }

        $studentId = (int) $student->id;
        $studentGrade = (int) $student->grade_level;
        $isGraduateOrInactive = $studentGrade >= 13 || $student->status !== 'active';

        // Use teacher's grade_level column directly
        $teachers = DB::table('teachers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('teacher_approval as ta', function ($join) {
                $join->on('ta.teacher_id', '=', 't.id')->where('ta.status', '=', 'approved');
            })
            ->where('t.status', 'active')
            ->where('u.status', 'active')
            ->where('u.role', 'teacher')
            ->whereNotNull('t.grade_level')
            ->where('t.grade_level', $studentGrade)
            ->select('t.id', 'u.name', 't.advisory_class')
            ->orderBy('u.name')
            ->get();

        $requests = DB::table('enrollment_requests as er')
            ->join('teachers as t', 'er.teacher_id', '=', 't.id')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('er.student_id', $studentId)
            ->select('u.name', 'er.status', 'er.date_requested')
            ->orderByDesc('er.date_requested')
            ->get();

        $latestRequest = $requests->first();
        $enrollmentState = 'none';
        if ($latestRequest) {
            if ($latestRequest->status === 'approved') {
                $enrollmentState = 'approved';
            } elseif ($latestRequest->status === 'pending') {
                $enrollmentState = 'pending';
            } elseif ($latestRequest->status === 'rejected') {
                $enrollmentState = 'rejected';
            }
        }

        return view('student.enrollment_request', [
            'isGraduateOrInactive' => $isGraduateOrInactive,
            'teachers' => $teachers,
            'requests' => $requests,
            'enrollmentState' => $enrollmentState,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (! $student) {
            flash_notice('Student profile not found. Contact admin.', 'error');

            return redirect()->route('student.dashboard');
        }

        $studentId = (int) $student->id;
        $teacherId = (int) $request->integer('teacher_id');
        $message = '';

        if ($student->needs_reenrollment === 'yes') {
            $student->needs_reenrollment = 'no';
            $student->save();
        }

        if ((int) $student->grade_level >= 13 || $student->status !== 'active') {
            $message = 'Cannot enroll. Graduated or inactive.';
        } else {
            $existing = EnrollmentRequest::where('student_id', $studentId)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($existing) {
                $message = 'Already enrolled/pending. Wait for approval.';
            }

            if ($message === '' && $teacherId > 0) {
                // Verify teacher has grade_level matching student's grade
                $teacherGrade = DB::table('teachers')
                    ->where('id', $teacherId)
                    ->whereNotNull('grade_level')
                    ->where('grade_level', (int) $student->grade_level)
                    ->exists();

                if (! $teacherGrade) {
                    $message = 'Invalid teacher for your grade level.';
                }
            }

            if ($message === '' && $teacherId > 0) {
                $cap = DB::selectOne(
                    'SELECT COALESCE(ta.max_students, t.max_students, 30) as `limit`
                     FROM teachers t
                     LEFT JOIN teacher_approval ta ON ta.teacher_id = t.id AND ta.status = \'approved\'
                     WHERE t.id = ? AND t.status = \'active\'
                     LIMIT 1',
                    [$teacherId]
                );
                $limit = (int) ($cap->limit ?? 30);

                $approvedCount = EnrollmentRequest::where('teacher_id', $teacherId)
                    ->where('status', 'approved')
                    ->count();

                if ($approvedCount < $limit) {
                    EnrollmentRequest::create([
                        'student_id' => $studentId,
                        'teacher_id' => $teacherId,
                        'status' => 'pending',
                        'date_requested' => now(),
                    ]);

                    app(NotificationService::class)->enrollmentRequested(
                        $teacherId,
                        auth()->user()->name ?? 'A student'
                    );

                    app(NotificationService::class)->enrollmentSubmitted(
                        $studentId,
                        auth()->user()->name ?? 'A student'
                    );

                    $message = 'Enrollment request sent! Wait for teacher approval.';
                } else {
                    $message = "Class full (max {$limit}). Try another teacher.";
                }
            } elseif ($message === '') {
                $message = 'Select a teacher.';
            }
        }

        $type = $message === 'Enrollment request sent! Wait for teacher approval.' ? 'success' : 'error';
        flash_notice($message, $type);

        return redirect()->route('student.enrollment');
    }
}
