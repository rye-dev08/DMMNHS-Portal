<?php

namespace App\Services;

use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Models\GradeSubmissionDeadline;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Monitoring layer on top of the existing grading module.
 *
 * A "submission unit" is (teacher, subject_name, term). For the selected
 * grading period (school year + term) we count, per unit, how many of the
 * teacher's approved students have a grade row for that subject. The status
 * is derived dynamically from the existing `grades` table — nothing here
 * writes or duplicates grading records.
 */
class GradeSubmissionMonitorService
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_LATE = 'late';

    public function defaultSchoolYear(): string
    {
        return (string) (Setting::find(1)?->current_school_year ?? '');
    }

    public function defaultTerm(): int
    {
        return (int) (Setting::find(1)?->current_term ?? 1);
    }

    public function availableSchoolYears(): array
    {
        $years = [
            $this->defaultSchoolYear(),
            ...DB::table('academic_calendar_events')->distinct()->pluck('school_year')->toArray(),
            ...DB::table('grade_submission_deadlines')->distinct()->pluck('school_year')->toArray(),
        ];

        return array_values(array_unique(array_filter($years, fn ($year) => $year !== '')));
    }

    public function terms(): array
    {
        return [1, 2, 3];
    }

    public function gradeLevels(): array
    {
        return [7, 8, 9, 10, 11, 12];
    }

    public function teachers(): Collection
    {
        return DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->select('u.id as user_id', 'u.name')
            ->orderBy('u.name')
            ->get();
    }

    public function subjects(): Collection
    {
        return DB::table('subjects')
            ->select('subject_name')
            ->distinct()
            ->orderBy('subject_name')
            ->pluck('subject_name');
    }

    public function sections(): Collection
    {
        return DB::table('teachers')
            ->whereNotNull('advisory_class')
            ->where('advisory_class', '!=', '')
            ->distinct()
            ->orderBy('advisory_class')
            ->pluck('advisory_class');
    }

    /**
     * Build the submission units for the given filters.
     *
     * @param  array<string, mixed>  $filters  school_year, term, grade_level, section, teacher, subject, status
     */
    public function units(array $filters = []): Collection
    {
        $schoolYear = (string) ($filters['school_year'] ?? $this->defaultSchoolYear());
        $term = (int) ($filters['term'] ?? $this->defaultTerm());
        $quarter = 'Term '.$term;

        $teachers = $this->teachersWithAdvisory($filters);

        if ($teachers->isEmpty()) {
            return collect();
        }

        $teacherIds = $teachers->pluck('teacher_id')->all();

        $subjectRows = DB::table('subjects')
            ->whereIn('teacher_id', $teacherIds)
            ->select('id', 'teacher_id', 'student_id', 'subject_name')
            ->get();

        if ($subjectRows->isEmpty()) {
            return collect();
        }

        $approvedByTeacher = DB::table('enrollment_requests')
            ->whereIn('teacher_id', $teacherIds)
            ->where('status', 'approved')
            ->get(['teacher_id', 'student_id'])
            ->groupBy('teacher_id')
            ->map(fn ($rows) => $rows->pluck('student_id')->map(fn ($id) => (int) $id)->all());

        $gradeBySubject = DB::table('grades')
            ->whereIn('subject_id', $subjectRows->pluck('id')->unique()->values()->all())
            ->where('quarter', $quarter)
            ->get(['subject_id', 'student_id', 'date_submitted'])
            ->groupBy('subject_id')
            ->map(function ($rows) {
                $max = $rows->reduce(function (?Carbon $carry, $row) {
                    $date = $row->date_submitted ? Carbon::parse($row->date_submitted) : null;

                    return $date && (! $carry || $date->gt($carry)) ? $date : $carry;
                }, null);

                return (object) [
                    'student_ids' => $rows->pluck('student_id')->map(fn ($id) => (int) $id)->all(),
                    'max_date' => $max,
                ];
            });

        $deadlines = $this->deadlinesFor($schoolYear, $term);

        $units = collect();

        foreach ($teachers as $teacher) {
            $approvedSet = $approvedByTeacher[$teacher->teacher_id] ?? [];
            $teacherSubjects = $subjectRows->where('teacher_id', $teacher->teacher_id)->groupBy('subject_name');

            foreach ($teacherSubjects as $subjectName => $rows) {
                $assignedRows = $rows->filter(fn ($row) => in_array((int) $row->student_id, $approvedSet, true));
                $assigned = $assignedRows->count();

                if ($assigned === 0) {
                    continue;
                }

                // A subject unit is only "graded" for students who actually
                // have a grade row for it in this term — not merely because the
                // subject appears in the grades table for someone else.
                $gradedRows = $assignedRows->filter(
                    fn ($row) => isset($gradeBySubject[(int) $row->id])
                        && in_array((int) $row->student_id, $gradeBySubject[(int) $row->id]->student_ids, true)
                );
                $graded = $gradedRows->count();

                $status = $graded >= $assigned ? self::STATUS_SUBMITTED : self::STATUS_PENDING;
                $deadline = $this->deadlineForSubject($deadlines, $subjectName);

                if ($status === self::STATUS_PENDING && $deadline && $deadline->lt(Carbon::today())) {
                    $status = self::STATUS_LATE;
                }

                $lastUpdated = $gradedRows->reduce(function (?Carbon $carry, $row) use ($gradeBySubject) {
                    $date = $gradeBySubject[(int) $row->id]->max_date;

                    return $date && (! $carry || $date->gt($carry)) ? $date : $carry;
                }, null);

                $units->push((object) [
                    'teacher_user_id' => (int) $teacher->user_id,
                    'teacher_id' => (int) $teacher->teacher_id,
                    'teacher_name' => $teacher->name,
                    'subject_name' => $subjectName,
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'grade_level' => $teacher->parsed['grade'] ?? null,
                    'section' => $teacher->parsed['section'] ?? null,
                    'advisory_class' => $teacher->advisory_class ?? '',
                    'assigned' => $assigned,
                    'graded' => $graded,
                    'status' => $status,
                    'submission_date' => $status === self::STATUS_SUBMITTED ? $lastUpdated : null,
                    'last_updated' => $lastUpdated,
                    'deadline' => $deadline,
                ]);
            }
        }

        if (! empty($filters['subject'])) {
            $units = $units->where('subject_name', (string) $filters['subject']);
        }

        if (! empty($filters['status'])) {
            $units = $units->where('status', (string) $filters['status']);
        }

        return $units
            ->sortBy(function ($unit) {
                return [(int) $unit->grade_level, strtolower((string) $unit->teacher_name), strtolower((string) $unit->subject_name)];
            })
            ->values();
    }

    /**
     * Teacher-level summary buckets. A teacher is counted once:
     * late (any unit late) > pending (any unit pending) > submitted (all units).
     * The status filter is intentionally ignored so the buckets stay complete.
     *
     * @param  array<string, mixed>  $filters
     */
    public function summary(array $filters = []): object
    {
        $units = $this->units(array_diff_key($filters, array_flip(['status'])));

        $perTeacher = $units->groupBy('teacher_user_id');
        $total = $perTeacher->count();

        $submitted = 0;
        $pending = 0;
        $late = 0;

        foreach ($perTeacher as $teacherUnits) {
            if ($teacherUnits->contains(fn ($unit) => $unit->status === self::STATUS_LATE)) {
                $late++;
            } elseif ($teacherUnits->contains(fn ($unit) => $unit->status === self::STATUS_PENDING)) {
                $pending++;
            } else {
                $submitted++;
            }
        }

        $completion = $total > 0 ? (int) round($submitted / $total * 100) : 0;

        return (object) compact('total', 'submitted', 'pending', 'late', 'completion');
    }

    /**
     * Unit-level completion (percent of units fully submitted).
     *
     * @param  Collection<int, object>  $units
     */
    public function completion(Collection $units): int
    {
        $total = $units->count();

        if ($total === 0) {
            return 0;
        }

        $done = $units->where('status', self::STATUS_SUBMITTED)->count();

        return (int) round($done / $total * 100);
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_LATE => 'Late',
            default => 'Pending',
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function teachersWithAdvisory(array $filters): Collection
    {
        $teachers = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->select('u.id as user_id', 'u.name', 't.id as teacher_id', 't.advisory_class')
            ->get()
            ->map(function ($teacher) {
                $teacher->parsed = TeacherAssignmentController::parseAdvisory((string) ($teacher->advisory_class ?? ''));

                return $teacher;
            });

        if (! empty($filters['teacher'])) {
            $teachers = $teachers->where('user_id', (int) $filters['teacher']);
        }

        if (! empty($filters['grade_level'])) {
            $gradeLevel = (int) $filters['grade_level'];
            $teachers = $teachers->filter(fn ($teacher) => $teacher->parsed && (int) $teacher->parsed['grade'] === $gradeLevel);
        }

        if (! empty($filters['section'])) {
            $section = trim((string) $filters['section']);
            $teachers = $teachers->filter(fn ($teacher) => trim((string) ($teacher->advisory_class ?? '')) === $section);
        }

        return $teachers->values();
    }

    private function deadlinesFor(string $schoolYear, int $term): Collection
    {
        return GradeSubmissionDeadline::query()
            ->where('school_year', $schoolYear)
            ->where('term', $term)
            ->get()
            ->keyBy('subject_name');
    }

    private function deadlineForSubject(Collection $deadlines, string $subjectName): ?Carbon
    {
        $specific = $deadlines->get($subjectName);
        $deadline = $specific ?? $deadlines->get(GradeSubmissionDeadline::ALL_SUBJECTS);

        return $deadline?->deadline;
    }
}
