<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Requirement;
use App\Models\RequirementSubmission;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\NotificationService;
use App\Services\RequirementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequirementController extends Controller
{
    private function teacher(): ?Teacher
    {
        return Teacher::where('user_id', auth()->id())->first();
    }

    private function teacherId(): int
    {
        return (int) ($this->teacher()->id ?? 0);
    }

    private function owned(Requirement $requirement): Requirement
    {
        abort_unless((int) $requirement->teacher_id === $this->teacherId(), 403);

        return $requirement;
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'requirement_type' => ['required', Rule::in(array_keys(Requirement::TYPES))],
            'description' => ['required', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'submission_required' => ['sometimes', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ];
    }

    public function index(): View
    {
        $teacherId = $this->teacherId();
        $period = Setting::find(1)->period();
        $service = app(RequirementService::class);

        $requirements = Requirement::where('teacher_id', $teacherId)
            ->where('school_year', $period->school_year)
            ->where('term', $period->term)
            ->with('submissions')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $requirements->getCollection()->transform(function (Requirement $requirement) use ($service) {
            $requirement->progress = $service->progress($requirement);
            $requirement->can_bump = $service->canBump($requirement);
            $requirement->bump_available_at = $service->bumpCooldownRemaining($requirement);

            return $requirement;
        });

        return view('teacher.requirements_index', [
            'requirements' => $requirements,
            'period' => $period,
            'teacherId' => $teacherId,
            'teacherName' => auth()->user()->name ?? '',
        ]);
    }

    public function create(): View
    {
        $teacher = $this->teacher();
        $service = app(RequirementService::class);

        return view('teacher.requirements_create', [
            'teacher' => $teacher,
            'studentCount' => $service->assignedStudents((int) ($teacher->id ?? 0))->count(),
            'types' => Requirement::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->teacher();
        $teacherId = (int) ($teacher->id ?? 0);

        if ($teacherId === 0) {
            flash_notice('Teacher profile not found.', 'error');

            return redirect()->route('teacher.requirements.index');
        }

        $period = Setting::find(1)->period();
        $validated = $request->validate($this->rules());

        $attachmentPath = null;
        $attachmentName = null;

        try {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentPath = $file->store('requirement-files', 'public');
                $attachmentName = $file->getClientOriginalName();
            }
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to upload the attachment. Please check the file and try again.', 'error');

            return back()->withInput();
        }

        try {
            $requirement = Requirement::create([
                'teacher_id' => $teacherId,
                'title' => $validated['title'],
                'requirement_type' => $validated['requirement_type'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'] ?? null,
                'submission_required' => $request->boolean('submission_required'),
                'attachment' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'section' => $teacher->advisory_class,
                'school_year' => $period->school_year,
                'term' => $period->term,
                'status' => Requirement::STATUS_ACTIVE,
            ]);
        } catch (\Throwable $e) {
            report($e);

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            flash_notice('Unable to create the requirement. Please try again.', 'error');

            return back()->withInput();
        }

        $service = app(RequirementService::class);
        $studentIds = $service->assignedStudentIds($teacherId);
        $link = route('student.requirements.show', $requirement->id);

        foreach ($studentIds as $studentId) {
            app(NotificationService::class)->requirementAssigned($studentId, $requirement->title, $link);
        }

        flash_notice("Requirement \"{$requirement->title}\" sent to ".count($studentIds).' student(s).', 'success');

        return redirect()->route('teacher.requirements.show', $requirement->id);
    }

    public function show(Requirement $requirement): View
    {
        $this->owned($requirement);

        $service = app(RequirementService::class);
        $students = $service->assignedStudents((int) $requirement->teacher_id);
        $submissions = $requirement->submissions()->get()->keyBy('student_id');

        $rows = $students->map(function ($student) use ($submissions) {
            $submission = $submissions->get((int) $student->id);

            return (object) [
                'student_id' => (int) $student->id,
                'student_name' => $student->name,
                'submission' => $submission,
                'status' => $submission ? $submission->status : RequirementSubmission::STATUS_PENDING,
            ];
        });

        return view('teacher.requirements_show', [
            'requirement' => $requirement,
            'rows' => $rows,
            'progress' => $service->progress($requirement),
            'canBump' => $service->canBump($requirement),
            'bumpAvailableAt' => $service->bumpCooldownRemaining($requirement),
        ]);
    }

    public function edit(Requirement $requirement): View
    {
        $this->owned($requirement);

        return view('teacher.requirements_edit', [
            'requirement' => $requirement,
            'types' => Requirement::TYPES,
            'studentCount' => app(RequirementService::class)->assignedStudents((int) $requirement->teacher_id)->count(),
        ]);
    }

    public function update(Request $request, Requirement $requirement): RedirectResponse
    {
        $this->owned($requirement);
        $validated = $request->validate($this->rules());

        $attachmentPath = $requirement->attachment;
        $attachmentName = $requirement->attachment_name;

        try {
            if ($request->hasFile('attachment')) {
                if ($attachmentPath) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $file = $request->file('attachment');
                $attachmentPath = $file->store('requirement-files', 'public');
                $attachmentName = $file->getClientOriginalName();
            } elseif ($request->boolean('remove_attachment')) {
                if ($attachmentPath) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = null;
                $attachmentName = null;
            }
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to process the attachment. Please try again.', 'error');

            return back()->withInput();
        }

        try {
            $requirement->update([
                'title' => $validated['title'],
                'requirement_type' => $validated['requirement_type'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'] ?? null,
                'submission_required' => $request->boolean('submission_required'),
                'attachment' => $attachmentPath,
                'attachment_name' => $attachmentName,
            ]);
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to update the requirement. Please try again.', 'error');

            return back()->withInput();
        }

        flash_notice('Requirement updated.', 'success');

        return redirect()->route('teacher.requirements.show', $requirement->id);
    }

    public function bump(Request $request, Requirement $requirement): RedirectResponse
    {
        $this->owned($requirement);
        $service = app(RequirementService::class);

        if (! $service->canBump($requirement)) {
            flash_notice('Please wait for the bump cooldown (once every 24 hours).', 'error');

            return back();
        }

        $count = $service->bumpAll($requirement, $this->teacherId(), app(NotificationService::class));

        flash_notice("Bump reminder sent to {$count} pending student(s).", 'success');

        return back();
    }

    public function remindStudent(Request $request, Requirement $requirement, Student $student): RedirectResponse
    {
        $this->owned($requirement);
        $service = app(RequirementService::class);

        if (! $service->canBump($requirement)) {
            flash_notice('Please wait for the bump cooldown (once every 24 hours).', 'error');

            return back();
        }

        $sent = $service->bumpStudent($requirement, (int) $student->id, $this->teacherId(), app(NotificationService::class));

        flash_notice(
            $sent ? 'Reminder sent to the student.' : 'Reminder not sent. The student may have already submitted.',
            $sent ? 'success' : 'info'
        );

        return back();
    }

    public function download(Requirement $requirement): StreamedResponse
    {
        $this->owned($requirement);
        abort_if(! $requirement->attachment, 404);

        return Storage::disk('public')->download($requirement->attachment, $requirement->attachment_name);
    }

    public function destroy(Request $request, Requirement $requirement): RedirectResponse
    {
        $this->owned($requirement);

        try {
            if ($requirement->attachment) {
                Storage::disk('public')->delete($requirement->attachment);
            }

            foreach ($requirement->submissions()->get() as $submission) {
                if ($submission->attachment) {
                    Storage::disk('public')->delete($submission->attachment);
                }
            }

            $requirement->submissions()->delete();
            $requirement->delete();
        } catch (\Throwable $e) {
            report($e);
            flash_notice('Unable to delete the requirement. Please try again.', 'error');

            return redirect()->route('teacher.requirements.index');
        }

        flash_notice('Requirement deleted.', 'success');

        return redirect()->route('teacher.requirements.index');
    }
}
