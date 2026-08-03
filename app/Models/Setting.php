<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The settings table uses a fixed singleton id of 1 and is not auto-incremented.
     */
    protected $table = 'settings';

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id', 'current_semester', 'current_school_year', 'max_students_per_class', 'max_subjects_per_teacher',
    ];
}