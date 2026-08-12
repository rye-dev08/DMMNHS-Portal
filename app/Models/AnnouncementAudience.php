<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementAudience extends Model
{
    protected $table = 'announcement_audiences';

    protected $fillable = [
        'announcement_id', 'target_type', 'target_value',
    ];
}
