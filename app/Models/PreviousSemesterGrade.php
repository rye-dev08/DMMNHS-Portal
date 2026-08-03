<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreviousSemesterGrade extends Model
{
    /**
     * The legacy table has no Laravel timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'original_grade_id', 'student_id', 'subject_id', 'grade', 'quarter',
        'archived_semester', 'archived_school_year',
    ];
}