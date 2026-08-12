<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarEvent;
use App\Models\Announcement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $settings = Setting::find(1);
        $period = $settings?->period();

        $systemStatus = (object) [
            'online' => true,
            'school_year' => $period?->school_year,
            'term' => $period?->term,
            'phase' => $period?->phase ?? Setting::PHASE_NONE,
            'enrollment' => match ($period?->phase ?? Setting::PHASE_NONE) {
                Setting::PHASE_ENROLLMENT => 'Open for Enrollment',
                Setting::PHASE_CLOSED => 'Enrollment Closed',
                default => 'Not in Enrollment Phase',
            },
        ];

        $stats = (object) [
            'students' => DB::table('users')->where('role', 'student')->where('status', 'active')->count(),
            'teachers' => DB::table('users')->where('role', 'teacher')->where('status', 'active')->count(),
            'programs' => DB::table('subjects')->distinct()->count('subject_name'),
            'announcements' => Announcement::where('status', Announcement::STATUS_PUBLISHED)->count(),
            'requirements' => RequirementSubmission::count(),
        ];

        $year = (string) ($period?->school_year ?? '');
        $term = (int) ($period?->term ?? 1);

        $announcements = Announcement::query()
            ->where('status', Announcement::STATUS_PUBLISHED)
            ->where('school_year', $year)
            ->where('term', $term)
            ->whereDate('publish_date', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', now()->toDateString());
            })
            ->orderByDesc('publish_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $upcomingEvents = AcademicCalendarEvent::query()
            ->where('school_year', $year)
            ->where('term', $term)
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->limit(4)
            ->get();

        return view('landing', [
            'systemStatus' => $systemStatus,
            'stats' => $stats,
            'announcements' => $announcements,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
