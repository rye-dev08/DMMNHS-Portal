@php
    $info = $user->teacher;
    $fields = [
        'Full Name' => $user->name ?? 'N/A',
        'Username' => $user->username ?? 'N/A',
        'Email' => $user->email ?? 'N/A',
        'Account Status' => $user->status ?? 'N/A',
        'Advisory Class' => $info->advisory_class ?: 'Not set',
        'Max Students' => ((int) ($info->max_students ?? 0)) > 0 ? (int) $info->max_students : 'Not set',
        'Max Subjects Per Student' => ((int) ($info->max_subjects ?? 0)) > 0 ? (int) $info->max_subjects : 'Not set',
    ];
@endphp

<x-layouts.app :title="'Teacher Info'">
    <div class="relative mx-auto max-w-[760px] pb-4 pt-2">
        {{-- Page Header --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
            <div class="group relative flex items-center gap-2.5">
                <span class="inline-block h-6 w-6 rounded-full bg-gradient-to-br from-[#0018f9] to-[#38bdf8] shadow-[0_0_12px_rgba(0,24,249,0.5)]"></span>
                <div class="relative">
                    <h2 class="m-0 bg-gradient-to-r from-[#0a1633] via-[#0018f9] to-[#0080fe] bg-clip-text text-[22px] font-extrabold tracking-tight text-transparent">TEACHER INFO</h2>
                    <div class="mt-1 h-[3px] w-full rounded-full bg-gradient-to-r from-[#0018f9] via-[#38bdf8] to-transparent"></div>
                </div>
            </div>
        </div>

        {{-- Teacher info card --}}
        <div class="fut-card relative overflow-hidden rounded-2xl">
            {{-- Animated gradient border --}}
            <div class="fut-border"></div>

            {{-- Inner glass surface --}}
            <div class="fut-card-inner relative m-[1.5px] rounded-[15px] bg-gradient-to-br from-white/85 via-white/90 to-[#eef4ff]/90 backdrop-blur-md">
                {{-- Corner brackets --}}
                <div class="corner-bracket corner-bracket-tl"></div>
                <div class="corner-bracket corner-bracket-tr"></div>
                <div class="corner-bracket corner-bracket-bl"></div>
                <div class="corner-bracket corner-bracket-br"></div>

                {{-- Card header with horizontal layout --}}
                <header class="header-flex">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5.5 w-5.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>

                    <div class="header-text">
                        <h3>Teacher Information</h3>
                        <p>Your professional and account details.</p>
                    </div>
                </header>

                <div class="card-content">
                    {{-- Personal information section --}}
                    <div class="information-section">
                        <div class="section-title">Personal Information</div>
                        <div class="info-grid">
                            @foreach ($fields as $label => $value)
                                <div class="info-item">
                                    <div class="label">{{ $label }}</div>
                                    <div class="value">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-8 flex w-fit items-center gap-3">
            <a href="{{ route('teacher.info.edit') }}"
               class="rounded-lg bg-gradient-to-r from-[#0018f9] to-[#0080fe] px-5 py-2.5 font-semibold text-white no-underline shadow-[0_4px_14px_-4px_rgba(0,24,249,0.6)] transition hover:brightness-110">Edit Info</a>
            <a href="{{ route('teacher.dashboard') }}"
               class="rounded-lg border border-[#0018f9]/25 bg-white px-5 py-2.5 font-semibold text-[#0a1633] no-underline shadow-sm transition hover:bg-[#eaf3ff]">Back to Dashboard</a>
        </div>
    </div>

    <style>
        /* Teacher Info Card Layout - matches Student Info */
        .header-flex {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 28px;
        }

        .icon-wrapper {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0018f9 0%, #0056ff 55%, #38bdf8 100%);
            color: #ffffff;
            box-shadow: 0 0 20px rgba(0, 24, 249, 0.5);
        }

        .icon-wrapper svg {
            width: 22px;
            height: 22px;
            flex: none;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .header-text h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.25;
            color: #0a1633;
        }

        .header-text p {
            margin: 0;
            font-size: 13px;
            line-height: 1.4;
            color: #64748b;
        }

        .card-content {
            padding: 20px 28px;
        }

        .information-section {
            margin-bottom: 28px;
        }

        .section-title {
            margin-bottom: 12px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0018f9;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .info-item {
            padding: 12px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .info-item .label {
            margin-bottom: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
        }

        .info-item .value {
            font-size: 14px;
            font-weight: 500;
            color: #0f172a;
        }
    </style>
</x-layouts.app>