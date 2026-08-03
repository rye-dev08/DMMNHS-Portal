<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreviousSemesterSubject extends Model
{
    /**
     * The legacy table has no Laravel timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'original_subject_id', 'student_id', 'teacher_id', 'subject_name', 'course_code',
        'teacher_code', 'room_no', 'archived_semester', 'archived_school_year',
    ];
}