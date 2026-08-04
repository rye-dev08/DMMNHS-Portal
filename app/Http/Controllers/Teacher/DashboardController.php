<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $teacherProfileId = (int) ($teacher->id ?? 0);

        $advisory = $teacher ? ($teacher->advisory_class ?? '') : '';
        $subjectsCount = \DB::table('subjects')->where('teacher_id', $teacherProfileId)->count();
        $approvedCount = \DB::table('enrollment_requests')
            ->where('teacher_id', $teacherProfileId)
            ->where('status', 'approved')
            ->count();

        return view('teacher.dashboard', [
            'advisory' => $advisory,
            'subjectsCount' => $subjectsCount,
            'approvedCount' => $approvedCount,
        ]);
    }
}