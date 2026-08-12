@php
    $code = 503;
    $title = 'Service Unavailable';
    $message = 'The portal is temporarily unavailable for maintenance or upgrades. Please check back in a few minutes.';
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
@endphp
<x-errors._error-page :code="$code" :title="$title" :message="$message" :icon="$icon" />
