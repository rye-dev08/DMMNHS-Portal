<?php

if (! function_exists('flash_notice')) {
    /**
     * Flash a notice for the floating alert component.
     * Mirrors the legacy `set_flash_notice()` helper.
     */
    function flash_notice(string $message, string $type = 'info'): void
    {
        session()->flash('flash_notice', [
            'message' => $message,
            'type' => $type,
        ]);
    }
}

if (! function_exists('flash_modal')) {
    /**
     * Flash a prominent centered modal (e.g. "Password updated successfully").
     * Rendered by the `<x-notice />` component.
     */
    function flash_modal(string $message, string $type = 'success', ?string $title = null): void
    {
        session()->flash('flash_modal', [
            'message' => $message,
            'type' => $type,
            'title' => $title,
        ]);
    }
}