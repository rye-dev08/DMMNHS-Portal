<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarEvent;
use App\Models\ContactMessage;
use App\Models\EnrollmentRequest;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AnnouncementService;
use App\Services\GradeSubmissionMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unreadCount' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? '',
                    'message' => $n->data['message'] ?? '',
                    'read' => $n->read_at !== null,
                    'link' => $n->data['link'] ?? null,
                ]),
        ]);
    }

    public function announcements(Request $request): JsonResponse
    {
        $feed = app(AnnouncementService::class)->feed($request->user());

        return response()->json([
            'unreadCount' => $feed['unreadCount'],
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $user = $request->user();

        $pendingCount = ContactMessage::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);

                if ($user->role === 'office_admin') {
                    $q->orWhereNull('user_id');
                }
            })
            ->where('status', ContactMessage::STATUS_PENDING)
            ->whereNull('archived_at')
            ->count();

        return response()->json([
            'pendingCount' => $pendingCount,
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $period = Setting::find(1)?->period();
        $data = [];

        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();

            if ($student) {
                $data['requirements'] = Requirement::query()
                    ->where('school_year', (string) ($period->school_year ?? ''))
                    ->where('term', (int) ($period->term ?? 1))
                    ->where('status', Requirement::STATUS_ACTIVE)
                    ->whereIn('teacher_id', function ($q) use ($student) {
                        $q->select('teacher_id')
                            ->from('enrollment_requests')
                            ->where('student_id', $student->id)
                            ->where('status', 'approved');
                    })
                    ->count();

                $data['pendingSubmissions'] = RequirementSubmission::query()
                    ->where('student_id', $student->id)
                    ->whereIn('status', [
                        RequirementSubmission::STATUS_SUBMITTED,
                        RequirementSubmission::STATUS_RESUBMITTED,
                    ])
                    ->count();
            }
        } elseif ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                $data['pendingSubmissions'] = RequirementSubmission::query()
                    ->whereIn('status', [
                        RequirementSubmission::STATUS_SUBMITTED,
                        RequirementSubmission::STATUS_RESUBMITTED,
                    ])
                    ->whereHas('requirement', function ($q) use ($teacher) {
                        $q->where('teacher_id', $teacher->id);
                    })
                    ->count();

                $data['pendingMessages'] = ContactMessage::query()
                    ->where('status', ContactMessage::STATUS_PENDING)
                    ->whereNull('archived_at')
                    ->count();
            }
        } elseif ($user->role === 'office_admin') {
            $data['pendingMessages'] = ContactMessage::query()
                ->where('status', ContactMessage::STATUS_PENDING)
                ->whereNull('archived_at')
                ->count();

            $data['pendingSubmissions'] = RequirementSubmission::query()
                ->whereIn('status', [
                    RequirementSubmission::STATUS_SUBMITTED,
                    RequirementSubmission::STATUS_RESUBMITTED,
                ])
                ->count();

            $data['upcomingEvents'] = AcademicCalendarEvent::query()
                ->where('school_year', (string) ($period->school_year ?? ''))
                ->whereDate('event_date', '>=', now()->toDateString())
                ->count();
        }

        return response()->json($data);
    }

    public function gradeSubmissions(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'teacher') {
            return response()->json([]);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (! $teacher) {
            return response()->json([]);
        }

        $service = app(GradeSubmissionMonitorService::class);
        $filters = [
            'school_year' => (string) ($request->query('school_year') ?? $service->defaultSchoolYear()),
            'term' => (int) ($request->query('term') ?? $service->defaultTerm()),
            // Scope the poll to the current teacher so other teachers' pending
            // and late submissions are never exposed to this endpoint.
            'teacher' => (int) $teacher->id,
        ];

        $units = $service->units($filters)
            ->filter(fn ($unit) => $unit->status !== GradeSubmissionMonitorService::STATUS_SUBMITTED);

        $summary = $service->summary($filters);

        return response()->json([
            'units' => $units->values()->map(fn ($unit) => [
                'subject_name' => $unit->subject_name,
                'status' => $unit->status,
                'teacher_user_id' => $unit->teacher_user_id,
                'teacher_name' => $unit->teacher_name,
            ]),
            'summary' => [
                'total' => $summary['total'],
                'submitted' => $summary['submitted'],
                'pending' => $summary['pending'],
                'late' => $summary['late'],
            ],
        ]);
    }

    public function enrollmentRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'teacher') {
            return response()->json([]);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (! $teacher) {
            return response()->json([]);
        }

        $count = EnrollmentRequest::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'pendingCount' => $count,
        ]);
    }
}
