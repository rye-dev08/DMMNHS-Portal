<?php

namespace App\Support;

/**
 * Standardized grade display and color rules.
 * Ported from the legacy `map_grade_display()` helper.
 */
class GradeFormatter
{
    public static function display(mixed $rawGrade): array
    {
        if ($rawGrade === null || $rawGrade === '' || strtoupper((string) $rawGrade) === 'N/A') {
            return ['label' => 'N/A', 'color' => '#9ca3af'];
        }

        if (is_numeric($rawGrade)) {
            $grade = (int) $rawGrade;

            if ($grade >= 83) {
                return ['label' => (string) $grade, 'color' => '#22c55e']; // green
            }

            if ($grade >= 75) {
                return ['label' => (string) $grade, 'color' => '#f97316']; // orange
            }

            if ($grade >= 1) {
                return ['label' => 'INC', 'color' => '#eab308']; // yellow
            }

            return ['label' => 'DROPPED', 'color' => '#ef4444']; // red
        }

        $text = strtoupper(trim((string) $rawGrade));

        if ($text === 'DROPPED') {
            return ['label' => 'DROPPED', 'color' => '#ef4444'];
        }

        if ($text === 'INC') {
            return ['label' => 'INC', 'color' => '#eab308'];
        }

        return ['label' => $text, 'color' => '#9ca3af'];
    }
}