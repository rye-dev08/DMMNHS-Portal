<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class InfoController extends Controller
{
    public function index(): View
    {
        $user = User::with('teacher')
            ->where('id', auth()->id())
            ->first();

        return view('teacher.info', ['user' => $user]);
    }
}