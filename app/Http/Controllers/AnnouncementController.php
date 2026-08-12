<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $feed = app(AnnouncementService::class)->feed(auth()->user());

        return view('announcements.index', [
            'announcements' => $feed['items'],
            'unreadCount' => $feed['unreadCount'],
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $announcement = Announcement::findOrFail((int) $request->integer('id'));

        $service = app(AnnouncementService::class);

        // Only allow marking announcements the user can actually see, so an
        // announcement targeted elsewhere (or not yet published) can't be
        // marked read by guessing its id.
        if (! $service->isVisibleFor($announcement, $user)) {
            return response()->json([
                'ok' => false,
                'unread' => $service->unreadCount($user),
            ], 422);
        }

        $service->markRead($announcement, $user);

        return response()->json([
            'ok' => true,
            'unread' => $service->unreadCount($user),
        ]);
    }
}
