<?php

namespace App\Http\Controllers;

use App\Services\DigitalIdService;
use Illuminate\View\View;

/**
 * Public, unauthenticated student ID verification reached by scanning the
 * QR code on a digital ID. Only ever exposes the minimal information needed
 * to confirm identity against the current database state.
 */
class StudentVerificationController extends Controller
{
    public function show(string $token, DigitalIdService $service): View
    {
        $data = $service->verificationData($token);

        return view('verify.student', [
            'data' => $data ?? ['state' => DigitalIdService::STATE_INVALID],
        ]);
    }
}
