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
<body class="min-h-screen bg-[#f6f8fc] text-slate-900">
    <div class="page-fade-in min-h-screen">
        {{ $slot }}
    </div>
    @stack('scripts')
</body>
</html>