<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BiometricEnrollmentController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\LecturerController;
use App\Http\Controllers\Admin\ManualUnlockController;
use App\Http\Controllers\Admin\MeetingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => redirect()->route('dashboard'))->name('index');
        Route::get('lecturers/import', [LecturerController::class, 'import'])->name('lecturers.import');
        Route::post('lecturers/import', [LecturerController::class, 'importStore'])->name('lecturers.import.store');
        Route::get('lecturers/import/template', [LecturerController::class, 'importTemplate'])->name('lecturers.import.template');
        Route::resource('lecturers', LecturerController::class);
        Route::get('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::post('students/import', [StudentController::class, 'importStore'])->name('students.import.store');
        Route::get('students/import/template', [StudentController::class, 'importTemplate'])->name('students.import.template');
        Route::resource('students', StudentController::class);
        Route::resource('departments', DepartmentController::class);
        Route::resource('classes', ClassController::class);
        Route::resource('subjects', SubjectController::class);
        Route::resource('rooms', RoomController::class);
        Route::resource('academic-years', AcademicYearController::class);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('devices', DeviceController::class);
        Route::post('biometric-enrollments/sync-all', [BiometricEnrollmentController::class, 'requestSyncAll'])->name('biometric-enrollments.sync-all');
        Route::post('biometric-enrollments/{biometric_enrollment}/sync', [BiometricEnrollmentController::class, 'requestSync'])->name('biometric-enrollments.sync');
        Route::resource('biometric-enrollments', BiometricEnrollmentController::class);
        Route::get('meetings', [MeetingController::class, 'index'])->name('meetings.index');
        Route::get('meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
        Route::get('manual-unlock', [ManualUnlockController::class, 'index'])->name('manual-unlock.index');
        Route::post('manual-unlock', [ManualUnlockController::class, 'store'])->name('manual-unlock.store');
        Route::get('reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
        Route::get('reports/attendance/download', [ReportController::class, 'downloadAttendanceReport'])->name('reports.attendance.download');
        Route::get('reports/classes', fn () => redirect()->route('admin.reports.attendance', array_merge(request()->query(), ['report_type' => 'class'])))->name('reports.classes');
        Route::get('reports/classes/download', [ReportController::class, 'downloadClassReport'])->name('reports.classes.download');
        Route::get('reports/subjects', fn () => redirect()->route('admin.reports.attendance', array_merge(request()->query(), ['report_type' => 'subject'])))->name('reports.subjects');
        Route::get('reports/subjects/download', [ReportController::class, 'downloadSubjectReport'])->name('reports.subjects.download');
        Route::get('reports/lecturers', fn () => redirect()->route('admin.reports.attendance', array_merge(request()->query(), ['report_type' => 'lecturer'])))->name('reports.lecturers');
        Route::get('reports/lecturers/download', [ReportController::class, 'downloadLecturerReport'])->name('reports.lecturers.download');
        Route::get('reports/denied-access', [ReportController::class, 'deniedAccessReport'])->name('reports.denied-access');
        Route::get('reports/denied-access/download', [ReportController::class, 'downloadDeniedAccessReport'])->name('reports.denied-access.download');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';







