<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $student = Student::where('user_id', auth()->id())->first();
        $studentId = (int) ($student->id ?? 0);

        $currentTerm = (int) (Setting::find(1)->current_term ?? 1);
        $schedule = [];

        if ($studentId > 0) {
            $schedule = DB::table('subjects as s')
                ->join('teachers as t', 't.id', '=', 's.teacher_id')
                ->join('users as u', 'u.id', '=', 't.user_id')
                ->where('s.student_id', $studentId)
                ->select('s.subject_name', 's.course_code', 's.teacher_code', 's.room_no',
                    'u.name as teacher_name')
                ->orderBy('s.subject_name')
                ->get();
        }

        return view('student.class_schedule', [
            'schedule' => $schedule,
            'currentTerm' => $currentTerm,
        ]);
    }
}