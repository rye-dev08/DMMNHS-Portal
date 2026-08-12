<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementSubmission extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_NEEDS_REVISION = 'needs_revision';

    public const STATUS_RESUBMITTED = 'resubmitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_UNDER_REVIEW => 'Under Review',
        self::STATUS_NEEDS_REVISION => 'Needs Revision',
        self::STATUS_RESUBMITTED => 'Resubmitted',
        self::STATUS_APPROVED => 'Approved',
    ];

    public const STATUS_STYLES = [
        self::STATUS_PENDING => 'border-amber-200 bg-amber-50 text-amber-700',
        self::STATUS_SUBMITTED => 'border-sky-200 bg-sky-50 text-sky-700',
        self::STATUS_UNDER_REVIEW => 'border-violet-200 bg-violet-50 text-violet-700',
        self::STATUS_NEEDS_REVISION => 'border-red-200 bg-red-50 text-red-700',
        self::STATUS_RESUBMITTED => 'border-orange-200 bg-orange-50 text-orange-700',
        self::STATUS_APPROVED => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    ];

    protected $fillable = [
        'requirement_id',
        'student_id',
        'teacher_id',
        'status',
        'response_text',
        'attachment',
        'attachment_name',
        'feedback',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusStyle(): string
    {
        return self::STATUS_STYLES[$this->status] ?? self::STATUS_STYLES[self::STATUS_PENDING];
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isNeedsRevision(): bool
    {
        return $this->status === self::STATUS_NEEDS_REVISION;
    }

    public function isResubmitted(): bool
    {
        return $this->status === self::STATUS_RESUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
