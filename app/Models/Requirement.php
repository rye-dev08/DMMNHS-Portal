<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requirement extends Model
{
    public const TYPE_LEGAL_DOCUMENT = 'legal_document';

    public const TYPE_SCHOOL_FORM = 'school_form';

    public const TYPE_ACADEMIC = 'academic';

    public const TYPE_ACTIVITY = 'activity';

    public const TYPE_PROJECT = 'project';

    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_LEGAL_DOCUMENT => 'Legal Document',
        self::TYPE_SCHOOL_FORM => 'School Form',
        self::TYPE_ACADEMIC => 'Academic Requirement',
        self::TYPE_ACTIVITY => 'Activity',
        self::TYPE_PROJECT => 'Project',
        self::TYPE_OTHER => 'Other',
    ];

    public const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'teacher_id',
        'title',
        'requirement_type',
        'description',
        'due_date',
        'submission_required',
        'attachment',
        'attachment_name',
        'section',
        'school_year',
        'term',
        'status',
        'last_bumped_at',
        'last_bumped_by',
        'bump_count',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'submission_required' => 'boolean',
        'term' => 'integer',
        'bump_count' => 'integer',
        'last_bumped_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(RequirementSubmission::class, 'requirement_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->requirement_type] ?? self::TYPES[self::TYPE_OTHER];
    }

    public function hasDueDate(): bool
    {
        return $this->due_date !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null && $this->due_date->lt(now()->startOfDay());
    }

    public function isDueSoon(int $days = 3): bool
    {
        if ($this->due_date === null || $this->isOverdue()) {
            return false;
        }

        return $this->due_date->lte(now()->addDays($days)->startOfDay());
    }
}
