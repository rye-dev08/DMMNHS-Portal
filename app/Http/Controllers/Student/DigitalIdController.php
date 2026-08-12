<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Student;
use App\Services\DigitalIdService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DigitalIdController extends Controller
{
    /**
     * Show the student's digital ID card with its scannable QR code.
     */
    public function show(DigitalIdService $service): View
    {
        $student = $this->currentStudent();
        $token = $service->ensureToken($student);
        $period = Setting::find(1)?->period();

        return view('student.digital_id', [
            'student' => $student,
            'studentIdNo' => $service->ensureStudentIdNo($student),
            'advisory' => $service->advisoryParts($student),
            'status' => $service->statusFor($student),
            'schoolYear' => (string) ($period->school_year ?? ''),
            'term' => (int) ($period->term ?? 1),
            'qrSvg' => $service->qrSvg($service->verificationUrl($token), 96),
            'verifyUrl' => $service->verificationUrl($token),
            'tokenGeneratedAt' => $student->id_token_generated_at,
        ]);
    }

    /**
     * Upload / replace the student's profile photo used on the ID card.
     */
    public function uploadPhoto(Request $request, DigitalIdService $service): RedirectResponse
    {
        $student = $this->currentStudent();

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        try {
            $oldPhoto = $student->photo;

            $student->photo = $request->file('photo')->store('student-photos', 'public');
            $student->save();

            if ($oldPhoto !== null) {
                Storage::disk('public')->delete($oldPhoto);
            }

            $service->ensureToken($student);

            app(NotificationService::class)->digitalIdGenerated($student->id);
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to upload the photo. Please check the file and try again.', 'error');

            return redirect()->route('student.digital-id');
        }

        flash_notice('Profile photo updated. Your digital ID has been refreshed.', 'success');

        return redirect()->route('student.digital-id');
    }

    private function currentStudent(): Student
    {
        $student = auth()->user()?->student;

        abort_if($student === null, 404);

        return $student;
    }
}
