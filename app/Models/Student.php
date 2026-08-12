<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    /**
     * The legacy table does not use Laravel's created_at / updated_at columns.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'student_id_no', 'id_token', 'id_token_generated_at', 'photo',
        'sex', 'birthday', 'age', 'grade_level', 'status', 'needs_reenrollment',
    ];

    protected function casts(): array
    {
        return [
            'id_token_generated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
