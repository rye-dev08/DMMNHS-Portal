<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementService;
use App\Services\ImportantDatesService;
use App\Services\TeacherWorkloadService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $feed = app(AnnouncementService::class)->feed($user);
        $workload = app(TeacherWorkloadService::class)->forUser($user);

        return view('teacher.dashboard', [
            'teacher' => $workload->teacher,
            'advisory' => (string) ($workload->teacher?->advisory_class ?? ''),
            'workload' => $workload,
            'importantDates' => app(ImportantDatesService::class)->forUser($user),
            'announcements' => $feed['items'],
            'announcementUnread' => $feed['unreadCount'],
        ]);
    }
}
