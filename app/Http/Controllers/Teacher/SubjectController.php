<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherSubject;
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

        return view('teacher.advisory_portal', [
            'subjects' => $subjects,
            'approvedCount' => $approvedCount,
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

        if ($subjectName === '') {
            flash_notice('Subject name required.', 'error');

            return redirect()->route('teacher.advisory-portal');
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

            return redirect()->route('teacher.advisory-portal');
        }

        TeacherSubject::create([
            'teacher_id' => $teacherId,
            'subject_name' => $subjectName,
            'course_code' => $courseCode,
            'teacher_code' => $teacherCode,
            'room_no' => $roomNo,
        ]);

        // Auto apply to all currently approved students.
        $studentIds = DB::table('enrollment_requests')
            ->where('teacher_id', $teacherId)
            ->where('status', 'approved')
            ->distinct()
            ->pluck('student_id');

        $applied = 0;
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
                ]);
                $applied++;
            }
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
        $subject->delete();
        DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->where('subject_name', $subjectName)
            ->delete();
        flash_notice("Subject '{$subjectName}' deleted safely.", 'success');

        return redirect()->route('teacher.advisory-portal');
    }
}