@props(['title' => config('app.name')])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}">
    <script>
        if (localStorage.getItem('sidebar-collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f7fb] text-slate-900">
    <x-layouts.sidebar />

    <div id="app-shell" class="flex min-h-screen flex-col transition-[padding] duration-300 lg:pl-[264px]">
        <x-layouts.header />

         <main id="page-main" class="page-fade-in flex-1 w-full">
            {{-- Page content wrapper: max-width centered --}}
            <div class="mx-auto w-full max-w-[1080px] px-4 pt-4 pb-6 sm:px-6 lg:px-8">
                <x-notice />
                {{ $slot }}
            </div>
        </main>

        <x-layouts.footer />
    </div>
    @stack('scripts')
    </body>
</html>