<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentRequest;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $student = Student::where('user_id', auth()->id())->first();
        $studentId = (int) ($student->id ?? 0);

        $period = Setting::find(1)->period();
        $currentTerm = $period->term;

        // Archive term selector (year + term), only archived (past) terms.
        $archivedPeriods = DB::table('previous_term_grades')
            ->where('student_id', $studentId)
            ->selectRaw('archived_school_year as y, archived_term as t')
            ->distinct()
            ->orderByDesc('archived_school_year')
            ->orderByDesc('archived_term')
            ->get();

        $selectedYear = (string) $request->string('year');
        $selectedTerm = (int) $request->integer('term');

        $viewingHistory = $selectedTerm > 0 && $selectedYear !== '';

        if ($viewingHistory) {
            // Load from the archive for the selected past term.
            $rows = DB::table('previous_term_grades as g')
                ->join('previous_term_subjects as s', 's.original_subject_id', '=', 'g.subject_id')
                ->where('g.student_id', $studentId)
                ->where('s.student_id', $studentId)
                ->where('g.archived_term', $selectedTerm)
                ->where('g.archived_school_year', $selectedYear)
                ->select('s.subject_name', 'g.grade', 'g.remarks')
                ->orderBy('s.subject_name')
                ->get();

            $currentTerm = $selectedTerm;
        } else {
            // Current (live) term.
            $quarter = 'Term ' . $currentTerm;

            $teacherIds = EnrollmentRequest::where('student_id', $studentId)
                ->where('status', 'approved')
                ->pluck('teacher_id');

            $subjects = $teacherIds->isEmpty()
                ? collect()
                : Subject::where('student_id', $studentId)
                    ->whereIn('teacher_id', $teacherIds)
                    ->orderBy('subject_name')
                    ->get();

            $rows = [];
            foreach ($subjects as $subject) {
                $grade = Grade::where('student_id', $studentId)
                    ->where('subject_id', $subject->id)
                    ->where('quarter', $quarter)
                    ->orderByDesc('date_submitted')
                    ->first();

                $rows[] = (object) [
                    'subject_name' => $subject->subject_name,
                    'grade' => $grade->grade ?? 'N/A',
                    'remarks' => $grade->remarks ?? '',
                ];
            }
        }

        $total = 0;
        $count = 0;
        foreach ($rows as $row) {
            if (is_numeric($row->grade)) {
                $total += (float) $row->grade;
                $count++;
            }
        }

        $gwa = $count > 0 ? round($total / $count, 2) : null;

        return view('student.grades', [
            'rows' => $rows,
            'currentTerm' => $viewingHistory ? (int) ($selectedTerm) : $period->term,
            'schoolYear' => $viewingHistory ? $selectedYear : (string) $period->school_year,
            'viewingHistory' => $viewingHistory,
            'archivedPeriods' => $archivedPeriods,
            'selectedTerm' => $selectedTerm,
            'selectedYear' => $selectedYear,
            'gwa' => $gwa,
        ]);
    }
}