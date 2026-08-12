<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendarEvent;
use App\Models\ContactMessage;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Services\AnnouncementService;
use App\Services\GradeSubmissionMonitorService;
use App\Services\ImportantDatesService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $period = Setting::find(1)?->period();

        $stats = (object) [
            'requirements' => Requirement::query()
                ->where('school_year', (string) ($period->school_year ?? ''))
                ->where('term', (int) ($period->term ?? 1))
                ->where('status', Requirement::STATUS_ACTIVE)
                ->count(),
            'pendingSubmissions' => RequirementSubmission::query()
                ->whereIn('status', [
                    RequirementSubmission::STATUS_SUBMITTED,
                    RequirementSubmission::STATUS_RESUBMITTED,
                ])
                ->count(),
            'upcomingEvents' => AcademicCalendarEvent::query()
                ->where('school_year', (string) ($period->school_year ?? ''))
                ->whereDate('event_date', '>=', now()->toDateString())
                ->count(),
            'pendingMessages' => ContactMessage::query()
                ->where('status', ContactMessage::STATUS_PENDING)
                ->whereNull('archived_at')
                ->count(),
        ];

        $feed = app(AnnouncementService::class)->feed(auth()->user());

        return view('office.dashboard', [
            'stats' => $stats,
            'gradeSummary' => app(GradeSubmissionMonitorService::class)->summary(),
            'importantDates' => app(ImportantDatesService::class)->forUser(auth()->user()),
            'announcements' => $feed['items'],
            'announcementUnread' => $feed['unreadCount'],
        ]);
    }
}
