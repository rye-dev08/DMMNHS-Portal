@props(['title' => config('app.name')])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7fb] text-slate-900">
    <x-layouts.sidebar />

    <div id="app-shell" class="flex min-h-screen flex-col transition-[padding] duration-300 lg:pl-[264px]">
        <x-layouts.header />

        <main id="page-main" class="relative mx-auto my-6 flex-1 w-[min(96%,1080px)]">
            {{-- Outer glow / border ring --}}
            <div class="absolute -inset-[1.5px] rounded-[18px] bg-gradient-to-br from-[#0018f9]/50 via-[#38bdf8]/25 to-[#0a1633]/60 blur-[1px]"></div>

            {{-- Panel --}}
            <div class="relative overflow-hidden rounded-[18px] border border-[#0018f9]/25 bg-gradient-to-br from-[#ffffff] via-[#f6f8ff] to-[#e8f0ff] shadow-[0_18px_50px_-12px_rgba(2,6,23,0.25)]">

                {{-- Subtle grid backdrop --}}
                <div class="pointer-events-none absolute inset-0 opacity-[0.5]"
                     style="background-image: linear-gradient(rgba(10,22,51,0.045) 1px, transparent 1px), linear-gradient(90deg, rgba(10,22,51,0.045) 1px, transparent 1px); background-size: 34px 34px;"></div>

                {{-- Futuristic corner brackets --}}
                <div class="pointer-events-none absolute left-3 top-3 h-7 w-7 rounded-tl-lg border-l-2 border-t-2 border-[#0018f9]/70"></div>
                <div class="pointer-events-none absolute right-3 top-3 h-7 w-7 rounded-tr-lg border-r-2 border-t-2 border-[#0018f9]/70"></div>
                <div class="pointer-events-none absolute bottom-3 left-3 h-7 w-7 rounded-bl-lg border-b-2 border-l-2 border-[#38bdf8]/60"></div>
                <div class="pointer-events-none absolute bottom-3 right-3 h-7 w-7 rounded-br-lg border-b-2 border-r-2 border-[#38bdf8]/60"></div>

                {{-- Top status line --}}
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9]"></div>

                {{-- Soft inner glows --}}
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(56,189,248,0.10),transparent_55%)]"></div>
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,24,249,0.08),transparent_55%)]"></div>

                {{-- Body --}}
                <div class="relative p-[18px] sm:p-6">
                    <x-notice />
                    {{ $slot }}
                </div>
            </div>
        </main>

        <x-layouts.footer />
    </div>
</body>
</html>