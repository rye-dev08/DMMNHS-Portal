<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /**
     * The legacy table only has a created_at column (no updated_at).
     */
    public $timestamps = false;

    protected $fillable = [
        'teacher_id', 'student_id', 'subject_name', 'course_code', 'teacher_code', 'room_no', 'subject_type',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
