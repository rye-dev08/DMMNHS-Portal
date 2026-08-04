<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradesOverviewController extends Controller
{
    public function index(): View
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $teacherId = (int) ($teacher->id ?? 0);

        $quarter = 'Term ' . (int) (Setting::find(1)->current_term ?? 1);

        // Column headers: unique subjects the teacher teaches.
        $subjects = DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->selectRaw('subject_name, course_code')
            ->groupBy('subject_name', 'course_code')
            ->orderBy('subject_name')
            ->get();

        $students = DB::table('students as s')
            ->join('enrollment_requests as er', function ($join) {
                $join->on('er.student_id', '=', 's.id')->where('er.status', '=', 'approved');
            })
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('er.teacher_id', $teacherId)
            ->select('s.id', 'u.name')
            ->distinct()
            ->orderBy('u.name')
            ->get();

        // For each student, they own a dedicated subject row, so map grade by subject_name.
        $studentsData = [];
        foreach ($students as $student) {
            $grades = [];
            $studentSubjects = DB::table('subjects')
                ->where('teacher_id', $teacherId)
                ->where('student_id', $student->id)
                ->get();

            foreach ($studentSubjects as $subjectRow) {
                $grade = DB::table('grades')
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subjectRow->id)
                    ->where('quarter', $quarter)
                    ->orderByDesc('date_submitted')
                    ->value('grade') ?? 'N/A';
                $grades[$subjectRow->subject_name] = $grade;
            }

            $studentsData[$student->id] = [
                'name' => $student->name,
                'grades' => $grades,
            ];
        }

        return view('teacher.grades_overview', [
            'subjects' => $subjects,
            'studentsData' => $studentsData,
        ]);
    }
}