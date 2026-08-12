<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Models\GradeSubmissionDeadline;
use App\Models\User;
use App\Services\GradeSubmissionMonitorService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeSubmissionMonitorController extends Controller
{
    public function index(Request $request): View
    {
        $service = app(GradeSubmissionMonitorService::class);
        $filters = $this->filters($request);

        $units = $service->units($filters);
        $summary = $service->summary($filters);
        $completion = $service->completion($units);

        $deadlines = GradeSubmissionDeadline::query()
            ->where('school_year', $filters['school_year'])
            ->where('term', $filters['term'])
            ->orderBy('subject_name')
            ->get();

        return view('office.grade_submission_monitor', [
            'filters' => $filters,
            'units' => $units,
            'summary' => $summary,
            'completion' => $completion,
            'deadlines' => $deadlines,
            'schoolYears' => $service->availableSchoolYears(),
            'terms' => $service->terms(),
            'gradeLevels' => $service->gradeLevels(),
            'teachers' => $service->teachers(),
            'subjects' => $service->subjects(),
            'sections' => $service->sections(),
        ]);
    }

    public function storeDeadline(Request $request): RedirectResponse
    {
        $request->validate([
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'subject_name' => ['nullable', 'string', 'max:100'],
            'deadline' => ['required', 'date'],
        ]);

        GradeSubmissionDeadline::updateOrCreate(
            [
                'school_year' => (string) $request->string('school_year'),
                'term' => (int) $request->integer('term'),
                'subject_name' => trim((string) $request->string('subject_name')),
            ],
            ['deadline' => $request->date('deadline')->toDateString()]
        );

        flash_notice('Submission deadline saved.', 'success');

        return back();
    }

    public function updateDeadline(Request $request, GradeSubmissionDeadline $deadline): RedirectResponse
    {
        $request->validate([
            'deadline' => ['required', 'date'],
        ]);

        $deadline->update([
            'deadline' => $request->date('deadline')->toDateString(),
        ]);

        flash_notice('Submission deadline updated.', 'success');

        return back();
    }

    public function destroyDeadline(GradeSubmissionDeadline $deadline): RedirectResponse
    {
        $deadline->delete();

        flash_notice('Submission deadline removed.', 'success');

        return back();
    }

    public function remind(Request $request): RedirectResponse
    {
        $request->validate([
            'teacher_user_id' => ['required', 'integer'],
            'subject_name' => ['required', 'string', 'max:100'],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'school_year' => ['required', 'string', 'max:20'],
        ]);

        $teacher = User::query()
            ->where('id', (int) $request->integer('teacher_user_id'))
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->first();

        if (! $teacher) {
            flash_notice('Teacher not found.', 'error');

            return back();
        }

        app(NotificationService::class)->gradeSubmissionReminder(
            (int) $teacher->id,
            (string) $request->string('subject_name'),
            (int) $request->integer('term'),
            (string) $request->string('school_year')
        );

        flash_notice("Reminder sent to {$teacher->name}.", 'success');

        return back();
    }

    public function remindAll(Request $request): RedirectResponse
    {
        $service = app(GradeSubmissionMonitorService::class);
        $filters = $this->filters($request);

        $units = $service->units($filters)
            ->filter(fn ($unit) => $unit->status !== GradeSubmissionMonitorService::STATUS_SUBMITTED);

        $sent = 0;
        foreach ($units->groupBy('teacher_user_id') as $userId => $teacherUnits) {
            $teacher = User::query()
                ->where('id', (int) $userId)
                ->where('role', 'teacher')
                ->where('status', 'active')
                ->first();

            if (! $teacher) {
                continue;
            }

            $subjectLabel = $teacherUnits->pluck('subject_name')->unique()->implode(', ');

            app(NotificationService::class)->gradeSubmissionReminder(
                (int) $teacher->id,
                $subjectLabel,
                (int) $filters['term'],
                (string) $filters['school_year']
            );

            $overdueUnits = $teacherUnits->filter(
                fn ($unit) => $unit->status === GradeSubmissionMonitorService::STATUS_LATE
            );

            if ($overdueUnits->isNotEmpty()) {
                app(NotificationService::class)->gradeSubmissionOverdue(
                    (int) $teacher->id,
                    $subjectLabel,
                    (int) $filters['term'],
                    (string) $filters['school_year']
                );
            }

            $sent++;
        }

        flash_notice("Reminders sent to {$sent} teacher(s).", 'success');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $service = app(GradeSubmissionMonitorService::class);

        return [
            'school_year' => trim((string) $request->string('school_year')) ?: $service->defaultSchoolYear(),
            'term' => (int) ($request->integer('term') ?: $service->defaultTerm()),
            'grade_level' => $request->integer('grade_level'),
            'section' => trim((string) $request->string('section')),
            'teacher' => $request->integer('teacher'),
            'subject' => trim((string) $request->string('subject')),
            'status' => trim((string) $request->string('status')),
        ];
    }
}
