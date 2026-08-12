<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSubmissionDeadline extends Model
{
    /**
     * subject_name = '' means the deadline applies to every subject in the
     * grading period (term-wide default). A row with a real subject name
     * overrides the default for that subject only.
     */
    public const ALL_SUBJECTS = '';

    protected $fillable = [
        'school_year', 'term', 'subject_name', 'deadline',
    ];

    protected function casts(): array
    {
        return [
            'term' => 'integer',
            'deadline' => 'date',
        ];
    }

    public function isGlobal(): bool
    {
        return $this->subject_name === self::ALL_SUBJECTS;
    }
}
