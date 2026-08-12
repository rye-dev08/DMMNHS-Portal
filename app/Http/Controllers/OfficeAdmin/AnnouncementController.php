<?php

namespace App\Http\Controllers\OfficeAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Setting;
use App\Services\AnnouncementService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $settings = Setting::find(1);
        $currentYear = (string) ($settings->current_school_year ?? date('Y').'-'.(date('Y') + 1));

        $status = (string) $request->input('status', '');
        $audience = (string) $request->input('audience', '');
        $schoolYear = (string) $request->input('school_year', '');
        $term = (string) $request->input('term', '');
        $dateFrom = (string) $request->input('date_from', '');
        $dateTo = (string) $request->input('date_to', '');
        $q = (string) $request->input('q', '');

        $query = Announcement::with('audiences');
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($audience !== '') {
            $query->where('target_role', $audience);
        }
        if ($schoolYear !== '') {
            $query->where('school_year', $schoolYear);
        }
        if ($term !== '') {
            $query->where('term', (int) $term);
        }
        if ($dateFrom !== '') {
            $query->whereDate('publish_date', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('publish_date', '<=', $dateTo);
        }
        if ($q !== '') {
            $query->where('title', 'like', '%'.$q.'%');
        }

        $announcements = $query->orderByDesc('publish_date')->orderByDesc('id')->get();

        $service = app(AnnouncementService::class);
        $announcementsData = $announcements->map(function (Announcement $announcement) use ($service) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'summary' => $announcement->short_summary,
                'content' => $announcement->content,
                'priority' => $announcement->priority,
                'priority_badge' => announcement_priority_style((string) $announcement->priority, 'badge'),
                'priority_accent' => announcement_priority_style((string) $announcement->priority, 'accent'),
                'publish_date' => $announcement->publish_date ? $announcement->publish_date->format('M d, Y') : '',
                'expiration_date' => $announcement->expiration_date ? $announcement->expiration_date->format('M d, Y') : '',
                'attachment_url' => $announcement->attachment ? asset('storage/'.$announcement->attachment) : '',
                'attachment_name' => $announcement->attachment_name,
                'target_label' => $service->audienceLabel($announcement),
            ];
        })->values();

        return view('office.announcements', [
            'announcements' => $announcements,
            'announcementsData' => $announcementsData,
            'status' => $status,
            'audience' => $audience,
            'schoolYear' => $schoolYear,
            'term' => $term,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'q' => $q,
            'years' => $this->years($currentYear),
            'currentYear' => $currentYear,
            'sections' => $this->sectionsList(),
            'students' => $this->studentsList(),
            'teachers' => $this->teachersList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = Announcement::create($data);

        $this->syncAudiences($announcement, $request);

        app(NotificationService::class)->announcementCreated($announcement);

        flash_notice('Announcement created successfully!', 'success');

        return redirect()->route('office.announcements', $this->filterQuery());
    }

    public function edit(int $announcementId): View
    {
        $announcement = Announcement::with('audiences')->findOrFail($announcementId);

        $currentYear = (string) (Setting::find(1)->current_school_year ?? date('Y').'-'.(date('Y') + 1));

        return view('office.edit_announcement_modal', [
            'announcement' => $announcement,
            'modalId' => 'edit-announcement-modal',
            'years' => $this->years($currentYear),
            'sections' => $this->sectionsList(),
            'students' => $this->studentsList(),
            'teachers' => $this->teachersList(),
        ]);
    }

    public function update(Request $request, int $announcementId): RedirectResponse
    {
        $announcement = Announcement::findOrFail($announcementId);

        try {
            $data = $this->validated($request, $announcement);
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors())
                ->with('edit_announcement_id', $announcement->id);
        }

        $announcement->update($data);

        $this->syncAudiences($announcement, $request);

        app(NotificationService::class)->announcementUpdated($announcement);

        flash_notice('Announcement updated successfully!', 'success');

        return redirect()->route('office.announcements', $this->filterQuery());
    }

    public function toggleStatus(int $announcementId): RedirectResponse
    {
        $announcement = Announcement::findOrFail($announcementId);

        $wasPublished = $announcement->isPublished();

        $announcement->update([
            'status' => $wasPublished
                ? Announcement::STATUS_UNPUBLISHED
                : Announcement::STATUS_PUBLISHED,
        ]);

        if (! $wasPublished && $announcement->isPublished()) {
            $announcement->load('audiences');
            app(NotificationService::class)->announcementCreated($announcement);
        }

        flash_notice($announcement->isPublished()
            ? "Announcement '{$announcement->title}' published."
            : "Announcement '{$announcement->title}' unpublished.", 'success');

        return redirect()->route('office.announcements', $this->filterQuery());
    }

    public function destroy(int $announcementId): RedirectResponse
    {
        $announcement = Announcement::findOrFail($announcementId);

        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        DB::table('announcement_reads')->where('announcement_id', $announcement->id)->delete();
        $announcement->audiences()->delete();
        $announcement->delete();

        flash_notice('Announcement deleted.', 'success');

        return redirect()->route('office.announcements', $this->filterQuery());
    }

    private function validated(Request $request, ?Announcement $existing = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'short_summary' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'],
            'priority' => ['required', 'string', 'in:normal,important,urgent'],
            'status' => ['required', 'string', 'in:published,unpublished'],
            'target_role' => ['required', 'string', 'in:all,students,teachers,admins'],
            'publish_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'grade_levels' => ['nullable', 'array'],
            'grade_levels.*' => ['integer', 'between:7,12'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', 'max:255'],
            'students' => ['nullable', 'array'],
            'students.*' => ['integer', 'exists:students,id'],
            'teachers' => ['nullable', 'array'],
            'teachers.*' => ['integer', 'exists:teachers,id'],
            'remove_attachment' => ['sometimes', 'boolean'],
        ]);

        $attachment = $existing->attachment ?? null;
        $attachmentName = $existing->attachment_name ?? null;

        if ($request->hasFile('attachment')) {
            try {
                if ($attachment) {
                    Storage::disk('public')->delete($attachment);
                }
                $path = $request->file('attachment')->store('announcements', 'public');
            } catch (\Throwable $e) {
                report($e);
                flash_notice('Unable to upload the attachment. Please check the file and try again.', 'error');

                throw ValidationException::withMessages(['attachment' => 'The attachment could not be uploaded.']);
            }
            $attachment = $path;
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        } elseif ($request->boolean('remove_attachment') && $attachment) {
            Storage::disk('public')->delete($attachment);
            $attachment = null;
            $attachmentName = null;
        }

        return [
            'title' => trim($data['title']),
            'short_summary' => trim($data['short_summary'] ?? '') ?: null,
            'content' => trim($data['content'] ?? '') ?: null,
            'attachment' => $attachment,
            'attachment_name' => $attachmentName,
            'priority' => $data['priority'],
            'status' => $data['status'],
            'target_role' => $data['target_role'],
            'publish_date' => $data['publish_date'],
            'expiration_date' => $data['expiration_date'] ?: null,
            'school_year' => trim($data['school_year']),
            'term' => (int) $data['term'],
            'created_by' => auth()->id(),
        ];
    }

    private function syncAudiences(Announcement $announcement, Request $request): void
    {
        $rows = [];
        $role = (string) $request->input('target_role', '');

        if ($role === 'students') {
            foreach ((array) $request->input('grade_levels', []) as $grade) {
                $rows[] = ['target_type' => 'grade_level', 'target_value' => (string) (int) $grade];
            }
            foreach ((array) $request->input('sections', []) as $section) {
                $rows[] = ['target_type' => 'section', 'target_value' => trim((string) $section)];
            }
            foreach ((array) $request->input('students', []) as $studentId) {
                $rows[] = ['target_type' => 'student', 'target_value' => (string) (int) $studentId];
            }
        } elseif ($role === 'teachers') {
            foreach ((array) $request->input('teachers', []) as $teacherId) {
                $rows[] = ['target_type' => 'teacher', 'target_value' => (string) (int) $teacherId];
            }
        }

        $announcement->audiences()->delete();

        foreach ($rows as $row) {
            $announcement->audiences()->create($row);
        }
    }

    private function years(string $currentYear): array
    {
        return Announcement::query()
            ->distinct()
            ->pluck('school_year')
            ->map(fn ($year) => (string) $year)
            ->merge([$currentYear])
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function sectionsList(): array
    {
        return DB::table('teachers')
            ->whereNotNull('advisory_class')
            ->where('advisory_class', '!=', '')
            ->distinct()
            ->orderBy('advisory_class')
            ->pluck('advisory_class')
            ->all();
    }

    private function studentsList(): Collection
    {
        return DB::table('students as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->where('s.status', 'active')
            ->where('u.status', 'active')
            ->where('u.role', 'student')
            ->orderBy('u.name')
            ->select('s.id as student_id', 'u.name as student_name', 's.grade_level')
            ->get();
    }

    private function teachersList(): Collection
    {
        return DB::table('teachers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where('t.status', 'active')
            ->where('u.status', 'active')
            ->where('u.role', 'teacher')
            ->orderBy('u.name')
            ->select('t.id as teacher_id', 'u.name as teacher_name')
            ->get();
    }

    private function filterQuery(): array
    {
        return collect(request()->query())
            ->only(['status', 'audience', 'school_year', 'term', 'date_from', 'date_to', 'q'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }
}
