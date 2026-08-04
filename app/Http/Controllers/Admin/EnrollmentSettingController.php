<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentSettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::find(1);

        return view('admin.enrollment_settings', [
            'settings' => $settings,
        ]);
    }

    public function advisory(): View
    {
        $teachers = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->select('u.id as user_id', 'u.name', 't.advisory_class')
            ->orderBy('u.name')
            ->get();

        return view('admin.teacher_advisory', [
            'teachers' => $teachers,
        ]);
    }

    public function saveAdvisory(Request $request): RedirectResponse
    {
        $teacherUserId = (int) $request->integer('teacher_user_id');
        $advisoryClass = trim((string) $request->string('advisory_class'));

        if ($teacherUserId > 0) {
            DB::table('teachers')->where('user_id', $teacherUserId)->update([
                'advisory_class' => $advisoryClass,
            ]);
            flash_notice('Advisory class saved!', 'success');
        }

        return redirect()->route('admin.teacher-advisory');
    }

    public function endTerm(): RedirectResponse
    {
        $settings = Setting::find(1);
        $period = $settings->period();

        if (! $period->can_new_term) {
            flash_notice('New Term is only available during Term 1 or 2.', 'error');

            return redirect()->route('admin.enrollment-settings');
        }

        $currTerm = (int) ($settings->current_term ?? 1);

        DB::transaction(function () use ($currTerm) {
            $settings = Setting::find(1);
            $currYear = (string) ($settings->current_school_year ?? '');

            $this->archiveSubjects($currTerm, $currYear);
            $this->archiveGrades($currTerm, $currYear);

            DB::table('enrollment_requests')->delete();
            DB::table('grades')->delete();
            DB::table('subjects')->delete();
            DB::table('teacher_subjects')->delete();

            DB::table('teachers')->update(['advisory_class' => null]);
            DB::table('students')->where('status', 'active')->update(['needs_reenrollment' => 'yes']);

            Setting::where('id', 1)->update(['current_term' => $currTerm + 1]);
        });

        $nextTerm = $currTerm + 1;
        flash_notice("Term {$currTerm} archived to history. New Term {$nextTerm} - System fully reset!", 'success');

        return redirect()->route('admin.enrollment-settings');
    }

    public function endSchoolYear(): RedirectResponse
    {
        $settings = Setting::find(1);
        $period = $settings->period();

        if (! $period->can_end_school_year) {
            flash_notice('End School Year is only available during Term 3.', 'error');

            return redirect()->route('admin.enrollment-settings');
        }

        DB::transaction(function () {
            $settings = Setting::find(1);
            $currTerm = (int) ($settings->current_term ?? 1);
            $currYear = (string) ($settings->current_school_year ?? '2024-2025');

            $this->archiveSubjects($currTerm, $currYear);
            $this->archiveGrades($currTerm, $currYear);

            $students = DB::table('students')->where('status', 'active')->get(['id', 'user_id', 'grade_level']);
            foreach ($students as $student) {
                $newGrade = (int) $student->grade_level + 1;

                if ($newGrade >= 14) {
                    DB::table('graduated_students')->insert([
                        'user_id' => $student->user_id,
                        'graduation_grade' => (int) $student->grade_level,
                        'graduation_term' => $currTerm,
                        'graduation_school_year' => $currYear,
                        'graduation_date' => now()->toDateString(),
                    ]);

                    DB::table('students')->where('id', $student->id)->delete();
                } else {
                    DB::table('students')->where('id', $student->id)->update([
                        'grade_level' => $newGrade,
                        'needs_reenrollment' => 'yes',
                    ]);
                }
            }

            DB::table('enrollment_requests')->whereIn('status', ['pending', 'approved'])->delete();
            DB::table('subjects')->delete();
            DB::table('teacher_subjects')->delete();
            DB::table('grades')->delete();

            Setting::where('id', 1)->update(['enrollment_phase' => 'enrollment']);
        });

        flash_notice('School year ended. Enrollment phase is now open.', 'success');

        return redirect()->route('admin.enrollment-settings');
    }

    public function endEnrollmentPhase(): RedirectResponse
    {
        $settings = Setting::find(1);
        $period = $settings->period();

        if (! $period->can_end_enrollment_phase) {
            flash_notice('End Enrollment Phase is only available while the enrollment phase is open.', 'error');

            return redirect()->route('admin.enrollment-settings');
        }

        Setting::where('id', 1)->update(['enrollment_phase' => 'closed']);

        flash_notice('Enrollment phase closed. You may now start the new school year.', 'success');

        return redirect()->route('admin.enrollment-settings');
    }

    public function newSchoolYear(): RedirectResponse
    {
        $settings = Setting::find(1);
        $period = $settings->period();

        if (! $period->can_new_school_year) {
            flash_notice('New School Year is only available after the enrollment phase has been closed.', 'error');

            return redirect()->route('admin.enrollment-settings');
        }

        DB::transaction(function () {
            $settings = Setting::find(1);
            $currYear = (string) ($settings->current_school_year ?? '2024-2025');

            $yearParts = explode('-', $currYear);
            $startYear = (int) $yearParts[0];
            $nextYear = ($startYear + 1) . '-' . ($startYear + 2);

            Setting::where('id', 1)->update([
                'current_term' => 1,
                'current_school_year' => $nextYear,
                'enrollment_phase' => 'none',
            ]);
        });

        flash_notice('New school year started!', 'success');

        return redirect()->route('admin.enrollment-settings');
    }

    private function archiveSubjects(int $term, string $schoolYear): void
    {
        DB::insert(
            'INSERT INTO previous_term_subjects
                (original_subject_id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, archived_term, archived_school_year)
             SELECT id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, ?, ? FROM subjects',
            [$term, $schoolYear]
        );
    }

    private function archiveGrades(int $term, string $schoolYear): void
    {
        DB::insert(
            'INSERT INTO previous_term_grades
                (original_grade_id, student_id, subject_id, grade, remarks, quarter, archived_term, archived_school_year)
             SELECT id, student_id, subject_id, grade, remarks, quarter, ?, ? FROM grades',
            [$term, $schoolYear]
        );
    }
}