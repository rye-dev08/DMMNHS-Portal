<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GraduatedStudent extends Model
{
    /**
     * The legacy table has no Laravel timestamps.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'graduation_grade', 'graduation_term', 'graduation_school_year', 'graduation_date',
    ];
}