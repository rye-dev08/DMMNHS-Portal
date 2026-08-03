<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(): View
    {
        $student = Student::where('user_id', auth()->id())->first();
        $studentId = (int) ($student->id ?? 0);

        $currentSem = (int) (Setting::find(1)->current_semester ?? 1);
        $quarter = 'Sem ' . $currentSem;

        $teacherIds = EnrollmentRequest::where('student_id', $studentId)
            ->where('status', 'approved')
            ->pluck('teacher_id');

        $subjects = $teacherIds->isEmpty()
            ? collect()
            : Subject::whereIn('teacher_id', $teacherIds)->orderBy('subject_name')->get();

        $rows = [];
        $total = 0;
        $count = 0;

        foreach ($subjects as $subject) {
            $grade = Grade::where('student_id', $studentId)
                ->where('subject_id', $subject->id)
                ->where('quarter', $quarter)
                ->orderByDesc('date_submitted')
                ->first();

            $gradeValue = $grade->grade ?? 'N/A';
            $remarks = $grade->remarks ?? '';

            if (is_numeric($gradeValue)) {
                $total += (float) $gradeValue;
                $count++;
            }

            $rows[] = (object) [
                'subject_name' => $subject->subject_name,
                'grade' => $gradeValue,
                'remarks' => $remarks,
            ];
        }

        $gwa = $count > 0 ? round($total / $count, 2) : null;

        return view('student.grades', [
            'rows' => $rows,
            'currentSem' => $currentSem,
            'gwa' => $gwa,
        ]);
    }
}