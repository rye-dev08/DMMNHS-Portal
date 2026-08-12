<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read and navigate to the page it refers to.
     */
    public function open(Request $request, string $notification): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();

        $link = $notification->data['link'] ?? null;

        return redirect($link ?: route('notifications.index'));
    }

    public function readAll(): RedirectResponse
    {
        try {
            $count = auth()->user()->unreadNotifications()->update(['read_at' => now()]);

            if ($count > 0) {
                flash_notice('All notifications marked as read.', 'success');
            }
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to update notifications. Please try again.', 'error');
        }

        return redirect()->back();
    }
}
