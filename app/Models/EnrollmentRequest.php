<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentRequest extends Model
{
    /**
     * The legacy table stores its own requested-at datetime and no Laravel timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'teacher_id', 'status', 'date_requested',
    ];

    protected function casts(): array
    {
        return [
            'date_requested' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}