<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\MessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly MessageService $service) {}

    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        $state = [
            'remaining' => null,
            'limit' => MessageService::DAILY_LIMIT,
            'limitReached' => false,
            'blocked' => false,
            'isSender' => false,
        ];

        $user = auth()->user();

        if ($user && in_array($user->role, ['student', 'teacher'], true)) {
            $state = [
                'remaining' => $this->service->remainingToday($user),
                'limit' => MessageService::DAILY_LIMIT,
                'limitReached' => $this->service->limitReached($user),
                'blocked' => $this->service->isBlocked($user),
                'isSender' => true,
            ];
        }

        return view('contact', $state);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string'],
        ]);

        $user = auth()->user();

        // Logged-in students/teachers are attributed to their portal account:
        // name/email are taken from the account (never from the form), and the
        // daily limit + block restrictions are enforced server-side.
        if ($user && in_array($user->role, ['student', 'teacher'], true)) {
            try {
                $this->service->submit($user, $validated);
            } catch (ValidationException $exception) {
                return back()
                    ->withInput()
                    ->withErrors($exception->errors());
            }

            flash_notice('Your message has been sent to the administration.', 'success');

            return redirect()->route('contact');
        }

        ContactMessage::create($validated);

        flash_notice('Message sent successfully! The school office will reach out to you.', 'success');

        return redirect()->route('contact');
    }
}
