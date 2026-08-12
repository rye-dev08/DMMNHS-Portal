@php
    $n = is_array($notification) ? $notification : [];
    $kind = $n['kind'] ?? 'info';
    $title = $n['title'] ?? ($n['subject'] ?? 'DMMNHS Student Portal');
    $greeting = $n['greeting'] ?? null;
    $message = $n['message'] ?? '';
    $lines = array_values((array) ($n['lines'] ?? []));
    $actionText = $n['action_text'] ?? null;
    $actionUrl = $n['action_url'] ?? ($n['link'] ?? '');
    $deadline = $n['deadline'] ?? null;
    $extra = $n['extra'] ?? null;

    $palette = [
        'success' =>    '#059669',
        'error' =>      '#dc2626',
        'grades' =>     '#0018f9',
        'subject' =>    '#2563eb',
        'enrollment' => '#7c3aed',
        'requirement' =>'#ca8a04',
        'security' =>   '#b91c1c',
        'info' =>       '#0369a3',
    ];
    $accent = $palette[$kind] ?? $palette['info'];

    $greetingHtml = $greeting
        ? '<p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#334155;">' . e($greeting) . '</p>'
        : '';

    $contentHtml = '';
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') continue;
        $contentHtml .= '<p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#334155;">' . e($line) . '</p>';
    }
    if ($contentHtml === '' && $message !== '') {
        $contentHtml = '<p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#334155;">' . e($message) . '</p>';
    }

    $label = $actionText ?: 'Open Portal';
    $href = $actionUrl !== '' ? $actionUrl : null;
    $appName = config('app.name', 'DMMNHS Student Portal');
    $appUrl = rtrim((string) config('app.url', env('APP_URL', 'http://127.0.0.1:8000')), '/');
    $logoUrl = $appUrl . '/images/dmnhs-no-bg.jpg';
    $year = date('Y');
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" style="margin:0;padding:0;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="color-scheme" content="light">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style type="text/css">
        body { margin:0 !important; padding:0 !important; width:100% !important; background:#f5f7fb; }
        #outlook a { padding:0; margin:0; } Body { width:100% !important; }
        .ExternalClass { width:100%; } .ReadMsgBody { width:100%; }
        img { -ms-interpolation-mode:bicubic; border:0; height:auto; line-height:100%; outline:none; text-decoration:none; }
        @media screen and (max-width:580px) {
            .wrapper { padding:16px !important; }
            .hero-title { font-size:24px !important; }
            .button { padding:14px 22px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;width:100% !important;background-color:#f5f7fb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';-webkit-text-size-adjust:none;">
    <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#f5f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table class="wrapper" role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width:640px;background-color:#ffffff;border-radius:14px;overflow:hidden;">
                    <tr>
                <td style="background-color:{{ $accent }};padding:28px 32px;">
                    <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="padding-bottom:14px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="left">
                                    @if($logoUrl)
                                        <tr>
                                            <td width="40" height="40" style="text-align:left;">
                                                <img src="{{ $logoUrl }}" alt="DMMNHS Logo" width="40" height="40" style="display:block;border:0;width:40px;height:40px;border-radius:8px;background-color:rgba(255,255,255,0.25);">
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="right" style="margin-right:4px;">
                                    <tr>
                                        <td style="width:200px;height:20px;border-radius:4px;background-color:rgba(255,255,255,0.30);"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <h1 class="hero-title" style="margin:0;font-size:28px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">{{ $title }}</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding-top:8px;font-size:13px;line-height:16px;color:rgba(255,255,255,0.85);">
                                            DMMNHS Student Portal
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td style="padding-top:12px;"><div style="width:100%;height:1px;background-color:rgba(255,255,255,0.28);"></div></td></tr>
                    </table>
                </td>
                    </tr>
                    <tr>
                        <td style="padding:4px 32px 0;">
                            {!! $greetingHtml !!}{!! $contentHtml !!}
                            @if($deadline)
                                <p style="margin:0 0 16px 0;font-size:15px;line-height:24px;color:#334155;">Deadline: <strong style="color:{{ $accent }};">{{ $deadline }}</strong></p>
                            @endif
                            @if($extra)
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:0 0 16px 0;">
                                    <tr>
                                        <td style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
                                            <p style="margin:0;font-size:14px;line-height:22px;color:#475569;">{{ $extra }}</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                    @if($href)
                        <tr>
                            <td style="padding:24px 32px 8px;">
                                <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" bgcolor="{{ $accent }}" style="border-radius:8px;">
                                            <a href="{{ $href }}" target="_blank" rel="noopener" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;background-color:{{ $accent }};border:1px solid {{ $accent }};border-radius:8px;">{{ $label }}</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    @if(isset($n['detail_title']) || isset($n['detail_lines']))
                        <tr>
                            <td style="padding:0 32px 16px;">
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:10px;background-color:#f8fafc;">
                                    <tr>
                                        <td style="padding:16px;">
                                            @if(isset($n['detail_title']))
                                                <p style="margin:0 0 8px 0;font-size:14px;font-weight:600;color:#0a1633;">{{ $n['detail_title'] }}</p>
                                            @endif
                                            @foreach((array)($n['detail_lines'] ?? []) as $dl)
                                                <p style="margin:0 0 6px 0;font-size:13px;line-height:19px;color:#475569;">{{ $dl }}</p>
                                            @endforeach
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr><td style="padding:0 32px 0;"><div style="width:100%;height:1px;background-color:#e2e8f0;"></div></td></tr>
                    <tr>
                        <td style="padding:22px 32px 28px;text-align:center;font-size:12px;line-height:18px;color:#94a3b8;">
                            <p style="margin:0 0 8px 0;">This email was sent to you because your DMMNHS Student Portal account triggered a notification. If you did not expect this, please contact your administrator.<br>This is an automated message — please do not reply directly.</p>
                            <p style="margin:0;">&copy; {{ $year }} DMMNHS Student Portal. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
