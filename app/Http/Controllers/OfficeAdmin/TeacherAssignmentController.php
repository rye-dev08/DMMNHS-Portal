<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    public function advisory(): View
    {
        $filter = request('filter', 'all');

        $teachers = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->whereNotNull('t.advisory_class')
            ->where('t.advisory_class', '!=', '')
            ->select('u.id as user_id', 'u.name', 't.advisory_class')
            ->orderBy('u.name')
            ->get()
            ->filter(function ($t) use ($filter) {
                $parsed = self::parseAdvisory($t->advisory_class ?? '');
                if (! $parsed) {
                    return false;
                }
                if ($filter === 'jhs') {
                    return $parsed['grade'] <= 10;
                }
                if ($filter === 'shs') {
                    return $parsed['grade'] >= 11;
                }

                return true;
            })
            ->sortByDesc(function ($t) {
                $parsed = self::parseAdvisory($t->advisory_class ?? '');

                return $parsed ? $parsed['grade'] : 0;
            });

        return view('office.teacher_advisory', [
            'teachers' => $teachers,
            'filter' => $filter,
        ]);
    }

    public static function parseAdvisory(string $advisoryClass): ?array
    {
        if (! $advisoryClass) {
            return null;
        }
        if (! preg_match('/^Grade\s+(\d+)-(.+)$/', trim($advisoryClass), $m)) {
            return null;
        }
        $grade = (int) $m[1];
        $rest = $m[2];

        $track = null;
        if (preg_match('/\s*\((\w+)\)\s*$/', $rest, $trackMatch)) {
            $track = $trackMatch[1];
            $rest = preg_replace('/\s*\([\w]+\)\s*$/', '', $rest);
        }
        $rest = trim($rest);

        return [
            'grade' => $grade,
            'section' => $rest,
            'level' => $grade >= 11 ? 'SHS' : 'JHS',
            'track' => $track,
        ];
    }

    /**
     * Check whether a section name is already taken by ANY class across all
     * grade levels, ignoring capitalization and leading/trailing whitespace.
     * A section may therefore only be used once in the whole school.
     *
     * Used by both the Assign Class (create) and Edit Modal flows so the
     * uniqueness rule stays identical everywhere. The comparison is done in
     * PHP (via parseAdvisory) instead of a REGEXP so it works on MySQL and
     * SQLite test databases alike, and never mistakes a section that merely
     * starts with the same characters (e.g. "A" vs "AB").
     */
    public static function sectionExists(string $section, ?int $excludeUserId = null): bool
    {
        $section = trim($section);
        $normalized = self::normalizeSection($section);

        if ($normalized === '') {
            return false;
        }

        $query = DB::table('teachers')
            ->whereNotNull('advisory_class')
            ->where('advisory_class', '!=', '')
            ->select('user_id', 'advisory_class');

        if ($excludeUserId !== null) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        return $query->get()->contains(function ($row) use ($normalized) {
            $parsed = self::parseAdvisory((string) $row->advisory_class);

            return $parsed !== null && self::normalizeSection($parsed['section']) === $normalized;
        });
    }

    /**
     * Lowercase + trim a section name so comparisons ignore case and whitespace.
     */
    private static function normalizeSection(string $value): string
    {
        $value = trim($value);

        return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
    }

    /**
     * Distinct section names currently in use, for display in the Assign
     * Class form so the admin can see at a glance which names are taken.
     */
    public static function takenSections(): array
    {
        return DB::table('teachers')
            ->whereNotNull('advisory_class')
            ->where('advisory_class', '!=', '')
            ->orderBy('advisory_class')
            ->pluck('advisory_class')
            ->map(function ($value) {
                $parsed = self::parseAdvisory((string) $value);

                return $parsed['section'] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function assignClass(): View
    {
        // Only show teachers who DON'T have an advisory class assigned
        $teachers = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->whereNull('t.advisory_class')
            ->select('u.id as user_id', 'u.name', 't.advisory_class')
            ->orderBy('u.name')
            ->get();

        return view('office.assign_class', [
            'teachers' => $teachers,
            'takenSections' => self::takenSections(),
        ]);
    }

    /**
     * Shared data needed to render the edit-advisory modal partial.
     */
    private function editModalData(int $teacherUserId): array
    {
        $teacher = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->where('u.id', $teacherUserId)
            ->select('u.id as user_id', 'u.name', 't.advisory_class')
            ->first();

        abort_unless((bool) $teacher, 404);

        $parsed = null;
        if ($teacher->advisory_class) {
            $parsed = self::parseAdvisory($teacher->advisory_class);
        }

        // All teachers for reference (but we only edit the selected one)
        $allTeachers = DB::table('users as u')
            ->join('teachers as t', 't.user_id', '=', 'u.id')
            ->where('u.role', 'teacher')
            ->where('u.status', 'active')
            ->where('t.status', 'active')
            ->select('u.id as user_id', 'u.name', 't.advisory_class')
            ->orderBy('u.name')
            ->get();

        return [
            'teacher' => $teacher,
            'parsed' => $parsed,
            'allTeachers' => $allTeachers,
        ];
    }

    /**
     * Render the edit modal with validation errors bound so the office admin
     * sees the error inline inside the form (visible over the modal backdrop)
     * instead of a toast behind the blurred background.
     */
    private function editModalError(Request $request, array $errors, int $teacherUserId): Response
    {
        $request->flash();

        $view = view('office.edit_advisory_modal', $this->editModalData($teacherUserId))
            ->withErrors($errors);

        return response($view->render(), 422);
    }

    public function editAdvisory(int $teacherUserId): View
    {
        return view('office.edit_advisory_modal', $this->editModalData($teacherUserId));
    }

    public function updateAdvisory(Request $request, int $teacherUserId): RedirectResponse|Response|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'grade_level' => 'required|integer|in:7,8,9,10,11,12',
            'section_name' => 'required|string|max:255',
            'track' => 'nullable|string|in:Academic,TVL',
        ]);

        if ($validator->fails()) {
            return $this->editModalError($request, $validator->errors()->toArray(), $teacherUserId);
        }

        $grade = (int) $validator->validated()['grade_level'];
        $section = trim((string) $request->input('section_name'));
        $track = trim((string) $request->input('track'));

        // Section names are unique across ALL grade levels (7-12), so a name
        // already used anywhere cannot be assigned again. Exclude the current
        // teacher so they can keep their own section unchanged.
        if (self::sectionExists($section, $teacherUserId)) {
            return $this->editModalError($request, [
                'section_name' => 'This section name is already taken by another class.',
            ], $teacherUserId);
        }

        $advisoryClass = "Grade {$grade}-{$section}";
        if ($grade >= 11 && $track !== '') {
            $advisoryClass .= " ({$track})";
        }

        DB::table('teachers')->where('user_id', $teacherUserId)->update([
            'advisory_class' => $advisoryClass,
        ]);

        app(NotificationService::class)->advisoryChanged($teacherUserId, $advisoryClass);

        flash_notice("Advisory class {$advisoryClass} updated successfully!", 'success');

        // For AJAX, hand back the redirect target as JSON so the fetch does
        // not consume the flashed success message before the browser reloads.
        if ($request->ajax()) {
            return response()->json(['ok' => true, 'redirect' => route('office.teacher-advisory')]);
        }

        return redirect()->route('office.teacher-advisory');
    }

    public function storeAdvisory(Request $request): RedirectResponse
    {
        $request->validate([
            'teacher_user_id' => 'required|integer|exists:users,id',
            'grade_level' => 'required|integer|in:7,8,9,10,11,12',
            'section_name' => 'required|string|max:255',
            'track' => 'nullable|string|in:Academic,TVL',
        ]);

        $teacherUserId = $request->integer('teacher_user_id');
        $grade = $request->integer('grade_level');
        $section = trim($request->string('section_name'));
        $track = trim($request->string('track'));

        // Ensure teacher doesn't already have an advisory class (CREATE only)
        $existingAdvisory = DB::table('teachers')
            ->where('user_id', $teacherUserId)
            ->whereNotNull('advisory_class')
            ->exists();

        if ($existingAdvisory) {
            return back()->withErrors([
                'teacher_user_id' => 'This teacher already has an advisory class assigned.',
            ])->withInput();
        }

        // Section names are unique across ALL grade levels (7-12).
        if (self::sectionExists($section)) {
            return back()->withErrors([
                'section_name' => 'This section name is already taken by another class.',
            ])->withInput();
        }

        $advisoryClass = "Grade {$grade}-{$section}";
        if ($grade >= 11 && $track !== '') {
            $advisoryClass .= " ({$track})";
        }

        DB::table('teachers')->where('user_id', $teacherUserId)->update([
            'advisory_class' => $advisoryClass,
        ]);

        app(NotificationService::class)->advisoryAssigned($teacherUserId, $advisoryClass);

        flash_notice("Advisory class {$advisoryClass} assigned successfully!", 'success');

        return redirect()->route('office.teacher-advisory');
    }
}
