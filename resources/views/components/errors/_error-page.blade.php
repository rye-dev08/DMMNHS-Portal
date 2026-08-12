@props(['code', 'title', 'message', 'icon'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} - {{ $title }}</title>
    <link rel="icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f8fc] text-slate-900">
    <div class="relative flex min-h-screen w-full flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-[#0a1633] via-[#0d2450] to-[#164aa8] p-6">
        <x-decorative-background />

        <div class="relative z-10 w-[min(460px,100%)] rounded-[22px] border border-white/15 bg-white/[0.06] p-8 text-center shadow-[0_30px_80px_-20px_rgba(2,6,23,0.7)] backdrop-blur-md sm:p-10">
            {{-- Top status line --}}
            <div class="absolute inset-x-0 top-0 h-[3px] rounded-t-[22px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>

            <div class="mx-auto mb-5 inline-flex h-20 w-20 items-center justify-center rounded-2xl border border-white/20 bg-white/10 text-[#38bdf8] shadow-[0_0_0_10px_rgba(56,189,248,0.12)]">
                @isset($icon)
                    {!! $icon !!}
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-10 w-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                @endisset
            </div>

            <p class="bg-gradient-to-r from-[#38bdf8] to-[#2563eb] bg-clip-text text-6xl font-black tracking-tight text-transparent">
                {{ $code }}
            </p>

            <h1 class="mt-3 text-xl font-bold text-white">{{ $title }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-white/70">{{ $message }}</p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @auth
                    <a href="{{ route(auth()->user()->role === 'system_admin' ? 'admin.dashboard' : (auth()->user()->role === 'office_admin' ? 'office.dashboard' : (auth()->user()->role === 'teacher' ? 'teacher.dashboard' : 'student.dashboard'))) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-5 py-2.5 text-sm font-bold text-white shadow-[0_8px_18px_-6px_rgba(0,24,249,0.6)] transition hover:brightness-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                        </svg>
                        Return to Dashboard
                    </a>
                @else
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-5 py-2.5 text-sm font-bold text-white shadow-[0_8px_18px_-6px_rgba(0,24,249,0.6)] transition hover:brightness-110">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                        </svg>
                        Back to Home
                    </a>
                @endauth
                <a href="javascript:history.back()"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/20">
                    Go Back
                </a>
            </div>
        </div>

        <p class="relative z-10 mt-6 text-xs text-white/45">© 2026 DMMNHS Student Portal. All rights reserved.</p>
    </div>
</body>
</html>
