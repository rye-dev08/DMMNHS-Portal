<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
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
        app(NotificationService::class)->termChanged($nextTerm);
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
            DB::table('teachers')->update(['advisory_class' => null]);

            Setting::where('id', 1)->update(['enrollment_phase' => 'enrollment']);
        });

        app(NotificationService::class)->enrollmentPhaseOpened();

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

        app(NotificationService::class)->enrollmentPhaseClosed();

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

        $startedYear = '';
        $oldYear = (string) ($settings->current_school_year ?? '2024-2025');

        DB::transaction(function () use (&$startedYear, $oldYear) {
            $settings = Setting::find(1);

            $yearParts = explode('-', $oldYear !== '' ? $oldYear : '2024-2025');
            $startYear = (int) ($yearParts[0] ?: 2024);
            $nextYear = ($startYear + 1).'-'.($startYear + 2);
            $startedYear = $nextYear;

            // Increment teacher grade_level for new school year
            DB::table('teachers')
                ->where('grade_level', '>=', 7)
                ->where('grade_level', '<', 12)
                ->increment('grade_level');

            // Teachers at grade 12 move to graduated (set to null or 13+)
            DB::table('teachers')
                ->where('grade_level', 12)
                ->update(['grade_level' => null]);

            DB::table('teachers')->update(['advisory_class' => null]);

            Setting::where('id', 1)->update([
                'current_term' => 1,
                'current_school_year' => $nextYear,
                'enrollment_phase' => 'none',
            ]);
        });

        app(NotificationService::class)->newSchoolYearStarted($startedYear);
        app(NotificationService::class)->schoolYearChanged($oldYear, $startedYear);

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
