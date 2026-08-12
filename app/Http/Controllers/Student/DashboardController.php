<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementService;
use App\Services\ImportantDatesService;
use App\Services\StudentTimelineService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $feed = app(AnnouncementService::class)->feed(auth()->user());

        return view('student.dashboard', [
            'announcements' => $feed['items'],
            'announcementUnread' => $feed['unreadCount'],
            'importantDates' => app(ImportantDatesService::class)->forUser(auth()->user()),
            'recentTimeline' => app(StudentTimelineService::class)->recent(auth()->user(), 5),
        ]);
    }
}
