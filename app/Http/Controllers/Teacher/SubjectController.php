<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $teacherId = (int) ($teacher->id ?? 0);

        $subjects = TeacherSubject::where('teacher_id', $teacherId)->orderBy('subject_name')->get();
        $approvedCount = DB::table('enrollment_requests')
            ->where('teacher_id', $teacherId)
            ->where('status', 'approved')
            ->distinct()
            ->count('student_id');

        $isSHS = false;
        if ($teacher && $teacher->advisory_class) {
            $parsed = TeacherAssignmentController::parseAdvisory($teacher->advisory_class);
            $isSHS = $parsed && $parsed['grade'] >= 11;
        }

        return view('teacher.advisory_portal', [
            'subjects' => $subjects,
            'approvedCount' => $approvedCount,
            'isSHS' => $isSHS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $teacherId = (int) ($teacher->id ?? 0);

        $subjectName = trim((string) $request->string('subject_name'));
        $courseCode = trim((string) $request->string('course_code'));
        $teacherCode = trim((string) $request->string('teacher_code'));
        $roomNo = trim((string) $request->string('room_no'));
        $subjectType = trim((string) $request->string('subject_type', 'Major'));

        if ($subjectType !== 'Applied' && $subjectType !== 'Major') {
            $subjectType = 'Major';
        }

        if ($subjectName === '') {
            flash_notice('Subject name required.', 'error');

            return redirect()->route('teacher.advisory-portal')->withInput();
        }

        $duplicate = TeacherSubject::where('teacher_id', $teacherId)
            ->where(function ($query) use ($subjectName, $courseCode) {
                $query->where('subject_name', $subjectName);

                if ($courseCode !== '') {
                    $query->orWhere('course_code', $courseCode);
                }
            })
            ->exists();

        if ($duplicate) {
            flash_notice('Cannot add subject: same name or course code already exists.', 'error');

            return redirect()->route('teacher.advisory-portal')->withInput();
        }

        try {
            TeacherSubject::create([
                'teacher_id' => $teacherId,
                'subject_name' => $subjectName,
                'course_code' => $courseCode,
                'teacher_code' => $teacherCode,
                'room_no' => $roomNo,
                'subject_type' => $subjectType,
            ]);
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to add the subject. Please try again.', 'error');

            return redirect()->route('teacher.advisory-portal')->withInput();
        }

        // Auto apply to all currently approved students.
        $studentIds = DB::table('enrollment_requests')
            ->where('teacher_id', $teacherId)
            ->where('status', 'approved')
            ->distinct()
            ->pluck('student_id');

        $applied = 0;
        $appliedStudentIds = [];
        $service = app(NotificationService::class);

        foreach ($studentIds as $studentId) {
            $exists = DB::table('subjects')
                ->where('student_id', $studentId)
                ->where('teacher_id', $teacherId)
                ->where('subject_name', $subjectName)
                ->exists();

            if (! $exists) {
                DB::table('subjects')->insert([
                    'teacher_id' => $teacherId,
                    'student_id' => $studentId,
                    'subject_name' => $subjectName,
                    'course_code' => $courseCode,
                    'teacher_code' => $teacherCode,
                    'room_no' => $roomNo,
                    'subject_type' => $subjectType,
                ]);
                $applied++;
                $appliedStudentIds[] = (int) $studentId;
            }
        }

        foreach ($appliedStudentIds as $studentId) {
            $service->subjectAdded($studentId, $subjectName);
            $service->syncGradeCompletion($studentId);
        }

        flash_notice("Subject '{$subjectName}' added & applied to {$applied} students.", 'success');

        return redirect()->route('teacher.advisory-portal');
    }

    public function destroy(TeacherSubject $subject): RedirectResponse
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $teacherId = (int) ($teacher->id ?? 0);

        if ($subject->teacher_id !== $teacherId) {
            flash_notice('Cannot delete subject.', 'error');

            return redirect()->route('teacher.advisory-portal');
        }

        $hasGrades = DB::table('grades as g')
            ->join('subjects as s', 'g.subject_id', '=', 's.id')
            ->where('s.teacher_id', $teacherId)
            ->where('s.subject_name', $subject->subject_name)
            ->exists();

        if ($hasGrades) {
            flash_notice('Cannot delete subject: one or more students already have grades for this subject.', 'error');

            return redirect()->route('teacher.advisory-portal');
        }

        $subjectName = $subject->subject_name;
        $affectedStudentIds = DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->where('subject_name', $subjectName)
            ->pluck('student_id');

        $subject->delete();
        DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->where('subject_name', $subjectName)
            ->delete();

        $service = app(NotificationService::class);
        foreach ($affectedStudentIds as $studentId) {
            $service->subjectRemoved((int) $studentId, $subjectName);
            $service->syncGradeCompletion((int) $studentId);
        }

        flash_notice("Subject '{$subjectName}' deleted safely.", 'success');

        return redirect()->route('teacher.advisory-portal');
    }
}
