<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradesOverviewController extends Controller
{
    public function index(): View
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $teacherId = (int) ($teacher->id ?? 0);

        $subjects = DB::table('subjects')
            ->where('teacher_id', $teacherId)
            ->selectRaw('subject_name, course_code, MIN(id) as id')
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

        $studentsData = [];
        foreach ($students as $student) {
            $grades = [];
            foreach ($subjects as $subject) {
                $grade = DB::table('grades')
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->orderByDesc('date_submitted')
                    ->value('grade') ?? 'N/A';
                $grades[$subject->id] = $grade;
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