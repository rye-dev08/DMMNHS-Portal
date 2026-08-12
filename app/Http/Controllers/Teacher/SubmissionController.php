<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\RequirementSubmission;
use App\Models\Teacher;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    private function owned(RequirementSubmission $submission): RequirementSubmission
    {
        $teacherId = (int) (Teacher::where('user_id', auth()->id())->first()->id ?? 0);
        abort_unless((int) $submission->teacher_id === $teacherId, 403);

        return $submission;
    }

    public function approve(RequirementSubmission $submission): RedirectResponse
    {
        $this->owned($submission);

        if ($submission->isApproved()) {
            flash_notice('This submission is already approved.', 'info');

            return back();
        }

        $submission->update([
            'status' => RequirementSubmission::STATUS_APPROVED,
            'reviewed_at' => now(),
        ]);

        app(NotificationService::class)->submissionApproved(
            (int) $submission->student_id,
            $submission->requirement->title,
            route('student.requirements.show', $submission->requirement_id)
        );

        flash_notice('Submission approved.', 'success');

        return back();
    }

    public function revision(Request $request, RequirementSubmission $submission): RedirectResponse
    {
        $this->owned($submission);

        $validated = $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        $submission->update([
            'status' => RequirementSubmission::STATUS_NEEDS_REVISION,
            'feedback' => $validated['feedback'],
            'reviewed_at' => now(),
        ]);

        app(NotificationService::class)->submissionNeedsRevision(
            (int) $submission->student_id,
            $submission->requirement->title,
            route('student.requirements.show', $submission->requirement_id)
        );

        flash_notice('Submission marked as needs revision with feedback.', 'success');

        return back();
    }

    public function download(RequirementSubmission $submission): StreamedResponse
    {
        $this->owned($submission);
        abort_if(! $submission->attachment, 404);

        return Storage::disk('public')->download($submission->attachment, $submission->attachment_name);
    }
}
