<?php

namespace App\Http\Controllers;

use App\Rules\PasswordPolicy;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            try {
                $user->password_hash = Hash::make($request->string('new_password')->toString());
                $user->save();

                app(NotificationService::class)->passwordChanged($user);

                flash_notice('Your password has been changed successfully.', 'success');
            } catch (\Throwable $e) {
                report($e);
                flash_notice('Unable to change your password. Please try again.', 'error');
            }
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
