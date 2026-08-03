<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentInfoController extends Controller
{
    public function index(): View
    {
        $user_id = auth()->id();

        $student = Student::where('user_id', $user_id)->with('user')->first();

        if ($student) {
            $hasProfile = true;
            $info = $student;
        } else {
            $hasProfile = false;
            $info = (object) [
                'name' => auth()->user()->name,
                'sex' => 'N/A',
                'birthday' => 'N/A',
                'age' => 'N/A',
                'grade_level' => 'N/A',
                'username' => 'N/A',
                'email' => 'N/A',
                'status' => 'N/A',
            ];
        }

        $advisory = DB::selectOne(
            'SELECT tu.name AS teacher_name, t.advisory_class, t.max_subjects
             FROM enrollment_requests er
             JOIN students s ON s.id = er.student_id
             JOIN teachers t ON t.id = er.teacher_id
             JOIN users tu ON tu.id = t.user_id
             WHERE s.user_id = ? AND er.status = \'approved\'
             ORDER BY er.id DESC
             LIMIT 1',
            [$user_id]
        );

        $advisory = $advisory ?: (object) ['teacher_name' => 'N/A', 'advisory_class' => 'N/A', 'max_subjects' => 'N/A'];

        if ($advisory->advisory_class === '' || $advisory->advisory_class === null) {
            $advisory->advisory_class = 'Not set';
        }
        if ((int) $advisory->max_subjects <= 0) {
            $advisory->max_subjects = 'Not set';
        }

        return view('student.student_info', [
            'student' => $info,
            'hasProfile' => $hasProfile,
            'advisory' => $advisory,
        ]);
    }
}