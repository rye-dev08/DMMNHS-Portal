<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\RequirementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequirementController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('user_id', auth()->id())->first();
    }

    private function studentId(): int
    {
        return (int) ($this->student()->id ?? 0);
    }

    /**
     * Teachers whose approved enrollment the current student belongs to.
     */
    private function enrolledTeacherIds(int $studentId): array
    {
        return DB::table('enrollment_requests')
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->pluck('teacher_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function isEnrolled(Requirement $requirement, int $studentId): bool
    {
        return in_array((int) $requirement->teacher_id, $this->enrolledTeacherIds($studentId), true);
    }

    private function currentRequirements(int $studentId): array
    {
        $period = Setting::find(1)->period();
        $teacherIds = $this->enrolledTeacherIds($studentId);

        return [$period, $teacherIds];
    }

    public function index(): View|RedirectResponse
    {
        $studentId = $this->studentId();

        if ($studentId === 0) {
            flash_notice('Student profile not found. Contact admin.', 'error');

            return redirect()->route('student.dashboard');
        }

        [$period, $teacherIds] = $this->currentRequirements($studentId);
        $service = app(RequirementService::class);

        $requirements = Requirement::with('submissions')
            ->where('school_year', $period->school_year)
            ->where('term', $period->term)
            ->where('status', Requirement::STATUS_ACTIVE)
            ->whereIn('teacher_id', $teacherIds)
            ->orderByDesc('created_at')
            ->get();

        $requirements->each(function (Requirement $requirement) use ($service, $studentId) {
            $submission = $requirement->submissions->firstWhere('student_id', $studentId);
            $status = $service->effectiveStatus($submission);

            $requirement->submission = $submission;
            $requirement->effective_status = $status;
            $requirement->has_submitted = $submission !== null;
            $requirement->can_submit = $service->canSubmit($requirement, $submission);
            $requirement->is_pending = $status === RequirementSubmission::STATUS_PENDING;
            $requirement->is_overdue = $requirement->isOverdue();
            $requirement->is_due_soon = $requirement->isDueSoon();
            $requirement->is_approved = $status === RequirementSubmission::STATUS_APPROVED;
        });

        app(RequirementService::class)->notifyDueReminders(
            $studentId,
            $requirements,
            app(NotificationService::class)
        );

        return view('student.requirements', [
            'requirements' => $requirements,
            'period' => $period,
        ]);
    }

    public function show(Requirement $requirement): View|RedirectResponse
    {
        $studentId = $this->studentId();

        if ($studentId === 0) {
            flash_notice('Student profile not found. Contact admin.', 'error');

            return redirect()->route('student.dashboard');
        }

        abort_unless($this->isEnrolled($requirement, $studentId), 403);

        $period = Setting::find(1)->period();

        if ((string) $requirement->school_year !== $period->school_year || (int) $requirement->term !== $period->term) {
            abort(403);
        }

        $service = app(RequirementService::class);
        $submission = $requirement->submissions()->where('student_id', $studentId)->first();

        return view('student.requirements_show', [
            'requirement' => $requirement,
            'submission' => $submission,
            'effective_status' => $service->effectiveStatus($submission),
            'can_submit' => $service->canSubmit($requirement, $submission),
            'is_overdue' => $requirement->isOverdue(),
        ]);
    }

    public function submit(Request $request, Requirement $requirement): RedirectResponse
    {
        $studentId = $this->studentId();

        if ($studentId === 0) {
            flash_notice('Student profile not found. Contact admin.', 'error');

            return redirect()->route('student.dashboard');
        }

        abort_unless($this->isEnrolled($requirement, $studentId), 403);

        if (! $requirement->submission_required) {
            flash_notice('This requirement does not require a submission.', 'info');

            return back();
        }

        $submission = $requirement->submissions()->where('student_id', $studentId)->first();

        if (! app(RequirementService::class)->canSubmit($requirement, $submission)) {
            flash_notice('Your submission is already being reviewed or approved.', 'error');

            return back();
        }

        $validated = $request->validate([
            'response_text' => ['nullable', 'string', 'max:20000'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        if (blank($validated['response_text'] ?? null) && ! $request->hasFile('attachment')) {
            flash_notice('Provide a response text and/or attach a file.', 'error');

            return back()->withErrors(['response_text' => 'A response or file is required.']);
        }

        $attachmentPath = null;
        $attachmentName = null;

        try {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = $file->store('requirement-submissions', 'public');
                $attachmentName = $file->getClientOriginalName();
            }
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to upload the attachment. Please check the file and try again.', 'error');

            return back()->withInput();
        }

        $data = [
            'response_text' => $validated['response_text'] ?? null,
            'attachment' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'feedback' => null,
        ];

        try {
            if ($submission === null) {
                $requirement->submissions()->create(array_merge($data, [
                    'student_id' => $studentId,
                    'teacher_id' => $requirement->teacher_id,
                    'status' => RequirementSubmission::STATUS_SUBMITTED,
                ]));
            } else {
                $submission->update(array_merge($data, [
                    'status' => $submission->isNeedsRevision()
                        ? RequirementSubmission::STATUS_RESUBMITTED
                        : RequirementSubmission::STATUS_SUBMITTED,
                ]));
            }
        } catch (\Throwable $e) {
            report($e);

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            flash_notice('Unable to save your submission. Please try again.', 'error');

            return back()->withInput();
        }

        flash_notice('Requirement submitted successfully.', 'success');

        app(NotificationService::class)->requirementSubmitted(
            $studentId,
            $requirement->title,
            route('student.requirements.show', $requirement->id)
        );

        return redirect()->route('student.requirements.show', $requirement->id);
    }

    public function download(Requirement $requirement): StreamedResponse
    {
        $studentId = $this->studentId();
        abort_unless($studentId !== 0 && $this->isEnrolled($requirement, $studentId), 403);
        abort_if(! $requirement->attachment, 404);

        return Storage::disk('public')->download($requirement->attachment, $requirement->attachment_name);
    }

    public function downloadSubmission(Requirement $requirement): StreamedResponse
    {
        $studentId = $this->studentId();
        abort_unless($studentId !== 0 && $this->isEnrolled($requirement, $studentId), 403);

        $submission = $requirement->submissions()->where('student_id', $studentId)->first();
        abort_unless($submission && $submission->attachment, 404);

        return Storage::disk('public')->download($submission->attachment, $submission->attachment_name);
    }
}
