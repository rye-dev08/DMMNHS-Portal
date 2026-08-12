<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeCompletionFlag extends Model
{
    /**
     * Tracks whether a student has already been notified that their grades
     * are complete for a specific term + school year. Lets the grades-complete
     * notification fire once per period and re-fire only when the student
     * goes back to incomplete and completes again.
     */
    protected $table = 'grade_completion_flags';

    protected $fillable = [
        'student_id', 'term', 'school_year', 'notified',
    ];

    protected function casts(): array
    {
        return [
            'student_id' => 'int',
            'term' => 'int',
            'notified' => 'bool',
        ];
    }
}
