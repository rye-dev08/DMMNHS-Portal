<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\MessageSenderBlock;
use App\Services\AnnouncementService;
use App\Services\MessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessageCenterController extends Controller
{
    public function __construct(private readonly MessageService $service) {}

    public function index(Request $request): View
    {
        $this->service->pruneExpiredInvalid();

        $status = (string) $request->input('status', '');
        $role = (string) $request->input('role', '');
        $dateFrom = (string) $request->input('date_from', '');
        $dateTo = (string) $request->input('date_to', '');
        $q = (string) $request->input('q', '');
        $blockedOnly = $request->boolean('blocked');

        $query = ContactMessage::with('user.student', 'user.teacher')
            ->whereNull('archived_at');

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($role !== '') {
            $query->where('sender_role', $role);
        }
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%')
                    ->orWhere('subject', 'like', '%'.$q.'%')
                    ->orWhere('message', 'like', '%'.$q.'%');
            });
        }
        if ($blockedOnly) {
            $query->whereHas('user', fn ($user) => $user->whereIn(
                'id',
                MessageSenderBlock::query()->whereNull('unblocked_at')->select('user_id')
            ));
        }

        $messages = $query->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'valid' THEN 1 WHEN 'invalid' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $blockedSenders = MessageSenderBlock::query()
            ->with('user')
            ->whereNull('unblocked_at')
            ->count();

        $blockedUserIds = MessageSenderBlock::query()
            ->whereNull('unblocked_at')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $summary = (object) [
            'pending' => ContactMessage::where('status', ContactMessage::STATUS_PENDING)->whereNull('archived_at')->count(),
            'valid' => ContactMessage::where('status', ContactMessage::STATUS_VALID)->whereNull('archived_at')->count(),
            'invalid' => ContactMessage::where('status', ContactMessage::STATUS_INVALID)->whereNull('archived_at')->count(),
            'blockedSenders' => $blockedSenders,
        ];

        return view('office.message_center', [
            'messages' => $messages,
            'summary' => $summary,
            'status' => $status,
            'role' => $role,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'q' => $q,
            'blockedOnly' => $blockedOnly,
            'blockedUserIds' => $blockedUserIds,
            'senderInfo' => $this->senderInfoMap($messages),
        ]);
    }

    public function markValid(ContactMessage $message): RedirectResponse
    {
        $this->service->moderate($message, ContactMessage::STATUS_VALID);

        flash_notice('Message marked as valid.', 'success');

        return redirect()->back();
    }

    public function markInvalid(ContactMessage $message): RedirectResponse
    {
        $this->service->moderate($message, ContactMessage::STATUS_INVALID);

        flash_notice('Message marked as invalid.', 'success');

        return redirect()->back();
    }

    public function blockSender(Request $request, ContactMessage $message): RedirectResponse
    {
        $sender = $message->user;

        if (! $sender) {
            flash_notice('This message has no portal account to block.', 'error');

            return redirect()->back();
        }

        $reason = (string) $request->input('reason', '');

        $this->service->blockSender($sender, $reason, Auth::user());

        flash_notice("{$sender->name} has been blocked from sending messages.", 'success');

        return redirect()->back();
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        flash_notice('Message deleted.', 'success');

        return redirect()->back();
    }

    public function blockedSenders(): View
    {
        $blocks = MessageSenderBlock::query()
            ->with(['user.student', 'user.teacher', 'blocker'])
            ->whereNull('unblocked_at')
            ->orderByDesc('blocked_at')
            ->paginate(15);

        return view('office.blocked_senders', [
            'blocks' => $blocks,
        ]);
    }

    public function unblock(MessageSenderBlock $block): RedirectResponse
    {
        $this->service->unblock($block);

        flash_notice('Sender unblocked.', 'success');

        return redirect()->route('office.message-center.blocked');
    }

    /**
     * Extra sender context (grade/section for students, advisory for
     * teachers) used by the office admin inbox.
     */
    private function senderInfoMap($messages): array
    {
        $service = app(AnnouncementService::class);

        $map = [];

        foreach ($messages as $message) {
            $user = $message->user;

            if (! $user || isset($map[$user->id])) {
                continue;
            }

            $info = [];

            if ($user->role === 'student' && $user->student) {
                $info[] = $user->student->grade_level !== null
                    ? 'Grade '.$user->student->grade_level
                    : null;
                $info[] = $service->studentSection($user->student->id);
            } elseif ($user->role === 'teacher' && $user->teacher) {
                $info[] = $user->teacher->advisory_class ?: null;
            }

            $map[$user->id] = collect($info)->filter()->implode(' · ');
        }

        return $map;
    }
}
