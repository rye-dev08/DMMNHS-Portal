<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string'],
        ]);

        ContactMessage::create($validated);

        flash_notice('Message sent successfully! The school office will reach out to you.', 'success');

        return redirect()->route('contact');
    }
}