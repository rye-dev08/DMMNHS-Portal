<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnrollmentSettingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\EnrollmentController as StudentEnrollmentController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\ScheduleController;
use App\Http\Controllers\Student\StudentInfoController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\EnrollmentRequestController as TeacherEnrollmentRequestController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\GradesOverviewController;
use App\Http\Controllers\Teacher\InfoController;
use App\Http\Controllers\Teacher\SubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
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

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts');
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{user}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('accounts.update');
        Route::post('/accounts/{user}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggle-status');
        Route::post('/accounts/{user}/reset-password', [AccountController::class, 'resetPassword'])->name('accounts.reset-password');
        Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
        Route::get('/enrollment-settings', [EnrollmentSettingController::class, 'index'])->name('enrollment-settings');
        Route::post('/enrollment-settings/advisory', [EnrollmentSettingController::class, 'saveAdvisory'])->name('enrollment-settings.advisory');
        Route::post('/enrollment-settings/end-term', [EnrollmentSettingController::class, 'endTerm'])->name('enrollment-settings.end-term');
        Route::post('/enrollment-settings/end-school-year', [EnrollmentSettingController::class, 'endSchoolYear'])->name('enrollment-settings.end-school-year');
        Route::post('/enrollment-settings/end-enrollment-phase', [EnrollmentSettingController::class, 'endEnrollmentPhase'])->name('enrollment-settings.end-enrollment-phase');
        Route::post('/enrollment-settings/new-school-year', [EnrollmentSettingController::class, 'newSchoolYear'])->name('enrollment-settings.new-school-year');
        Route::get('/teacher-advisory', [EnrollmentSettingController::class, 'advisory'])->name('teacher-advisory');
        Route::get('/assign-class', [EnrollmentSettingController::class, 'assignClass'])->name('assign-class');
        Route::post('/assign-class', [EnrollmentSettingController::class, 'storeAdvisory'])->name('assign-class.store');
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
        Route::get('/info', [InfoController::class, 'index'])->name('info');
        Route::get('/info/edit', [InfoController::class, 'edit'])->name('info.edit');
        Route::put('/info', [InfoController::class, 'update'])->name('info.update');
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
    });
});
