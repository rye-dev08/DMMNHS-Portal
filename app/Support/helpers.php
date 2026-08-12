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

if (! function_exists('academic_calendar_category_style')) {
    /**
     * Tailwind classes used to render an academic calendar event category.
     * Kind can be 'badge' (pill), 'dot' (indicator), or 'cell' (grid marker).
     */
    function academic_calendar_category_style(string $category, string $kind = 'badge'): string
    {
        $styles = [
            'badge' => [
                'Academic' => 'border-blue-200 bg-blue-100/60 text-blue-700',
                'Exam' => 'border-rose-200 bg-rose-100/60 text-rose-700',
                'Holiday' => 'border-amber-200 bg-amber-100/60 text-amber-700',
                'Event' => 'border-purple-200 bg-purple-100/60 text-purple-700',
                'Activity' => 'border-emerald-200 bg-emerald-100/60 text-emerald-700',
                'Deadline' => 'border-orange-200 bg-orange-100/60 text-orange-700',
                'Other' => 'border-slate-200 bg-slate-100/60 text-slate-600',
            ],
            'dot' => [
                'Academic' => 'bg-[#0018f9]',
                'Exam' => 'bg-rose-500',
                'Holiday' => 'bg-amber-500',
                'Event' => 'bg-purple-500',
                'Activity' => 'bg-emerald-500',
                'Deadline' => 'bg-orange-500',
                'Other' => 'bg-slate-400',
            ],
            'cell' => [
                'Academic' => 'bg-[#0018f9]',
                'Exam' => 'bg-rose-500',
                'Holiday' => 'bg-amber-500',
                'Event' => 'bg-purple-500',
                'Activity' => 'bg-emerald-500',
                'Deadline' => 'bg-orange-500',
                'Other' => 'bg-slate-400',
            ],
        ];

        return $styles[$kind][$category] ?? $styles[$kind]['Other'];
    }
}

if (! function_exists('announcement_priority_style')) {
    /**
     * Tailwind classes used to render an announcement priority.
     * Kind can be 'badge' (pill) or 'accent' (edge/dot indicator).
     */
    function announcement_priority_style(string $priority, string $kind = 'badge'): string
    {
        $styles = [
            'badge' => [
                'normal' => 'border-sky-200 bg-sky-100/60 text-sky-700',
                'important' => 'border-amber-200 bg-amber-100/60 text-amber-700',
                'urgent' => 'border-red-200 bg-red-100/60 text-red-700',
            ],
            'accent' => [
                'normal' => 'bg-sky-500',
                'important' => 'bg-amber-500',
                'urgent' => 'bg-red-500',
            ],
        ];

        return $styles[$kind][$priority] ?? $styles[$kind]['normal'];
    }
}
