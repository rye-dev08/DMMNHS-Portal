<?php

namespace App\Http\Controllers;

use App\Rules\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function index(): View
    {
        return view('password.change');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! Hash::check($request->string('old_password')->toString(), $user->password_hash)) {
            flash_notice('Old password incorrect', 'error');
        } elseif ($request->string('new_password')->toString() !== $request->string('confirm_password')->toString()) {
            flash_notice('New passwords do not match', 'error');
        } elseif (! $this->passesPolicy($request->string('new_password')->toString())) {
            flash_notice('Password must be at least 8 chars and include uppercase or symbol', 'error');
        } else {
            $user->password_hash = Hash::make($request->string('new_password')->toString());
            $user->save();
            flash_modal('Your password has been changed successfully.', 'success', 'Password Updated');
        }

        return redirect()->back();
    }

    private function passesPolicy(string $password): bool
    {
        $rule = new PasswordPolicy;
        $fails = false;
        $rule->validate('new_password', $password, function () use (&$fails) {
            $fails = true;
        });

        return ! $fails;
    }
}