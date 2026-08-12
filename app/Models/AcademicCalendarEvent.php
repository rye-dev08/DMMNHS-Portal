<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicCalendarEvent extends Model
{
    protected $table = 'academic_calendar_events';

    protected $fillable = [
        'title', 'event_date', 'start_time', 'end_time', 'location',
        'short_description', 'full_description', 'category', 'school_year', 'term',
    ];

    protected $casts = [
        'event_date' => 'date:Y-m-d',
    ];

    /**
     * Allowed event categories (admin select options).
     */
    public const CATEGORIES = [
        'Academic' => 'Academic',
        'Exam' => 'Exam',
        'Holiday' => 'Holiday',
        'Event' => 'School Event',
        'Activity' => 'Activity',
        'Deadline' => 'Deadline',
        'Other' => 'Other',
    ];
}
