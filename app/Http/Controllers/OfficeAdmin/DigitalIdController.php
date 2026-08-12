<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Student;
use App\Services\DigitalIdService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DigitalIdController extends Controller
{
    /**
     * Searchable list of student digital IDs with live status, token state
     * and management actions.
     */
    public function index(Request $request, DigitalIdService $service): View
    {
        $q = trim((string) $request->input('q', ''));
        $statusFilter = (string) $request->input('status', '');

        $students = Student::with('user');

        if ($q !== '') {
            $students->where(function ($query) use ($q) {
                $query->where('student_id_no', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($user) use ($q) {
                        $user->where('name', 'like', "%{$q}%")
                            ->orWhere('username', 'like', "%{$q}%");
                    });
            });
        }

        $rows = $students->orderBy('id')->get()
            ->map(function (Student $student) use ($service) {
                $service->ensureStudentIdNo($student);
                $advisory = $service->advisoryParts($student);

                return (object) [
                    'student' => $student,
                    'user' => $student->user,
                    'student_id_no' => $student->student_id_no,
                    'status' => $service->statusFor($student),
                    'grade' => $advisory['grade'],
                    'section' => $advisory['section'],
                    'track' => $advisory['track'],
                    'has_token' => (bool) $student->id_token,
                    'token_generated_at' => $student->id_token_generated_at,
                ];
            })
            ->when($statusFilter !== '', fn (Collection $rows) => $rows->filter(
                fn ($row) => $row->status['state'] === $statusFilter
            ))
            ->values();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;
        $items = $rows->forPage($page, $perPage);

        $paginator = new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('office.digital_ids', [
            'rows' => $paginator,
            'q' => $q,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Full digital ID card preview for a single student.
     */
    public function show(Student $student, DigitalIdService $service): View
    {
        $token = $service->ensureToken($student);
        $period = Setting::find(1)?->period();

        return view('office.digital_id_show', [
            'student' => $student,
            'studentIdNo' => $service->ensureStudentIdNo($student),
            'advisory' => $service->advisoryParts($student),
            'status' => $service->statusFor($student),
            'schoolYear' => (string) ($period->school_year ?? ''),
            'term' => (int) ($period->term ?? 1),
            'qrSvg' => $service->qrSvg($service->verificationUrl($token), 96),
            'verifyUrl' => $service->verificationUrl($token),
            'token' => $token,
            'tokenGeneratedAt' => $student->id_token_generated_at,
        ]);
    }

    /**
     * Generate a fresh verification token (also assigns the ID number).
     */
    public function regenerate(Student $student, DigitalIdService $service): RedirectResponse
    {
        $service->ensureStudentIdNo($student);
        $service->regenerateToken($student);

        app(NotificationService::class)->digitalIdGenerated($student->id);

        flash_notice('Verification token regenerated. The QR code now points to a new URL.', 'success');

        return redirect()->route('office.digital-ids');
    }

    /**
     * Invalidate the verification token, making the ID unverifiable.
     */
    public function revoke(Student $student, DigitalIdService $service): RedirectResponse
    {
        $service->revokeToken($student);

        flash_notice('Verification token revoked. Scan this ID now returns "Invalid ID".', 'success');

        return redirect()->route('office.digital-ids');
    }
}
