<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreviousTermSubject extends Model
{
    /**
     * The legacy table has no Laravel timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'original_subject_id', 'student_id', 'teacher_id', 'subject_name', 'course_code',
        'teacher_code', 'room_no', 'archived_term', 'archived_school_year',
    ];
}