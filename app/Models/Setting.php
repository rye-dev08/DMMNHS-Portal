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
        'id', 'current_term', 'current_school_year', 'max_students_per_class', 'max_subjects_per_teacher',
        'enrollment_phase',
    ];

    /**
     * The allowed enrollment phases.
     */
    public const PHASE_NONE = 'none';
    public const PHASE_ENROLLMENT = 'enrollment';
    public const PHASE_CLOSED = 'closed';

    /**
     * Compute the current academic period state and which lifecycle
     * actions are allowed. Centralised so the controller and the
     * period indicator in the header share one source of truth.
     */
    public function period(): object
    {
        $term = (int) ($this->current_term ?? 1);
        $year = (string) ($this->current_school_year ?? '');
        $phase = (string) ($this->enrollment_phase ?? self::PHASE_NONE);

        $canNewTerm = $phase === self::PHASE_NONE && $term < 3;
        $canEndSchoolYear = $phase === self::PHASE_NONE && $term === 3;
        $canEndEnrollmentPhase = $phase === self::PHASE_ENROLLMENT;
        $canNewSchoolYear = $phase === self::PHASE_CLOSED;

        return (object) [
            'term' => $term,
            'school_year' => $year,
            'phase' => $phase,
            'label' => $this->periodLabel($term, $phase, $year),
            'can_new_term' => $canNewTerm,
            'can_end_school_year' => $canEndSchoolYear,
            'can_end_enrollment_phase' => $canEndEnrollmentPhase,
            'can_new_school_year' => $canNewSchoolYear,
        ];
    }

    private function periodLabel(int $term, string $phase, string $year): string
    {
        if ($phase === self::PHASE_ENROLLMENT) {
            return "Enrollment Phase • {$year}";
        }

        if ($phase === self::PHASE_CLOSED) {
            return "Enrollment Closed • {$year}";
        }

        return "Term {$term} • {$year}";
    }
}