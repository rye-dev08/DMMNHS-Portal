<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherApproval extends Model
{
    /**
     * The legacy table name is singular and does not use Laravel timestamps.
     */
    protected $table = 'teacher_approval';

    public $timestamps = false;

    protected $fillable = [
        'teacher_id', 'max_students', 'max_subjects', 'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}