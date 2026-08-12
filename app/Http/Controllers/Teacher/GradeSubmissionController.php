<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\GradeSubmissionMonitorService;
use Illuminate\View\View;

class GradeSubmissionController extends Controller
{
    public function index(): View
    {
        $service = app(GradeSubmissionMonitorService::class);
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $units = $service->units(['teacher' => (int) auth()->id()]);
        $completion = $service->completion($units);
        $submitted = $units->where('status', GradeSubmissionMonitorService::STATUS_SUBMITTED)->count();
        $pending = $units->where('status', GradeSubmissionMonitorService::STATUS_PENDING)->count();
        $late = $units->where('status', GradeSubmissionMonitorService::STATUS_LATE)->count();

        return view('teacher.grade_submissions', [
            'teacher' => $teacher,
            'units' => $units,
            'completion' => $completion,
            'submitted' => $submitted,
            'pending' => $pending,
            'late' => $late,
            'school_year' => $service->defaultSchoolYear(),
            'term' => $service->defaultTerm(),
        ]);
    }
}
