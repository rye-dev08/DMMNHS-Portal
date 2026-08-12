<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Teacher;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradeController extends Controller
{
    private function teacherId(): int
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        return (int) ($teacher->id ?? 0);
    }

    public function index(): View
    {
        $teacherId = $this->teacherId();

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

        return view('teacher.submit_grades', [
            'students' => $students,
            'teacherId' => $teacherId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacherId = $this->teacherId();

        $studentId = (int) $request->integer('student_id');
        $subjectId = (int) $request->integer('subject_id');
        $grade = trim((string) $request->string('grade'));
        $remarks = trim((string) $request->string('remarks'));

        $ownsSubject = DB::table('subjects')
            ->where('id', $subjectId)
            ->where('teacher_id', $teacherId)
            ->where('student_id', $studentId)
            ->exists();

        if (! $ownsSubject) {
            flash_notice('You can only submit grades for a subject assigned to this student under your advisory.', 'error');

            return redirect()->route('teacher.submit-grades');
        }

        if ($grade === '') {
            $grade = 'N/A';
        } elseif (! is_numeric($grade)) {
            flash_notice('Grade must be a number between 0 and 100, or N/A.', 'error');

            return redirect()->route('teacher.submit-grades')->withInput();
        } else {
            $numeric = (int) $grade;
            if ($numeric > 100 || $numeric < 0) {
                flash_notice('Grade must be between 0 and 100. The value was not saved.', 'error');

                return redirect()->route('teacher.submit-grades')->withInput();
            }
            $grade = (string) $numeric;
        }

        try {
            $quarter = 'Term '.(int) (Setting::find(1)->current_term ?? 1);

            Grade::updateOrCreate(
                ['student_id' => $studentId, 'subject_id' => $subjectId, 'quarter' => $quarter],
                ['grade' => $grade, 'remarks' => $remarks, 'date_submitted' => now()]
            );

            $studentName = DB::table('students as s')
                ->join('users as u', 'u.id', '=', 's.user_id')
                ->where('s.id', $studentId)
                ->value('u.name') ?? '';

            $subjectName = DB::table('subjects')->where('id', $subjectId)->value('subject_name') ?? '';

            $service = app(NotificationService::class);
            $term = (int) (Setting::find(1)->current_term ?? 1);
            $quarter = 'Term '.$term;

            $service->gradeSubmitted($studentId, $subjectName, $grade, $term);
            $service->syncGradeCompletion($studentId);

            // Notify the teacher once every assigned student's grade for this
            // subject unit is in, so the "complete" message isn't sent after a
            // single grade save (and the teacher user id, not teachers.id, is
            // passed to the notification service).
            $schoolYear = (string) (Setting::find(1)->current_school_year ?? '');
            $assigned = (int) DB::table('subjects')
                ->where('teacher_id', $teacherId)
                ->where('subject_name', $subjectName)
                ->distinct()
                ->count('student_id');
            $graded = (int) DB::table('grades as g')
                ->join('subjects as s', 's.id', '=', 'g.subject_id')
                ->where('s.teacher_id', $teacherId)
                ->where('s.subject_name', $subjectName)
                ->where('g.quarter', $quarter)
                ->distinct()
                ->count('g.student_id');

            if ($assigned > 0 && $graded >= $assigned) {
                $service->gradeSubmissionCompleted((int) auth()->id(), $subjectName, $term, $schoolYear);
            }
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to save the grade. Please try again.', 'error');

            return redirect()->route('teacher.submit-grades')->withInput();
        }

        \Log::info("Grade saved by teacher {$teacherId} for student {$studentName} - {$subjectName}");
        flash_notice("Grade saved for {$studentName} - {$subjectName}", 'success');

        return redirect()->route('teacher.submit-grades');
    }

    public function getSubjects(Request $request): JsonResponse
    {
        $studentId = (int) $request->integer('student_id');
        $teacherId = $this->teacherId();

        if ($studentId <= 0 || $teacherId <= 0) {
            return response()->json(['error' => 'Missing student_id or teacher_id'], 400);
        }

        $ownsStudent = DB::table('enrollment_requests')
            ->where('student_id', $studentId)
            ->where('teacher_id', $teacherId)
            ->where('status', 'approved')
            ->exists();

        if (! $ownsStudent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $subjects = DB::table('subjects as s')
            ->selectRaw(
                's.id, s.subject_name as name, '.
                'COALESCE((SELECT g2.grade FROM grades g2 WHERE g2.subject_id = s.id AND g2.student_id = ? ORDER BY g2.date_submitted DESC LIMIT 1), \'N/A\') as current_grade',
                [$studentId]
            )
            ->where('s.teacher_id', $teacherId)
            ->where('s.student_id', $studentId)
            ->orderBy('s.subject_name')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'current_grade' => $row->current_grade,
                ];
            });

        return response()->json($subjects);
    }
}
