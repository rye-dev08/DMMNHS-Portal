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

        $teachers = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->select('u.id as user_id', 'u.name', 't.advisory_class')
            ->orderBy('u.name')
            ->get();

        return view('admin.enrollment_settings', [
            'settings' => $settings,
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

        return redirect()->route('admin.enrollment-settings');
    }

    public function endSemester(): RedirectResponse
    {
        $settings = Setting::find(1);
        $currSem = (int) ($settings->current_semester ?? 1);

        DB::transaction(function () use ($currSem) {
            $settings = Setting::find(1);
            $currYear = (string) ($settings->current_school_year ?? '');

            $this->archiveSubjects($currSem, $currYear);
            $this->archiveGrades($currSem, $currYear);

            DB::table('enrollment_requests')->delete();
            DB::table('grades')->delete();
            DB::table('subjects')->delete();
            DB::table('teacher_subjects')->delete();

            DB::table('teachers')->update(['advisory_class' => null]);
            DB::table('students')->where('status', 'active')->update(['needs_reenrollment' => 'yes']);

            Setting::where('id', 1)->update(['current_semester' => $currSem + 1]);
        });

        $nextSem = $currSem + 1;
        flash_notice("Semester {$currSem} archived. New Semester {$nextSem} - System fully reset!", 'success');

        return redirect()->route('admin.enrollment-settings');
    }

    public function endSchoolYear(): RedirectResponse
    {
        DB::transaction(function () {
            $settings = Setting::find(1);
            $currSem = (int) ($settings->current_semester ?? 1);
            $currYear = (string) ($settings->current_school_year ?? '2024-2025');

            $this->archiveSubjects($currSem, $currYear);
            $this->archiveGrades($currSem, $currYear);

            $students = DB::table('students')->where('status', 'active')->get(['id', 'grade_level']);
            foreach ($students as $student) {
                $newGrade = (int) $student->grade_level + 1;

                if ($newGrade >= 14) {
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

            $yearParts = explode('-', $currYear);
            $startYear = (int) $yearParts[0];
            $nextYear = ($startYear + 1) . '-' . ($startYear + 2);

            Setting::where('id', 1)->update([
                'current_semester' => 1,
                'current_school_year' => $nextYear,
            ]);
        });

        flash_notice('School year ended, new year reset!', 'success');

        return redirect()->route('admin.enrollment-settings');
    }

    private function archiveSubjects(int $semester, string $schoolYear): void
    {
        DB::insert(
            'INSERT INTO previous_semester_subjects
                (original_subject_id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, archived_semester, archived_school_year)
             SELECT id, student_id, teacher_id, subject_name, course_code, teacher_code, room_no, ?, ? FROM subjects',
            [$semester, $schoolYear]
        );
    }

    private function archiveGrades(int $semester, string $schoolYear): void
    {
        DB::insert(
            'INSERT INTO previous_semester_grades
                (original_grade_id, student_id, subject_id, grade, quarter, archived_semester, archived_school_year)
             SELECT id, student_id, subject_id, grade, quarter, ?, ? FROM grades',
            [$semester, $schoolYear]
        );
    }
}