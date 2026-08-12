@php
    $navLinks = [
        ['label' => 'Home', 'target' => '#home'],
        ['label' => 'About', 'target' => '#about'],
        ['label' => 'Features', 'target' => '#features'],
        ['label' => 'Announcements', 'target' => '#announcements'],
        ['label' => 'Contact', 'target' => '#contact'],
    ];

    $features = [
        [
            'title' => 'Academic Calendar',
            'description' => 'Track school events, exams, holidays, and deadlines for the current term at a glance.',
            'icon' => 'calendar',
            'accent' => 'from-[#38bdf8] to-[#2563eb]',
        ],
        [
            'title' => 'Digital Student ID',
            'description' => 'A secure, scannable digital ID with a unique token, instantly verifiable by the school.',
            'icon' => 'id',
            'accent' => 'from-[#818cf8] to-[#6366f1]',
        ],
        [
            'title' => 'Requirement & Submission Tracker',
            'description' => 'Monitor assignments, school forms, and project submissions with clear status updates.',
            'icon' => 'clipboard',
            'accent' => 'from-[#34d399] to-[#059669]',
        ],
        [
            'title' => 'Announcements',
            'description' => 'Stay informed with school-wide announcements delivered straight to your portal.',
            'icon' => 'bell',
            'accent' => 'from-[#fbbf24] to-[#f59e0b]',
        ],
        [
            'title' => 'Messaging',
            'description' => 'Communicate securely with the school office and staff through the built-in message center.',
            'icon' => 'chat',
            'accent' => 'from-[#f472b6] to-[#db2777]',
        ],
        [
            'title' => 'Grades',
            'description' => 'View your subject grades, assessment scores, and quarterly academic performance.',
            'icon' => 'chart',
            'accent' => 'from-[#38bdf8] to-[#0284c7]',
        ],
        [
            'title' => 'Teacher Workload Dashboard',
            'description' => 'An at-a-glance view of classes, deadlines, grade submission progress, and pending workload.',
            'icon' => 'grid',
            'accent' => 'from-[#a78bfa] to-[#7c3aed]',
        ],
        [
            'title' => 'Important Dates',
            'description' => 'Upcoming events and requirement deadlines, gathered automatically and highlighted by urgency.',
            'icon' => 'tick',
            'accent' => 'from-[#2f7df6] to-[#0b3ef2]',
        ],
    ];

    $roles = [
        [
            'title' => 'Student',
            'icon' => 'person',
            'accent' => 'from-[#38bdf8] to-[#2563eb]',
            'items' => ['Enrollment', 'Grades', 'Digital ID', 'Requirements'],
        ],
        [
            'title' => 'Teacher',
            'icon' => 'book',
            'accent' => 'from-[#34d399] to-[#059669]',
            'items' => ['Students', 'Grades', 'Requirements', 'Workload Dashboard'],
        ],
        [
            'title' => 'Office Administrator',
            'icon' => 'sliders',
            'accent' => 'from-[#fbbf24] to-[#f59e0b]',
            'items' => ['Academic Calendar', 'Announcements', 'Teacher Assignment', 'Message Center', 'Digital Student ID'],
        ],
        [
            'title' => 'System Administrator',
            'icon' => 'key',
            'accent' => 'from-[#f472b6] to-[#db2777]',
            'items' => ['User Management', 'Enrollment Settings', 'School Year', 'Semester', 'System Configuration'],
        ],
    ];

    $previews = [
        ['title' => 'Student Dashboard', 'desc' => 'Personal academic hub', 'icon' => 'person'],
        ['title' => 'Teacher Dashboard', 'desc' => 'Workload & class management', 'icon' => 'book'],
        ['title' => 'Office Administrator Dashboard', 'desc' => 'Academic operations center', 'icon' => 'sliders'],
        ['title' => 'System Administrator Dashboard', 'desc' => 'Configuration & control', 'icon' => 'key'],
    ];

    $statCards = [
        ['label' => 'Students', 'value' => $stats->students, 'icon' => 'person'],
        ['label' => 'Teachers', 'value' => $stats->teachers, 'icon' => 'book'],
        ['label' => 'Academic Programs', 'value' => $stats->programs, 'icon' => 'grid'],
        ['label' => 'Announcements', 'value' => $stats->announcements, 'icon' => 'bell'],
        ['label' => 'Requirements Processed', 'value' => $stats->requirements, 'icon' => 'clipboard'],
    ];

    $icon = function (string $key): string {
        $patterns = [
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />',
            'id' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />',
            'bell' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />',
            'chat' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
            'grid' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
            'tick' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            'person' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'book' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />',
            'sliders' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />',
            'key' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />',
            'pin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />',
            'mail' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />',
            'phone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />',
            'facebook' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75 0 4.487 3.042 8.283 7.055 9.583.55.098.75-.23.75-.517 0-.256-.01-1.107-.015-2.166-3.062.685-3.708-1.303-3.708-1.303-.46-1.229-1.18-1.548-1.18-1.548-.98-.66.043-.654.043-.654 1.058.065 1.613 1.069 1.613 1.069.94 1.577 2.465 1.121 3.065.856.096-.66.365-1.121.668-1.378-2.09-.247-4.29-1.02-4.29-4.573 0-1.02.352-1.833.93-2.488-.093-.247-.405-1.197.088-2.488 0 0 .766-.243 2.505.95.735-.194 1.5-.291 2.25-.293.765.002 1.53.099 2.25.293 1.74-1.193 2.505-.95 2.505-.95.493 1.291.181 2.241.088 2.488.578.655.93 1.468.93 2.488 0 3.557-2.205 4.32-4.29 4.573.36.3.675.9.675 1.815 0 1.312-.01 2.368-.01 2.69 0 .288.198.62.75.517a9.76 9.76 0 0 0 7.05-9.583C21.75 6.615 17.385 2.25 12 2.25Z" />',
            'arrow' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />',
            'spark' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3Z" />',
            'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
            'bolt' => '<path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />',
            'device' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />',
            'stack' => '<path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />',
        ];
        return $patterns[$key] ?? $patterns['spark'];
    };

    $aboutHighlights = [
        ['title' => 'Real-time information', 'desc' => 'Grades, schedules, and deadlines are always current.', 'icon' => 'bolt'],
        ['title' => 'Secure access', 'desc' => 'Role-based authentication protects every account.', 'icon' => 'shield'],
        ['title' => 'Centralized academic management', 'desc' => 'One system for enrollment, records, and services.', 'icon' => 'stack'],
        ['title' => 'Responsive design', 'desc' => 'Works beautifully on desktop, tablet, and mobile.', 'icon' => 'device'],
    ];

    $previewEvents = $upcomingEvents->isNotEmpty()
        ? $upcomingEvents->map(fn ($event) => [
            'title' => $event->title,
            'subtitle' => \App\Models\AcademicCalendarEvent::CATEGORIES[$event->category] ?? $event->category,
            'date' => $event->event_date->format('M d, Y'),
        ])
        : collect([
            ['title' => 'Foundation Day', 'subtitle' => 'School Event', 'date' => 'Dec 10, 2026'],
            ['title' => 'Enrollment Deadline', 'subtitle' => 'Deadline', 'date' => 'Jun 30, 2026'],
            ['title' => 'Recognition Day', 'subtitle' => 'School Event', 'date' => 'Mar 27, 2026'],
        ]);
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMMNHS Student Portal</title>
    <meta name="description" content="Don Mariano Marcos National High School - Student Information & Grade Management Portal">
    <link rel="icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}" type="image/jpeg">
    <link rel="apple-touch-icon" href="{{ asset('images/dmnhs-no-bg.jpg') }}">
    <meta name="google-site-verification" content="ZnzZzW2FVWvjOdQAXeKiX7IlVj-Ss2zrGnTpsoH_qW8" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        section[id] { scroll-margin-top: 70px; }

        .lp-bg-grid {
            background-image: linear-gradient(rgba(148, 197, 255, 0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(148, 197, 255, 0.05) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        .lp-float { animation: lp-float 9s ease-in-out infinite; }
        .lp-float-slow { animation: lp-float 14s ease-in-out infinite reverse; }
        @keyframes lp-float {
            0%, 100% { transform: translateY(0) translateX(0); }
            50% { transform: translateY(-18px) translateX(6px); }
        }

        .lp-glow {
            background: radial-gradient(circle at center, rgba(56, 189, 248, 0.22), transparent 65%);
        }

        .lp-reveal {
            opacity: 0;
            transform: translateY(26px);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .lp-reveal.lp-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .lp-hero-gradient {
            background: radial-gradient(ellipse at top right, rgba(37, 99, 235, 0.25), transparent 55%),
                        radial-gradient(ellipse at bottom left, rgba(56, 189, 248, 0.14), transparent 50%);
        }

        .lp-timeline-line {
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.5), rgba(0, 24, 249, 0.2));
        }

        .lp-map {
            background:
                radial-gradient(circle at 30% 30%, rgba(56, 189, 248, 0.16), transparent 60%),
                linear-gradient(rgba(148, 197, 255, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 197, 255, 0.07) 1px, transparent 1px),
                linear-gradient(135deg, #0d2450, #164aa8);
            background-size: auto, 40px 40px, 40px 40px, auto;
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .lp-float, .lp-float-slow { animation: none; }
            .lp-reveal { transition: none; opacity: 1; transform: none; }
        }

        @media (max-width: 768px) {
            section[id] { scroll-margin-top: 64px; }
            .py-14 { padding-top: 2.5rem; padding-bottom: 2.5rem; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#070d1f] font-sans text-slate-200 antialiased">

    {{-- ============ STICKY NAV ============ --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-[#070d1f]/80 backdrop-blur-xl">
        <nav class="mx-auto flex w-[min(96%,1200px)] items-center justify-between gap-4 px-4 py-3.5 lg:px-0">
            <a href="#home" class="flex items-center gap-3 no-underline">
                <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School logo"
                     class="h-10 w-10 rounded-md bg-white/90 object-contain p-0.5 shadow-[0_0_18px_rgba(56,189,248,0.45)]">
                <span class="hidden text-[13px] font-semibold leading-tight text-white sm:block">
                    DMMNHS
                    <span class="block text-[11px] font-normal text-white/55">Student Portal</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['target'] }}"
                       class="rounded-lg px-3.5 py-2 text-[13.5px] font-medium text-white/65 no-underline transition hover:bg-white/10 hover:text-white">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <a href="{{ route('login') }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#0b3ef2] to-[#2f7df6] px-5 text-[13.5px] font-semibold text-white no-underline shadow-[0_8px_20px_rgba(37,99,235,0.35)] transition duration-200 hover:brightness-110 active:scale-[0.99]">
                Login
            </a>
        </nav>
    </header>

{{-- ============ HERO ============ --}}
    <section id="home" class="relative flex min-h-[calc(100vh-4rem)] items-center overflow-hidden bg-gradient-to-br from-[#0a1633] via-[#0d2450] to-[#164aa8] pt-20 pb-10">
        <x-decorative-background />
        <div class="pointer-events-none absolute inset-0 lp-hero-gradient"></div>
        <div class="pointer-events-none absolute inset-0 lp-bg-grid"></div>

        <div class="pointer-events-none absolute left-[8%] top-[22%] h-2 w-2 rounded-full bg-[#7db4ff] opacity-60 shadow-[0_0_16px_6px_rgba(125,180,255,0.5)] lp-float"></div>
        <div class="pointer-events-none absolute right-[12%] top-[30%] h-3 w-3 rounded-full bg-[#38bdf8]/50 opacity-50 shadow-[0_0_20px_8px_rgba(56,189,248,0.4)] lp-float-slow"></div>
        <div class="pointer-events-none absolute bottom-[28%] left-[16%] h-2.5 w-2.5 rounded-full bg-[#0b3ef2]/60 opacity-50 shadow-[0_0_18px_7px_rgba(11,62,242,0.5)] lp-float-slow"></div>
        <div class="pointer-events-none absolute right-[20%] bottom-[22%] h-1.5 w-1.5 rounded-full bg-[#7db4ff] opacity-50 shadow-[0_0_14px_5px_rgba(125,180,255,0.45)] lp-float"></div>

        <div class="relative z-10 mx-auto w-[min(92%,1000px)] text-center">
            <div class="lp-reveal lp-visible mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-[0_0_40px_rgba(56,189,248,0.35)] backdrop-blur">
                <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School logo"
                     class="h-14 w-14 rounded-xl object-contain">
            </div>

            <p class="lp-reveal mb-2 text-[12px] font-semibold uppercase tracking-[0.32em] text-[#7dc6ff]/80">
                Don Mariano Marcos National High School
            </p>
            <h1 class="lp-reveal mx-auto max-w-[900px] text-[30px] font-bold leading-[1.08] text-white sm:text-[42px] lg:text-[50px]">
                <span class="bg-gradient-to-r from-[#38bdf8] to-[#2563eb] bg-clip-text text-transparent">Student Information</span>
                & Grade Management Portal
            </h1>
            <p class="lp-reveal mx-auto mt-4 max-w-[680px] text-[14px] leading-relaxed text-white/70 sm:text-[16px]">
                A modern, secure, and centralized platform designed to simplify enrollment, academic records,
                requirements, announcements, communication, and student services.
            </p>

            <div class="lp-reveal mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('login') }}"
                   class="inline-flex h-[48px] w-full max-w-[240px] items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#0b3ef2] to-[#2f7df6] px-6 text-[14px] font-semibold text-white no-underline shadow-[0_10px_26px_rgba(37,99,235,0.45)] transition duration-200 hover:brightness-110 hover:shadow-[0_12px_32px_rgba(37,99,235,0.55)] active:scale-[0.99]">
                    Access Portal
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        {!! $icon('arrow') !!}
                    </svg>
                </a>
                <a href="#about"
                   class="inline-flex h-[48px] w-full max-w-[240px] items-center justify-center gap-2 rounded-[10px] border border-white/25 bg-white/10 px-6 text-[14px] font-semibold text-white no-underline backdrop-blur transition duration-200 hover:bg-white/20 active:scale-[0.99]">
                    Learn More
                </a>
            </div>

            <div class="lp-reveal mt-10 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-[12px] text-white/55">
                <span class="flex items-center gap-1.5"><span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Online & Secure</span>
                <span class="hidden h-3 w-px bg-white/20 sm:block"></span>
                <span>{{ $systemStatus->school_year ? 'School Year '.$systemStatus->school_year : 'System Ready' }}</span>
                <span class="hidden h-3 w-px bg-white/20 sm:block"></span>
                <span>{{ $systemStatus->term ? 'Term '.$systemStatus->term : 'Always Available' }}</span>
            </div>
        </div>
    </section>

    {{-- ============ ABOUT ============ --}}
    <section id="about" class="relative overflow-hidden bg-[#0a1633] py-14">
        <div class="pointer-events-none absolute inset-0 lp-glow"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="lp-reveal mx-auto max-w-[720px] text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#38bdf8]/30 bg-[#38bdf8]/10 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#7dc6ff]">
                    About
                </span>
                <h2 class="mt-5 text-[30px] font-bold text-white sm:text-[40px]">Why this Portal?</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/65 sm:text-[16px]">
                    This platform centralizes academic services into one secure system for students, teachers,
                    office administrators, and system administrators.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($aboutHighlights as $highlight)
                    <div class="lp-reveal group relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-[#38bdf8]/40 hover:bg-white/[0.09]">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-[#0018f9] opacity-60"></div>
                        <span class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $loop->index % 2 === 0 ? 'from-[#38bdf8] to-[#2563eb]' : 'from-[#2563eb] to-[#0b3ef2]' }} text-white shadow-[0_8px_20px_rgba(37,99,235,0.4)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-6 w-6">
                                {!! $icon($highlight['icon']) !!}
                            </svg>
                        </span>
                        <h3 class="text-[16px] font-semibold text-white">{{ $highlight['title'] }}</h3>
                        <p class="mt-1.5 text-[13.5px] leading-relaxed text-white/60">{{ $highlight['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ FEATURES ============ --}}
    <section id="features" class="relative overflow-hidden bg-[#070d1f] py-14">
        <div class="pointer-events-none absolute inset-0 lp-bg-grid opacity-60"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="lp-reveal mx-auto max-w-[720px] text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#38bdf8]/30 bg-[#38bdf8]/10 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#7dc6ff]">
                    Features
                </span>
                <h2 class="mt-5 text-[30px] font-bold text-white sm:text-[40px]">Everything in One Place</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/65 sm:text-[16px]">
                    Powerful tools designed around the daily needs of the school community.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($features as $feature)
                    <div class="lp-reveal group relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition duration-300 hover:-translate-y-1.5 hover:border-white/25 hover:bg-white/[0.09] hover:shadow-[0_20px_50px_-20px_rgba(56,189,248,0.4)]">
                        <span class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $feature['accent'] }} text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)] transition duration-300 group-hover:scale-110">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-6 w-6">
                                {!! $icon($feature['icon']) !!}
                            </svg>
                        </span>
                        <h3 class="text-[16px] font-semibold text-white">{{ $feature['title'] }}</h3>
                        <p class="mt-1.5 text-[13.5px] leading-relaxed text-white/60">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ ROLES ============ --}}
    <section id="roles" class="relative overflow-hidden bg-[#0a1633] py-14">
        <div class="pointer-events-none absolute inset-0 lp-glow"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="lp-reveal mx-auto max-w-[720px] text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#38bdf8]/30 bg-[#38bdf8]/10 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#7dc6ff]">
                    Who It's For
                </span>
                <h2 class="mt-5 text-[30px] font-bold text-white sm:text-[40px]">Built for Every Role</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/65 sm:text-[16px]">
                    Each member of the school community gets a tailored, focused experience.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($roles as $role)
                    <div class="lp-reveal relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-white/25 hover:shadow-[0_20px_50px_-20px_rgba(56,189,248,0.35)]">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r {{ $role['accent'] }}"></div>
                        <span class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $role['accent'] }} text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-6 w-6">
                                {!! $icon($role['icon']) !!}
                            </svg>
                        </span>
                        <h3 class="text-[17px] font-semibold text-white">{{ $role['title'] }}</h3>
                        <ul class="mt-3 grid gap-2">
                            @foreach ($role['items'] as $item)
                                <li class="flex items-start gap-2 text-[13.5px] text-white/65">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ PORTAL PREVIEW ============ --}}
    <section id="preview" class="relative overflow-hidden bg-[#070d1f] py-14">
        <div class="pointer-events-none absolute inset-0 lp-bg-grid opacity-60"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="lp-reveal mx-auto max-w-[720px] text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#38bdf8]/30 bg-[#38bdf8]/10 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#7dc6ff]">
                    Preview
                </span>
                <h2 class="mt-5 text-[30px] font-bold text-white sm:text-[40px]">A Glimpse Inside</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/65 sm:text-[16px]">
                    Dashboards tailored to each role, ready the moment you sign in.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($previews as $preview)
                    <div class="lp-reveal group overflow-hidden rounded-2xl border border-white/10 bg-white/[0.05] backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-white/25">
                        <div class="relative flex h-36 items-center justify-center overflow-hidden bg-gradient-to-br from-[#0d2450] via-[#164aa8] to-[#0b3ef2]">
                            <div class="pointer-events-none absolute inset-0 lp-bg-grid opacity-70"></div>
                            <span class="relative inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-white/25 bg-white/10 text-[#7dc6ff] backdrop-blur">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-7 w-7">
                                    {!! $icon($preview['icon']) !!}
                                </svg>
                            </span>
                            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-400/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-300">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Live
                            </span>
                        </div>
                        <div class="p-5">
                            <h3 class="text-[15px] font-semibold text-white">{{ $preview['title'] }}</h3>
                            <p class="mt-1 text-[12.5px] text-white/55">{{ $preview['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ EVENTS + ANNOUNCEMENTS + STATUS ============ --}}
    <section id="announcements" class="relative overflow-hidden bg-[#0a1633] py-14">
        <div class="pointer-events-none absolute inset-0 lp-glow"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Important Dates preview --}}
                <div class="lp-reveal rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur">
                    <h3 class="mb-4 flex items-center gap-2 text-[15px] font-semibold text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5 text-[#7dc6ff]">
                            {!! $icon('calendar') !!}
                        </svg>
                        Upcoming Events
                    </h3>
                    <ul class="grid gap-3">
                        @foreach ($previewEvents as $event)
                            <li class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.05] px-3.5 py-3 transition hover:border-[#38bdf8]/35">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#38bdf8]/25 to-[#2563eb]/25 text-[#7dc6ff]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4.5 w-4.5">
                                        {!! $icon('calendar') !!}
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13.5px] font-semibold text-white">{{ $event['title'] }}</span>
                                    <span class="block text-[11.5px] text-white/50">{{ $event['subtitle'] }}</span>
                                </span>
                                <span class="shrink-0 rounded-md border border-white/15 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/70">
                                    {{ $event['date'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Latest announcements --}}
                <div class="lp-reveal rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur lg:col-span-1">
                    <h3 class="mb-4 flex items-center gap-2 text-[15px] font-semibold text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5 text-[#7dc6ff]">
                            {!! $icon('bell') !!}
                        </svg>
                        Latest Announcements
                    </h3>
                    <ul class="grid gap-3">
                        @forelse ($announcements as $announcement)
                            <li class="rounded-xl border border-white/10 bg-white/[0.05] px-3.5 py-3 transition hover:border-[#38bdf8]/35">
                                <span class="block truncate text-[13.5px] font-semibold text-white">{{ $announcement->title }}</span>
                                <span class="mt-0.5 block text-[11.5px] text-white/50">
                                    {{ $announcement->publish_date ? $announcement->publish_date->format('M d, Y') : 'Recently' }}
                                    @if ($announcement->priority !== 'normal')
                                        <span class="ml-1 rounded-md border border-white/15 bg-white/5 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white/60">
                                            {{ $announcement->priorityLabel() }}
                                        </span>
                                    @endif
                                </span>
                                @if ($announcement->short_summary)
                                    <p class="mt-1 line-clamp-2 text-[12.5px] leading-relaxed text-white/55">{{ $announcement->short_summary }}</p>
                                @endif
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-white/15 px-3.5 py-6 text-center">
                                <p class="text-[13px] font-semibold text-white/60">No announcements yet</p>
                                <p class="mt-0.5 text-[12px] text-white/40">Check back later for school updates.</p>
                            </li>
                        @endforelse
                    </ul>
                    <a href="{{ route('login') }}"
                       class="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-[10px] border border-white/20 bg-white/10 px-5 text-[13.5px] font-semibold text-white no-underline transition hover:bg-white/20 active:scale-[0.99]">
                        View More
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            {!! $icon('arrow') !!}
                        </svg>
                    </a>
                </div>

                {{-- System status --}}
                <div class="lp-reveal rounded-2xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur">
                    <h3 class="mb-4 flex items-center gap-2 text-[15px] font-semibold text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5 text-[#7dc6ff]">
                            {!! $icon('bolt') !!}
                        </svg>
                        System Status
                    </h3>
                    <div class="mb-4 flex items-center justify-between rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3">
                        <span class="text-[13px] font-semibold text-emerald-300">All systems operational</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2.5 py-1 text-[11px] font-bold text-emerald-300">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                            </span>
                            Online
                        </span>
                    </div>
                    <ul class="grid gap-2.5">
                        <li class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.05] px-4 py-3">
                            <span class="text-[12.5px] text-white/55">Current School Year</span>
                            <span class="text-[13px] font-semibold text-white">{{ $systemStatus->school_year ?: '—' }}</span>
                        </li>
                        <li class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.05] px-4 py-3">
                            <span class="text-[12.5px] text-white/55">Current Semester</span>
                            <span class="text-[13px] font-semibold text-white">{{ $systemStatus->term ? 'Term '.$systemStatus->term : '—' }}</span>
                        </li>
                        <li class="flex items-center justify-between rounded-xl border border-white/10 bg-white/[0.05] px-4 py-3">
                            <span class="text-[12.5px] text-white/55">Enrollment Status</span>
                            <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-emerald-300">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                {{ $systemStatus->enrollment }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ STATISTICS ============ --}}
    <section id="stats" class="relative overflow-hidden bg-[#070d1f] py-14">
        <div class="pointer-events-none absolute inset-0 lp-bg-grid opacity-60"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="lp-reveal mx-auto max-w-[720px] text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#38bdf8]/30 bg-[#38bdf8]/10 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#7dc6ff]">
                    By the Numbers
                </span>
                <h2 class="mt-5 text-[30px] font-bold text-white sm:text-[40px]">The Portal in Numbers</h2>
            </div>

            <div class="mt-12 grid grid-cols-2 gap-5 lg:grid-cols-5">
                @foreach ($statCards as $card)
                    <div class="lp-reveal relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 text-center backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-white/25">
                        <span class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#38bdf8] to-[#2563eb] text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                                {!! $icon($card['icon']) !!}
                            </svg>
                        </span>
                        <p class="text-[30px] font-bold tabular-nums text-white" data-count-target="{{ $card['value'] }}">0</p>
                        <p class="mt-1 text-[12.5px] font-medium text-white/55">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ CONTACT ============ --}}
    <section id="contact" class="relative overflow-hidden bg-[#0a1633] py-14">
        <div class="pointer-events-none absolute inset-0 lp-glow"></div>
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="lp-reveal mx-auto max-w-[720px] text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-[#38bdf8]/30 bg-[#38bdf8]/10 px-4 py-1.5 text-[12px] font-semibold uppercase tracking-[0.18em] text-[#7dc6ff]">
                    Contact
                </span>
                <h2 class="mt-5 text-[30px] font-bold text-white sm:text-[40px]">Get in Touch</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/65 sm:text-[16px]">
                    Questions about enrollment, grades, or the portal? Reach the school office anytime.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="lp-reveal grid gap-4">
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#38bdf8] to-[#2563eb] text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                                {!! $icon('pin') !!}
                            </svg>
                        </span>
                        <div>
                            <p class="text-[12px] font-semibold uppercase tracking-wide text-[#7dc6ff]/70">School Address</p>
                            <p class="mt-1 text-[14px] leading-relaxed text-white/80">Don Mariano Marcos National High School, Philippines</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#38bdf8] to-[#2563eb] text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                                {!! $icon('mail') !!}
                            </svg>
                        </span>
                        <div>
                            <p class="text-[12px] font-semibold uppercase tracking-wide text-[#7dc6ff]/70">Email</p>
                            <p class="mt-1 text-[14px] text-white/80">registrar@dmnhs.edu</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#38bdf8] to-[#2563eb] text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                                {!! $icon('phone') !!}
                            </svg>
                        </span>
                        <div>
                            <p class="text-[12px] font-semibold uppercase tracking-wide text-[#7dc6ff]/70">Phone Number</p>
                            <p class="mt-1 text-[14px] text-white/80">+63 900 000 0000</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#38bdf8] to-[#2563eb] text-white shadow-[0_8px_20px_rgba(37,99,235,0.35)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                                {!! $icon('facebook') !!}
                            </svg>
                        </span>
                        <div>
                            <p class="text-[12px] font-semibold uppercase tracking-wide text-[#7dc6ff]/70">Facebook</p>
                            <p class="mt-1 text-[14px] text-white/80">facebook.com/DMMNHS.Portal</p>
                        </div>
                    </div>
                </div>

                <div class="lp-reveal lp-map relative flex min-h-[320px] items-center justify-center overflow-hidden rounded-2xl border border-white/10 backdrop-blur">
                    <div class="pointer-events-none absolute inset-0 bg-[#070d1f]/40"></div>
                    <div class="relative z-10 text-center">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full border border-white/25 bg-white/10 text-[#7dc6ff] shadow-[0_0_30px_rgba(56,189,248,0.4)]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-7 w-7">
                                {!! $icon('pin') !!}
                            </svg>
                        </span>
                        <p class="mt-3 text-[13.5px] font-semibold text-white">Interactive Map Coming Soon</p>
                        <p class="mt-1 text-[12px] text-white/55">Google Maps placeholder</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ FOOTER ============ --}}
    <footer class="relative overflow-hidden border-t border-white/10 bg-[#050a18] py-8">
        <div class="relative z-10 mx-auto w-[min(92%,1100px)]">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <a href="#home" class="flex items-center gap-3 no-underline">
                        <img src="{{ asset('images/dmnhs-no-bg.jpg') }}" alt="School logo"
                             class="h-11 w-11 rounded-md bg-white/90 object-contain p-0.5">
                        <span class="text-[14px] font-semibold text-white">
                            DMMNHS
                            <span class="block text-[11.5px] font-normal text-white/55">Student Information &amp; Grade Management Portal</span>
                        </span>
                    </a>
                    <p class="mt-4 max-w-[380px] text-[13px] leading-relaxed text-white/50">
                        Don Mariano Marcos National High School's centralized platform for students, teachers,
                        and administrators.
                    </p>
                </div>

                <div>
                    <h4 class="mb-3 text-[12px] font-semibold uppercase tracking-[0.16em] text-white/45">Quick Links</h4>
                    <ul class="grid gap-2">
                        @foreach ($navLinks as $link)
                            <li>
                                <a href="{{ $link['target'] }}" class="text-[13px] text-white/60 no-underline transition hover:text-white">{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="mb-3 text-[12px] font-semibold uppercase tracking-[0.16em] text-white/45">Portal</h4>
                    <ul class="grid gap-2">
                        <li><a href="{{ route('login') }}" class="text-[13px] text-white/60 no-underline transition hover:text-white">Login</a></li>
                        <li><a href="{{ route('contact') }}" class="text-[13px] text-white/60 no-underline transition hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-[13px] text-white/60 no-underline transition hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-[13px] text-white/60 no-underline transition hover:text-white">Terms of Use</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 sm:flex-row">
                <p class="text-[12px] text-white/40">&copy; {{ date('Y') }} Don Mariano Marcos National High School. All rights reserved.</p>
                <p class="text-[12px] text-white/40">DMMNHS Student Portal</p>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var revealEls = document.querySelectorAll('.lp-reveal');

            if (reduce || !('IntersectionObserver' in window)) {
                revealEls.forEach(function (el) { el.classList.add('lp-visible'); });
            } else {
                var revealObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('lp-visible');
                            revealObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
                revealEls.forEach(function (el) { revealObserver.observe(el); });
            }

            var counters = document.querySelectorAll('[data-count-target]');
            if (counters.length && !reduce && 'IntersectionObserver' in window) {
                var counterObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            return;
                        }
                        counterObserver.unobserve(entry.target);
                        var el = entry.target;
                        var target = parseInt(el.getAttribute('data-count-target'), 10) || 0;
                        var duration = 1400;
                        var start = null;

                        function step(timestamp) {
                            if (!start) { start = timestamp; }
                            var progress = Math.min((timestamp - start) / duration, 1);
                            var eased = 1 - Math.pow(1 - progress, 3);
                            el.textContent = Math.round(target * eased).toLocaleString();
                            if (progress < 1) {
                                window.requestAnimationFrame(step);
                            }
                        }
                        window.requestAnimationFrame(step);
                    });
                }, { threshold: 0.5 });
                counters.forEach(function (el) { counterObserver.observe(el); });
            } else if (counters.length) {
                counters.forEach(function (el) {
                    el.textContent = (parseInt(el.getAttribute('data-count-target'), 10) || 0).toLocaleString();
                });
            }
        })();
    </script>

    <x-cookie-consent />
    @stack('scripts')
</body>
</html>
