<?php

use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ImportantDatesController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeAdmin\AcademicCalendarController as OfficeAcademicCalendarController;
use App\Http\Controllers\OfficeAdmin\AnnouncementController as OfficeAnnouncementController;
use App\Http\Controllers\OfficeAdmin\DashboardController as OfficeDashboardController;
use App\Http\Controllers\OfficeAdmin\GradeSubmissionMonitorController as OfficeGradeSubmissionMonitorController;
use App\Http\Controllers\OfficeAdmin\MessageCenterController as OfficeMessageCenterController;
use App\Http\Controllers\OfficeAdmin\RequirementController as OfficeRequirementController;
use App\Http\Controllers\OfficeAdmin\TeacherAssignmentController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\EnrollmentController as StudentEnrollmentController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\RequirementController as StudentRequirementController;
use App\Http\Controllers\Student\ScheduleController;
use App\Http\Controllers\Student\StudentInfoController;
use App\Http\Controllers\Student\TimelineController as StudentTimelineController;
use App\Http\Controllers\SystemAdmin\AccountController;
use App\Http\Controllers\SystemAdmin\DashboardController as SystemAdminDashboardController;
use App\Http\Controllers\SystemAdmin\EnrollmentSettingController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\EnrollmentRequestController as TeacherEnrollmentRequestController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\GradesOverviewController;
use App\Http\Controllers\Teacher\GradeSubmissionController as TeacherGradeSubmissionController;
use App\Http\Controllers\Teacher\InfoController;
use App\Http\Controllers\Teacher\RequirementController as TeacherRequirementController;
use App\Http\Controllers\Teacher\SubjectController;
use App\Http\Controllers\Teacher\SubmissionController as TeacherSubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit')->middleware('throttle:contact');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Change password (shared by all roles).
    Route::get('/change-password', [PasswordController::class, 'index'])->name('password.change');
    Route::post('/change-password', [PasswordController::class, 'update'])->name('password.update');

    // Notifications (shared by all roles).
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Polling endpoints (shared by all roles).
    Route::get('/poll/notifications', [PollController::class, 'notifications'])->name('poll.notifications');
    Route::get('/poll/announcements', [PollController::class, 'announcements'])->name('poll.announcements');
    Route::get('/poll/messages', [PollController::class, 'messages'])->name('poll.messages');
    Route::get('/poll/dashboard', [PollController::class, 'dashboard'])->name('poll.dashboard');
    Route::get('/poll/grade-submissions', [PollController::class, 'gradeSubmissions'])->name('poll.grade-submissions');
    Route::get('/poll/enrollment-requests', [PollController::class, 'enrollmentRequests'])->name('poll.enrollment-requests');

    // Announcements (shared by all roles).
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');
    Route::post('/announcements/mark-read', [AnnouncementController::class, 'markRead'])->name('announcements.mark-read');

    Route::prefix('admin')->name('admin.')->middleware('role:system_admin')->group(function () {
        Route::get('/dashboard', [SystemAdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{user}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('accounts.update');
        Route::post('/accounts/{user}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggle-status');
        Route::post('/accounts/{user}/reset-password', [AccountController::class, 'resetPassword'])->name('accounts.reset-password');
        Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
        Route::get('/enrollment-settings', [EnrollmentSettingController::class, 'index'])->name('enrollment-settings');
        Route::post('/enrollment-settings/end-term', [EnrollmentSettingController::class, 'endTerm'])->name('enrollment-settings.end-term');
        Route::post('/enrollment-settings/end-school-year', [EnrollmentSettingController::class, 'endSchoolYear'])->name('enrollment-settings.end-school-year');
        Route::post('/enrollment-settings/end-enrollment-phase', [EnrollmentSettingController::class, 'endEnrollmentPhase'])->name('enrollment-settings.end-enrollment-phase');
        Route::post('/enrollment-settings/new-school-year', [EnrollmentSettingController::class, 'newSchoolYear'])->name('enrollment-settings.new-school-year');
    });

    Route::prefix('office')->name('office.')->middleware('role:office_admin')->group(function () {
        Route::get('/dashboard', [OfficeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/teacher-advisory', [TeacherAssignmentController::class, 'advisory'])->name('teacher-advisory');
        Route::get('/assign-class', [TeacherAssignmentController::class, 'assignClass'])->name('assign-class');
        Route::post('/assign-class', [TeacherAssignmentController::class, 'storeAdvisory'])->name('assign-class.store');
        Route::get('/advisory/{teacher}/edit', [TeacherAssignmentController::class, 'editAdvisory'])->name('advisory.edit');
        Route::put('/advisory/{teacher}', [TeacherAssignmentController::class, 'updateAdvisory'])->name('advisory.update');
        Route::get('/academic-calendar', [OfficeAcademicCalendarController::class, 'index'])->name('academic-calendar');
        Route::post('/academic-calendar', [OfficeAcademicCalendarController::class, 'store'])->name('academic-calendar.store');
        Route::get('/academic-calendar/{event}/edit', [OfficeAcademicCalendarController::class, 'edit'])->name('academic-calendar.edit');
        Route::put('/academic-calendar/{event}', [OfficeAcademicCalendarController::class, 'update'])->name('academic-calendar.update');
        Route::delete('/academic-calendar/{event}', [OfficeAcademicCalendarController::class, 'destroy'])->name('academic-calendar.destroy');
        Route::get('/announcements', [OfficeAnnouncementController::class, 'index'])->name('announcements');
        Route::post('/announcements', [OfficeAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [OfficeAnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [OfficeAnnouncementController::class, 'update'])->name('announcements.update');
        Route::post('/announcements/{announcement}/toggle-status', [OfficeAnnouncementController::class, 'toggleStatus'])->name('announcements.toggle-status');
        Route::delete('/announcements/{announcement}', [OfficeAnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::get('/message-center', [OfficeMessageCenterController::class, 'index'])->name('message-center');
        Route::get('/message-center/blocked-senders', [OfficeMessageCenterController::class, 'blockedSenders'])->name('message-center.blocked');
        Route::post('/messages/{message}/valid', [OfficeMessageCenterController::class, 'markValid'])->name('messages.valid');
        Route::post('/messages/{message}/invalid', [OfficeMessageCenterController::class, 'markInvalid'])->name('messages.invalid');
        Route::post('/messages/{message}/block', [OfficeMessageCenterController::class, 'blockSender'])->name('messages.block');
        Route::delete('/messages/{message}', [OfficeMessageCenterController::class, 'destroy'])->name('messages.destroy');
        Route::post('/message-sender-blocks/{block}/unblock', [OfficeMessageCenterController::class, 'unblock'])->name('message-sender-blocks.unblock');
        Route::get('/requirements', [OfficeRequirementController::class, 'index'])->name('requirements');
        Route::get('/requirements/{requirement}', [OfficeRequirementController::class, 'show'])->name('requirements.show');
        Route::get('/requirements/{requirement}/download', [OfficeRequirementController::class, 'download'])->name('requirements.download');
        Route::get('/important-dates', [ImportantDatesController::class, 'index'])->name('important-dates');
        Route::get('/grade-submissions', [OfficeGradeSubmissionMonitorController::class, 'index'])->name('grade-submissions');
        Route::post('/grade-submissions/deadlines', [OfficeGradeSubmissionMonitorController::class, 'storeDeadline'])->name('grade-submissions.deadlines.store');
        Route::put('/grade-submissions/deadlines/{deadline}', [OfficeGradeSubmissionMonitorController::class, 'updateDeadline'])->name('grade-submissions.deadlines.update');
        Route::delete('/grade-submissions/deadlines/{deadline}', [OfficeGradeSubmissionMonitorController::class, 'destroyDeadline'])->name('grade-submissions.deadlines.destroy');
        Route::post('/grade-submissions/remind', [OfficeGradeSubmissionMonitorController::class, 'remind'])->name('grade-submissions.remind');
        Route::post('/grade-submissions/remind-all', [OfficeGradeSubmissionMonitorController::class, 'remindAll'])->name('grade-submissions.remind-all');
    });

    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::get('/advisory-portal', [SubjectController::class, 'index'])->name('advisory-portal');
        Route::post('/advisory-portal', [SubjectController::class, 'store'])->name('advisory-portal.store');
        Route::delete('/advisory-portal/{teacherSubject}', [SubjectController::class, 'destroy'])->name('advisory-portal.destroy');
        Route::get('/enrollment-requests', [TeacherEnrollmentRequestController::class, 'index'])->name('enrollment-requests');
        Route::post('/enrollment-requests/approve', [TeacherEnrollmentRequestController::class, 'approve'])->name('enrollment-requests.approve');
        Route::post('/enrollment-requests/reject', [TeacherEnrollmentRequestController::class, 'reject'])->name('enrollment-requests.reject');
        Route::get('/submit-grades', [TeacherGradeController::class, 'index'])->name('submit-grades');
        Route::post('/submit-grades', [TeacherGradeController::class, 'store'])->name('submit-grades.store');
        Route::get('/subjects', [TeacherGradeController::class, 'getSubjects'])->name('subjects.json');
        Route::get('/grades-overview', [GradesOverviewController::class, 'index'])->name('grades-overview');
        Route::get('/grade-submissions', [TeacherGradeSubmissionController::class, 'index'])->name('grade-submissions');
        Route::get('/info', [InfoController::class, 'index'])->name('info');
        Route::get('/info/edit', [InfoController::class, 'edit'])->name('info.edit');
        Route::put('/info', [InfoController::class, 'update'])->name('info.update');
        Route::get('/academic-calendar', [AcademicCalendarController::class, 'index'])->name('calendar');
        Route::get('/requirements', [TeacherRequirementController::class, 'index'])->name('requirements');
        Route::get('/requirements/create', [TeacherRequirementController::class, 'create'])->name('requirements.create');
        Route::post('/requirements', [TeacherRequirementController::class, 'store'])->name('requirements.store');
        Route::get('/requirements/{requirement}', [TeacherRequirementController::class, 'show'])->name('requirements.show');
        Route::post('/requirements/{requirement}/bump', [TeacherRequirementController::class, 'bump'])->name('requirements.bump');
        Route::get('/requirements/{requirement}/edit', [TeacherRequirementController::class, 'edit'])->name('requirements.edit');
        Route::put('/requirements/{requirement}', [TeacherRequirementController::class, 'update'])->name('requirements.update');
        Route::delete('/requirements/{requirement}', [TeacherRequirementController::class, 'destroy'])->name('requirements.destroy');
        Route::get('/requirements/{requirement}/download', [TeacherRequirementController::class, 'download'])->name('requirements.download');
        Route::post('/requirements/{requirement}/remind/{student}', [TeacherRequirementController::class, 'remindStudent'])->name('requirements.remind');
        Route::post('/submissions/{submission}/approve', [TeacherSubmissionController::class, 'approve'])->name('submissions.approve');
        Route::post('/submissions/{submission}/revision', [TeacherSubmissionController::class, 'revision'])->name('submissions.revision');
        Route::get('/submissions/{submission}/download', [TeacherSubmissionController::class, 'download'])->name('submissions.download');
        Route::get('/important-dates', [ImportantDatesController::class, 'index'])->name('important-dates');
    });

    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/info', [StudentInfoController::class, 'index'])->name('info');
        Route::get('/info/edit', [StudentInfoController::class, 'edit'])->name('info.edit');
        Route::put('/info', [StudentInfoController::class, 'update'])->name('info.update');
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
        Route::get('/grades', [StudentGradeController::class, 'index'])->name('grades');
        Route::get('/enrollment', [StudentEnrollmentController::class, 'index'])->name('enrollment');
        Route::post('/enrollment', [StudentEnrollmentController::class, 'store'])->name('enrollment.store');
        Route::get('/academic-calendar', [AcademicCalendarController::class, 'index'])->name('calendar');
        Route::get('/requirements', [StudentRequirementController::class, 'index'])->name('requirements');
        Route::get('/requirements/{requirement}', [StudentRequirementController::class, 'show'])->name('requirements.show');
        Route::post('/requirements/{requirement}/submit', [StudentRequirementController::class, 'submit'])->name('requirements.submit');
        Route::get('/requirements/{requirement}/download', [StudentRequirementController::class, 'download'])->name('requirements.download');
        Route::get('/requirements/{requirement}/submission/download', [StudentRequirementController::class, 'downloadSubmission'])->name('requirements.submission-download');
        Route::get('/important-dates', [ImportantDatesController::class, 'index'])->name('important-dates');
        Route::get('/timeline', [StudentTimelineController::class, 'index'])->name('timeline');
    });
});
