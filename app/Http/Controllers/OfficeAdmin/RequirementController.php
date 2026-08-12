<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Services\RequirementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Read-only oversight of the Requirement & Submission Tracker for the
 * Office Administrator. Teachers keep full management of their own
 * requirements; this surface gives the registrar a school-wide view.
 */
class RequirementController extends Controller
{
    public function index(Request $request): View
    {
        $period = Setting::find(1)->period();
        $service = app(RequirementService::class);

        $q = trim((string) $request->input('q', ''));
        $type = (string) $request->input('type', '');

        $query = Requirement::query()
            ->with('teacher.user')
            ->where('school_year', $period->school_year)
            ->where('term', $period->term)
            ->where('status', Requirement::STATUS_ACTIVE);

        if ($type !== '' && array_key_exists($type, Requirement::TYPES)) {
            $query->where('requirement_type', $type);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%'.$q.'%')
                    ->orWhereHas('teacher.user', function ($user) use ($q) {
                        $user->where('name', 'like', '%'.$q.'%');
                    });
            });
        }

        $requirements = $query
            ->with('submissions')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $requirements->getCollection()->transform(function (Requirement $requirement) use ($service) {
            $requirement->progress = $service->progress($requirement);
            $requirement->teacher_name = $requirement->teacher?->user?->name ?? '—';

            return $requirement;
        });

        $pendingReview = RequirementSubmission::query()
            ->whereIn('status', [
                RequirementSubmission::STATUS_SUBMITTED,
                RequirementSubmission::STATUS_RESUBMITTED,
            ])
            ->count();

        $summary = (object) [
            'total' => Requirement::query()
                ->where('school_year', $period->school_year)
                ->where('term', $period->term)
                ->where('status', Requirement::STATUS_ACTIVE)
                ->count(),
            'pendingReview' => $pendingReview,
            'needsRevision' => RequirementSubmission::query()
                ->where('status', RequirementSubmission::STATUS_NEEDS_REVISION)
                ->count(),
            'approved' => RequirementSubmission::query()
                ->where('status', RequirementSubmission::STATUS_APPROVED)
                ->count(),
        ];

        return view('office.requirements', [
            'requirements' => $requirements,
            'summary' => $summary,
            'types' => Requirement::TYPES,
            'q' => $q,
            'type' => $type,
        ]);
    }

    public function show(Requirement $requirement): View
    {
        $service = app(RequirementService::class);
        $students = $service->assignedStudents((int) $requirement->teacher_id);
        $submissions = $requirement->submissions()->get()->keyBy('student_id');

        $rows = $students->map(function ($student) use ($submissions) {
            $submission = $submissions->get((int) $student->id);

            return (object) [
                'student_id' => (int) $student->id,
                'student_name' => $student->name,
                'grade_level' => (int) ($student->grade_level ?? 0),
                'submission' => $submission,
                'status' => $submission ? $submission->status : RequirementSubmission::STATUS_PENDING,
            ];
        });

        return view('office.requirements_show', [
            'requirement' => $requirement,
            'teacherName' => $requirement->teacher?->user?->name ?? '—',
            'rows' => $rows,
            'progress' => $service->progress($requirement),
        ]);
    }

    public function download(Requirement $requirement): StreamedResponse
    {
        abort_if(! $requirement->attachment, 404);

        return Storage::disk('public')
            ->download($requirement->attachment, $requirement->attachment_name);
    }
}
