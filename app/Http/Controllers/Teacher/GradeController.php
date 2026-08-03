<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Teacher;
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

        if ($grade === '') {
            $grade = 'N/A';
        } else {
            $numeric = (int) $grade;
            $grade = $numeric > 100 ? 100 : ($numeric < 0 ? 0 : $grade);
        }

        $quarter = 'Sem ' . (int) (Setting::find(1)->current_semester ?? 1);

        Grade::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId, 'quarter' => $quarter],
            ['grade' => $grade, 'remarks' => $remarks, 'date_submitted' => now()]
        );

        $studentName = DB::table('students as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('s.id', $studentId)
            ->value('u.name') ?? '';

        $subjectName = DB::table('subjects')->where('id', $subjectId)->value('subject_name') ?? '';

        \Log::info("Grade saved by teacher {$teacherId} for student {$studentName} - {$subjectName}");
        flash_notice("Grade saved for {$studentName} - {$subjectName}", 'success');

        return redirect()->route('teacher.submit-grades');
    }

    public function getSubjects(Request $request): JsonResponse
    {
        $studentId = (int) $request->integer('student_id');
        $teacherId = (int) $request->integer('teacher_id');

        if ($studentId <= 0 || $teacherId <= 0) {
            return response()->json(['error' => 'Missing student_id or teacher_id'], 400);
        }

        $subjects = DB::table('subjects as s')
            ->selectRaw(
                's.id, s.subject_name as name, ' .
                'COALESCE((SELECT g2.grade FROM grades g2 WHERE g2.subject_id = s.id AND g2.student_id = ? ORDER BY g2.date_submitted DESC LIMIT 1), \'N/A\') as current_grade',
                [$studentId]
            )
            ->where('s.teacher_id', $teacherId)
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