<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InfoController extends Controller
{
    public function index(): View
    {
        $user = User::with('teacher')
            ->where('id', auth()->id())
            ->first();

        return view('teacher.info', ['user' => $user]);
    }

    public function edit(): View
    {
        $user = User::with('teacher')
            ->where('id', auth()->id())
            ->first();

        return view('teacher.edit_info', ['user' => $user]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = User::findOrFail(auth()->id());

        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
        ])->validate();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        flash_modal('Your personal information has been updated.', 'success', 'Info Updated');

        return redirect()->route('teacher.info');
    }
}