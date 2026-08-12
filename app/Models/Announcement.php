<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title', 'short_summary', 'content', 'attachment', 'attachment_name',
        'priority', 'status', 'target_role', 'publish_date', 'expiration_date',
        'school_year', 'term', 'created_by',
    ];

    protected $casts = [
        'publish_date' => 'date:Y-m-d',
        'expiration_date' => 'date:Y-m-d',
        'term' => 'integer',
    ];

    public const PRIORITIES = [
        'normal' => 'Normal',
        'important' => 'Important',
        'urgent' => 'Urgent',
    ];

    public const TARGET_ROLES = [
        'all' => 'All Users',
        'students' => 'Students',
        'teachers' => 'Teachers',
        'admins' => 'Admins',
    ];

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_UNPUBLISHED = 'unpublished';

    /**
     * Audience refinement rows (grade levels, sections, specific students/teachers).
     */
    public function audiences(): HasMany
    {
        return $this->hasMany(AnnouncementAudience::class, 'announcement_id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasExpired(): bool
    {
        return $this->expiration_date !== null
            && $this->expiration_date->lt(now()->startOfDay());
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? 'Normal';
    }

    public function audienceBaseLabel(): string
    {
        return self::TARGET_ROLES[$this->target_role] ?? 'Everyone';
    }

    public function setExpirationDateAttribute($value): void
    {
        $this->attributes['expiration_date'] = $value ?: null;
    }
}
