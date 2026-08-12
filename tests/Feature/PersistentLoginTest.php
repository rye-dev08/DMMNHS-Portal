<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PersistentLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The login route applies CSRF protection and rate limiting. They are not
     * relevant to the behavior under test, so they are disabled here.
     */
    private function withoutLoginGuards(): static
    {
        return $this->withoutMiddleware([
            VerifyCsrfToken::class,
            ThrottleRequests::class,
        ]);
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

    private function loginUser(string $password = 'secret-123'): User
    {
        // `remember_token` is not fillable, so it is written via forceFill so the
        // tests observe a clean slate rather than the factory's default token.
        $user = User::factory()->create([
            'password_hash' => bcrypt($password),
            'status' => 'active',
        ]);

        $user->forceFill(['remember_token' => null])->save();

        return $user->fresh();
    }

    // `cookie-consent-banner` also appears inside the component's JavaScript
    // (getElementById), so the div itself is asserted by its id attribute.
    private const BANNER_DIV = 'id="cookie-consent-banner"';

    public function test_login_form_renders_remember_me_checkbox(): void
    {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('name="remember"', false)
            ->assertSee('Remember Me', false)
            // The cookie-consent banner lives on the landing page, not the login form.
            ->assertDontSee(self::BANNER_DIV, false);
    }

    public function test_login_with_remember_me_creates_persistent_authentication(): void
    {
        $user = $this->loginUser();
        $recaller = Auth::getRecallerName();

        $this->withoutLoginGuards()
            ->post(route('login.attempt'), [
                'username' => $user->username,
                'password' => 'secret-123',
                'remember' => '1',
            ])
            ->assertRedirect(route($this->dashboardRoute($user->role)))
            ->assertCookie($recaller);

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh()->getRememberToken());
    }

    public function test_login_without_remember_me_does_not_mint_persistent_cookie(): void
    {
        $user = $this->loginUser();
        $recaller = Auth::getRecallerName();

        $this->withoutLoginGuards()
            ->post(route('login.attempt'), [
                'username' => $user->username,
                'password' => 'secret-123',
            ])
            ->assertRedirect(route($this->dashboardRoute($user->role)))
            ->assertCookieMissing($recaller);

        $this->assertAuthenticated();
        $this->assertSame('', $user->fresh()->getRememberToken());
    }

    public function test_logout_invalidates_persistent_authentication(): void
    {
        $user = $this->loginUser();
        $recaller = Auth::getRecallerName();

        $this->withoutLoginGuards()
            ->post(route('login.attempt'), [
                'username' => $user->username,
                'password' => 'secret-123',
                'remember' => '1',
            ])
            ->assertRedirect(route($this->dashboardRoute($user->role)))
            ->assertCookie($recaller);

        $this->assertAuthenticated();
        $tokenAfterLogin = $user->fresh()->getRememberToken();
        $this->assertNotNull($tokenAfterLogin);

        // A real browser would send the remember cookie back on logout; simulate
        // that here so the guard queues the cookie-forget on the response.
        $recallerValue = $tokenAfterLogin.'|'.$user->getAuthIdentifier();

        $this->withCookies([$recaller => $recallerValue])
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            // The remember cookie is expired/forgotten on logout...
            ->assertCookieExpired($recaller);

        $this->assertGuest();
        // ...and the server-side token is cycled so the old cookie cannot resurrect the session.
        $this->assertNotSame($tokenAfterLogin, $user->fresh()->getRememberToken());
    }

    public function test_invalid_credentials_never_authenticate_or_set_remember_cookie(): void
    {
        $user = $this->loginUser();
        $recaller = Auth::getRecallerName();

        $this->withoutLoginGuards()
            ->post(route('login.attempt'), [
                'username' => $user->username,
                'password' => 'wrong-password',
                'remember' => '1',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertSame('', $user->fresh()->getRememberToken());
    }

    public function test_cookie_consent_banner_shown_on_first_visit(): void
    {
        $this->get(route('home'))->assertSee(self::BANNER_DIV, false);
    }

    public function test_cookie_consent_banner_hidden_after_accept(): void
    {
        $this->withUnencryptedCookies(['cookies_consent' => 'accepted'])
            ->get(route('home'))
            ->assertDontSee(self::BANNER_DIV, false);
    }

    public function test_cookie_consent_banner_hidden_within_session_after_decline_but_returns_next_visit(): void
    {
        // With a (session-only) declined cookie present, the banner is suppressed.
        $this->withUnencryptedCookies(['cookies_consent' => 'declined'])
            ->get(route('home'))
            ->assertDontSee(self::BANNER_DIV, false);

        // Test cookies are sticky for the whole test; drop them to simulate a
        // brand new visit with no consent cookie at all.
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];

        $this->get(route('home'))->assertSee(self::BANNER_DIV, false);
    }

    public function test_role_protection_is_preserved_when_unauthenticated(): void
    {
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('teacher.dashboard'))->assertRedirect(route('login'));
    }
}
