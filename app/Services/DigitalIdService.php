<?php

namespace App\Services;

use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Centralised logic for the Digital Student ID feature: stable student ID
 * numbers, secure non-guessable verification tokens, QR generation, section /
 * strand derivation and dynamic verification status.
 *
 * Everything is derived live from the existing student, enrollment, advisory
 * and settings systems so the ID always reflects current database state.
 */
class DigitalIdService
{
    public const STATE_VALID = 'valid';

    public const STATE_INACTIVE = 'inactive';

    public const STATE_NOT_ENROLLED = 'not_enrolled';

    public const STATE_INVALID = 'invalid';

    /**
     * Assign a stable student ID number if one does not exist yet.
     */
    public function ensureStudentIdNo(Student $student): string
    {
        if ($student->student_id_no) {
            return $student->student_id_no;
        }

        $prefix = (int) date('Y');
        $setting = Setting::find(1);
        $year = $setting?->current_school_year ? (string) $setting->current_school_year : '';
        if ($year !== '' && str_contains($year, '-')) {
            $prefix = (int) explode('-', $year)[0];
        }

        $student->student_id_no = $prefix.'-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT);
        $student->save();

        return $student->student_id_no;
    }

    /**
     * Ensure the student has a verification token, creating one on first use.
     */
    public function ensureToken(Student $student): string
    {
        if ($student->id_token) {
            return $student->id_token;
        }

        return $this->regenerateToken($student);
    }

    /**
     * Replace the verification token with a fresh random one.
     */
    public function regenerateToken(Student $student): string
    {
        $student->id_token = Str::random(64);
        $student->id_token_generated_at = now();
        $student->save();

        return $student->id_token;
    }

    /**
     * Permanently invalidate the verification token.
     */
    public function revokeToken(Student $student): void
    {
        $student->id_token = null;
        $student->id_token_generated_at = null;
        $student->save();
    }

    /**
     * Look up a student by their (secure, non-guessable) verification token.
     */
    public function findByToken(string $token): ?Student
    {
        return Student::with('user')->where('id_token', $token)->first();
    }

    /**
     * The student's current section from the existing advisory system.
     */
    public function sectionFor(Student $student): ?string
    {
        return app(AnnouncementService::class)->studentSection((int) $student->id);
    }

    /**
     * Grade / section / strand breakdown for the ID card. Handles both the
     * "Grade 11-Rizal (Academic)" production format and legacy "7-A" style.
     */
    public function advisoryParts(Student $student): array
    {
        $grade = $student->grade_level !== null ? (int) $student->grade_level : 0;
        $section = $this->sectionFor($student);

        if ($section === null) {
            return [
                'grade' => $grade,
                'section' => null,
                'track' => null,
                'level' => $grade >= 11 ? 'SHS' : 'JHS',
            ];
        }

        $parsed = TeacherAssignmentController::parseAdvisory($section);
        if ($parsed !== null) {
            return [
                'grade' => (int) $parsed['grade'],
                'section' => (string) $parsed['section'],
                'track' => $parsed['track'] !== null ? (string) $parsed['track'] : null,
                'level' => (string) $parsed['level'],
            ];
        }

        $clean = trim((string) $section);
        $clean = preg_replace('/\s*\(.+?\)\s*$/', '', $clean);
        $clean = preg_replace('/^Grade\s+\d+\s*[-–]\s*/i', '', $clean);

        return [
            'grade' => $grade,
            'section' => $clean !== '' ? $clean : null,
            'track' => null,
            'level' => $grade >= 11 ? 'SHS' : 'JHS',
        ];
    }

    /**
     * Live verification status computed from the current database state:
     * active user/student -> enrolled -> otherwise derived.
     */
    public function statusFor(Student $student): array
    {
        $studentStatus = strtolower((string) $student->status);
        $userStatus = $student->user ? strtolower((string) $student->user->status) : 'active';

        if ($studentStatus === 'inactive' || $userStatus === 'inactive') {
            return ['state' => self::STATE_INACTIVE, 'label' => 'Inactive'];
        }

        $enrolled = DB::table('enrollment_requests')
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->exists();

        if (! $enrolled) {
            return ['state' => self::STATE_NOT_ENROLLED, 'label' => 'Not Currently Enrolled'];
        }

        return ['state' => self::STATE_VALID, 'label' => 'Active'];
    }

    /**
     * Minimal public data exposed by the public verification endpoint.
     * Returns null when the token is unknown/invalid.
     */
    public function verificationData(string $token): ?array
    {
        $student = $this->findByToken($token);

        if ($student === null || $student->user === null) {
            return null;
        }

        $status = $this->statusFor($student);
        $advisory = $this->advisoryParts($student);
        $setting = Setting::find(1);
        $period = $setting !== null ? $setting->period() : null;

        return [
            'student' => $student,
            'state' => $status['state'],
            'status_label' => $status['label'],
            'grade' => $advisory['grade'],
            'section' => $advisory['section'],
            'track' => $advisory['track'],
            'school_year' => (string) ($period->school_year ?? ''),
            'term' => (int) ($period->term ?? 1),
            'verified_at' => now(),
        ];
    }

    /**
     * The verification URL encoded into the QR code.
     */
    public function verificationUrl(string $token): string
    {
        return route('verify.student', $token);
    }

    /**
     * Render the QR as a clean inline SVG (no XML prolog) for embedding.
     */
    public function qrSvg(string $content, int $size = 96): string
    {
        $svg = (string) QrCode::size($size)->generate($content);

        return preg_replace('/<\?xml[^?]*\?>\s*/', '', $svg) ?? $svg;
    }
}
