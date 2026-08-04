<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = (object) [
            'students' => DB::table('users')->where('role', 'student')->where('status', 'active')->count(),
            'teachers' => DB::table('users')->where('role', 'teacher')->where('status', 'active')->count(),
            'admins' => DB::table('users')->where('role', 'admin')->where('status', 'active')->count(),
            'pendingEnrollments' => DB::table('enrollment_requests')->where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}