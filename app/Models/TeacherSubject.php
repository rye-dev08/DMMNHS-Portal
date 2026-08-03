<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    /**
     * The legacy table only has a created_at column (no updated_at).
     */
    public $timestamps = false;

    protected $fillable = [
        'teacher_id', 'subject_name', 'course_code', 'teacher_code', 'room_no',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}