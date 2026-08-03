<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    /**
     * The legacy table manages its own created_at / updated_at through DB defaults.
     */
    public $timestamps = false;

    protected $fillable = [
        'teacher_id', 'student_id', 'score_type', 'item_no', 'score', 'max_score', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}