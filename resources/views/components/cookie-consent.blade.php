@props([
    'message' => 'This portal uses cookies that are essential for authentication, session management, and personalizing your experience.',
    'policy_url' => '#',
])

@php
// The banner is shown on every visit until the user explicitly ACCEPTS.
// A "declined" preference is session-scoped only (no expiry on the cookie),
// so it suppresses the banner for the current browsing session but the
// banner reappears on the next visit unless the user accepts.
$consent = request()->cookie('cookies_consent');
@endphp

@if ($consent !== 'accepted' && $consent !== 'declined')
<div id="cookie-consent-banner" data-cookie-banner
     style="position:fixed;bottom:1.5rem;left:1.5rem;right:1.5rem;z-index:9999;">
    <div class="mx-auto flex max-w-3xl items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-[0_18px_50px_rgba(15,23,42,0.18)] ring-1 ring-black/5">
        <p class="text-[13px] text-slate-600 flex-1">
            {{ $message }}
            @if($policy_url !== '#')
                <a href="{{ $policy_url }}" target="_blank" rel="noopener"
                   class="underline">Cookie Policy</a>
            @endif
        </p>
        <div class="mt-3 flex flex-col gap-2 sm:ml-4 sm:mt-0 sm:flex-row">
            <button type="button" data-cookie-accept
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Accept All
            </button>
            <button type="button" data-cookie-decline
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                Decline
            </button>
        </div>
    </div>
</div>
@endif

@once('cookie-consent-assets')
@push('scripts')
<script>
(function () {
    var CONSENT_COOKIE = 'cookies_consent';

    // Accept: persistent cookie (1 year) -> banner never shows again.
    // Decline: session cookie (no expiry) -> banner stays hidden for this
    // browsing session, but reappears on the next visit.
    function setCookie(name, value, days) {
        var expires = '';
        if (days > 0) {
            var d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = ";expires=" + d.toUTCString();
        }
        // Preferences-only cookie. NOT HTTP-only by design (client-side dismissal);
        // SameSite=Lax keeps it first-party. Never used for authentication.
        document.cookie = name + "=" + value + expires + ";path=/;SameSite=Lax";
    }

    function hasCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1);
            if (c.indexOf(nameEQ) === 0) return true;
        }
        return false;
    }

    function hide() {
        var banner = document.getElementById('cookie-consent-banner');
        if (banner) banner.remove();
    }

    // Guard: if the server already hid the banner (accepted), do nothing.
    if (hasCookie(CONSENT_COOKIE)) {
        return;
    }

    document.addEventListener('click', function (e) {
        var target = e.target;
        while (target && target !== document) {
            if (target.hasAttribute && target.hasAttribute('data-cookie-accept')) {
                setCookie(CONSENT_COOKIE, 'accepted', 365);
                hide();
                return;
            }
            if (target.hasAttribute && target.hasAttribute('data-cookie-decline')) {
                setCookie(CONSENT_COOKIE, 'declined', 0); // session-only
                hide();
                return;
            }
            target = target.parentElement;
        }
    });

    window.cookieConsent = { setCookie: setCookie, hasCookie: hasCookie };
})();
</script>
@endpush
@endonce
