<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    /**
     * The legacy table stores its own submission datetime and no Laravel timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'subject_id', 'grade', 'remarks', 'quarter', 'date_submitted',
    ];

    protected function casts(): array
    {
        return [
            'date_submitted' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}