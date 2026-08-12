<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user) {
            flash_notice('Invalid login credentials.', 'error');

            return redirect()->route('login')->withInput(['username' => $credentials['username']]);
        }

        $hash = (string) $user->password_hash;
        $verified = false;

        if ($hash !== '') {
            $info = password_get_info($hash);

            if (! empty($info['algo'])) {
                // Proper (bcrypt) hash present.
                $verified = password_verify($credentials['password'], $hash);

                if ($verified && password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    try {
                        $user->password_hash = password_hash($credentials['password'], PASSWORD_DEFAULT);
                        $user->save();
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            } elseif (hash_equals($hash, $credentials['password'])) {
                // Legacy plaintext fallback: verify and auto-upgrade.
                $verified = true;
                try {
                    $user->password_hash = password_hash($credentials['password'], PASSWORD_DEFAULT);
                    $user->save();
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        if ($verified) {
            if ($user->status !== 'active') {
                flash_notice('Your account is inactive. Please contact the administrator.', 'error');

                return redirect()->route('login')->withInput(['username' => $credentials['username']]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->route($this->dashboardRoute($user->role));
        }

        flash_notice('Invalid login credentials.', 'error');

        return redirect()->route('login')->withInput(['username' => $credentials['username']]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        flash_notice('You have been logged out successfully.', 'info');

        return redirect()->route('login');
    }

    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'system_admin' => 'admin.dashboard',
            'office_admin' => 'office.dashboard',
            'teacher' => 'teacher.dashboard',
            default => 'student.dashboard',
        };
    }
}
