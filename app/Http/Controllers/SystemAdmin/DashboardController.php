<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = (object) [
            'students' => DB::table('users')->where('role', 'student')->where('status', 'active')->count(),
            'teachers' => DB::table('users')->where('role', 'teacher')->where('status', 'active')->count(),
            'officeAdmins' => DB::table('users')->where('role', 'office_admin')->where('status', 'active')->count(),
            'pendingEnrollments' => DB::table('enrollment_requests')->where('status', 'pending')->count(),
        ];

        $feed = app(AnnouncementService::class)->feed(auth()->user());

        return view('admin.dashboard', [
            'stats' => $stats,
            'announcements' => $feed['items'],
            'announcementUnread' => $feed['unreadCount'],
        ]);
    }
}
