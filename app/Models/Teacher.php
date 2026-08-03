<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    /**
     * The legacy table does not use Laravel's created_at / updated_at columns.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'advisory_class', 'max_subjects', 'max_students', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approval()
    {
        return $this->hasOne(TeacherApproval::class, 'teacher_id');
    }
}