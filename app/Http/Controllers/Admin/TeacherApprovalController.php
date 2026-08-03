<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveTeacherRequest;
use App\Models\Setting;
use App\Models\Teacher;
use App\Models\TeacherApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class TeacherApprovalController extends Controller
{
    public function index(): View
    {
        $settings = Setting::find(1);
        $defaultMaxStudents = (int) ($settings->max_students_per_class ?? 30);
        $defaultMaxSubjects = (int) ($settings->max_subjects_per_teacher ?? 8);

        $teachers = DB::table('users')
            ->leftJoin('teachers', 'teachers.user_id', '=', 'users.id')
            ->where('users.role', 'teacher')
            ->where(function ($query) {
                $query->where('users.status', 'inactive')
                    ->orWhereNull('teachers.id');
            })
            ->select('users.id as user_id', 'users.name',
                DB::raw('COALESCE(teachers.advisory_class, \'\') as advisory_class'))
            ->orderBy('users.name')
            ->get();

        return view('admin.approve_teachers', [
            'teachers' => $teachers,
            'defaultMaxStudents' => $defaultMaxStudents,
            'defaultMaxSubjects' => $defaultMaxSubjects,
        ]);
    }

    public function approve(ApproveTeacherRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated) {
                $userId = (int) $validated['user_id'];
                $maxStudents = (int) $validated['max_students'];
                $maxSubjects = (int) $validated['max_subjects'];
                $advisoryClass = trim((string) ($validated['advisory_class'] ?? ''));

                $teacher = Teacher::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'advisory_class' => $advisoryClass,
                        'max_students' => $maxStudents,
                        'max_subjects' => $maxSubjects,
                        'status' => 'active',
                    ]
                );

                TeacherApproval::updateOrCreate(
                    ['teacher_id' => $teacher->id],
                    ['max_students' => $maxStudents, 'max_subjects' => $maxSubjects, 'status' => 'approved']
                );

                DB::table('users')
                    ->where('id', $userId)
                    ->where('role', 'teacher')
                    ->update(['status' => 'active']);
            });
        } catch (\Throwable $e) {
            flash_notice('Approval failed. Please try again.', 'error');

            return Redirect::route('admin.approve-teachers');
        }

        flash_notice('Teacher approved successfully.', 'success');

        return Redirect::route('admin.approve-teachers');
    }
}