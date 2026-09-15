<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParentPortal\DashboardController as ParentDashboardController;
use App\Http\Controllers\ParentPortal\FeesController;
use App\Http\Controllers\ParentPortal\FirstLoginController;
use App\Http\Controllers\ParentPortal\NoticesController;
use App\Http\Controllers\ParentPortal\ResultsController;
use App\Http\Controllers\Principal\AssignmentController;
use App\Http\Controllers\Principal\DashboardController as PrincipalDashboardController;
use App\Http\Controllers\Principal\ExamController;
use App\Http\Controllers\Principal\FeeController as PrincipalFeeController;
use App\Http\Controllers\Principal\NoticeController as PrincipalNoticeController;
use App\Http\Controllers\Principal\PromotionController;
use App\Http\Controllers\Principal\SchoolClassController;
use App\Http\Controllers\Principal\SubjectController;
use App\Http\Controllers\Principal\TeacherController;
use App\Http\Controllers\Principal\TermController;
use App\Http\Controllers\Principal\TimetableController as PrincipalTimetableController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\PrincipalController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\Teacher\ClassExamController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ElectiveEnrollmentController;
use App\Http\Controllers\Teacher\MarksController;
use App\Http\Controllers\Teacher\NoticeController as TeacherNoticeController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\TimetableController as TeacherTimetableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Auth routes (staff login: super_admin, principal, teacher, and parents
| once they've completed the guided first-login in Phase 5)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');
});

// Parent guided first-login flow (also reused when an existing parent links another child).
// Deliberately outside 'guest' middleware - FirstLoginController::guardAccess() handles
// authorization itself, since a logged-in parent needs to reach this too.
Route::prefix('parent/first-login')
    ->name('parent.first-login.')
    ->group(function () {
        Route::get('/', [FirstLoginController::class, 'chooseSchool'])->name('school');
        Route::post('/', [FirstLoginController::class, 'storeSchool']);
        Route::get('/class', [FirstLoginController::class, 'chooseClass'])->name('class');
        Route::post('/class', [FirstLoginController::class, 'storeClass']);
        Route::get('/verify', [FirstLoginController::class, 'verify'])->name('verify');
        Route::post('/verify', [FirstLoginController::class, 'storeVerify']);
        Route::get('/account', [FirstLoginController::class, 'account'])->name('account');
        Route::post('/account', [FirstLoginController::class, 'storeAccount'])->name('account.store');
    });

/*
|--------------------------------------------------------------------------
| Super Admin routes (Phase 2)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('schools', SchoolController::class)->except(['destroy']);
        Route::patch('schools/{school}/toggle-status', [SchoolController::class, 'toggleStatus'])->name('schools.toggle-status');

        Route::post('schools/{school}/principals', [PrincipalController::class, 'store'])->name('schools.principals.store');
        Route::patch('schools/{school}/principals/{principal}/toggle-status', [PrincipalController::class, 'toggleStatus'])->name('schools.principals.toggle-status');
        Route::patch('schools/{school}/principals/{principal}/reset-password', [PrincipalController::class, 'resetPassword'])->name('schools.principals.reset-password');
    });

/*
|--------------------------------------------------------------------------
| Principal routes (Phase 3)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:principal'])
    ->prefix('principal')
    ->name('principal.')
    ->group(function () {
        Route::get('/dashboard', [PrincipalDashboardController::class, 'index'])->name('dashboard');

        Route::get('terms', [TermController::class, 'index'])->name('terms.index');
        Route::post('terms', [TermController::class, 'store'])->name('terms.store');
        Route::patch('terms/{term}/make-current', [TermController::class, 'makeCurrent'])->name('terms.make-current');
        Route::delete('terms/{term}', [TermController::class, 'destroy'])->name('terms.destroy');

        Route::get('classes', [SchoolClassController::class, 'index'])->name('classes.index');
        Route::post('classes', [SchoolClassController::class, 'store'])->name('classes.store');
        Route::get('classes/{class}', [SchoolClassController::class, 'show'])->name('classes.show');
        Route::get('classes/{class}/edit', [SchoolClassController::class, 'edit'])->name('classes.edit');
        Route::put('classes/{class}', [SchoolClassController::class, 'update'])->name('classes.update');
        Route::delete('classes/{class}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

        Route::get('promotion/{class}', [PromotionController::class, 'show'])->name('promotion.show');
        Route::post('promotion/{class}', [PromotionController::class, 'store'])->name('promotion.store');

        Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
        Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('teachers/bulk', [TeacherController::class, 'bulkForm'])->name('teachers.bulk');
        Route::get('teachers/bulk/template', [TeacherController::class, 'bulkTemplate'])->name('teachers.bulk.template');
        Route::post('teachers/bulk', [TeacherController::class, 'bulkImport'])->name('teachers.bulk.import');
        Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
        Route::patch('teachers/{teacher}/toggle-status', [TeacherController::class, 'toggleStatus'])->name('teachers.toggle-status');
        Route::patch('teachers/{teacher}/reset-password', [TeacherController::class, 'resetPassword'])->name('teachers.reset-password');

        Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

        Route::get('timetable', [PrincipalTimetableController::class, 'index'])->name('timetable.index');
        Route::post('timetable', [PrincipalTimetableController::class, 'store'])->name('timetable.store');
        Route::patch('timetable/{class}/publish', [PrincipalTimetableController::class, 'publish'])->name('timetable.publish');
        Route::delete('timetable/{timetable}', [PrincipalTimetableController::class, 'destroy'])->name('timetable.destroy');

        Route::get('fees', [PrincipalFeeController::class, 'index'])->name('fees.index');
        Route::get('fees/{student}/ledger', [PrincipalFeeController::class, 'ledger'])->name('fees.ledger');
        Route::get('fees/charge/create', [PrincipalFeeController::class, 'createCharge'])->name('fees.charge.create');
        Route::post('fees/charge', [PrincipalFeeController::class, 'storeCharge'])->name('fees.charge.store');
        Route::get('fees/{student}/payment/create', [PrincipalFeeController::class, 'createPayment'])->name('fees.payment.create');
        Route::post('fees/{student}/payment', [PrincipalFeeController::class, 'storePayment'])->name('fees.payment.store');
        Route::get('fees/bulk', [PrincipalFeeController::class, 'bulkForm'])->name('fees.bulk');
        Route::post('fees/bulk', [PrincipalFeeController::class, 'bulkStore'])->name('fees.bulk.store');
        Route::delete('fees/transaction/{transaction}', [PrincipalFeeController::class, 'destroy'])->name('fees.destroy');

        Route::get('notices', [PrincipalNoticeController::class, 'index'])->name('notices.index');
        Route::get('notices/create', [PrincipalNoticeController::class, 'create'])->name('notices.create');
        Route::post('notices', [PrincipalNoticeController::class, 'store'])->name('notices.store');
        Route::delete('notices/{notice}', [PrincipalNoticeController::class, 'destroy'])->name('notices.destroy');

        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
        Route::patch('exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
        Route::patch('exams/{exam}/approve', [ExamController::class, 'approve'])->name('exams.approve');
        Route::patch('exams/{exam}/reject', [ExamController::class, 'reject'])->name('exams.reject');
    });

/*
|--------------------------------------------------------------------------
| Teacher routes (Phase 4)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        Route::get('students', [TeacherStudentController::class, 'index'])->name('students.index');
        Route::get('students/create', [TeacherStudentController::class, 'create'])->name('students.create');
        Route::post('students', [TeacherStudentController::class, 'store'])->name('students.store');
        Route::get('students/bulk', [TeacherStudentController::class, 'bulkForm'])->name('students.bulk');
        Route::get('students/bulk/template', [TeacherStudentController::class, 'bulkTemplate'])->name('students.bulk.template');
        Route::post('students/bulk', [TeacherStudentController::class, 'bulkImport'])->name('students.bulk.import');
        Route::get('students/{student}/edit', [TeacherStudentController::class, 'edit'])->name('students.edit');
        Route::put('students/{student}', [TeacherStudentController::class, 'update'])->name('students.update');

        Route::get('marks', [MarksController::class, 'index'])->name('marks.index');
        Route::get('marks/{assignment}/{exam}', [MarksController::class, 'show'])->name('marks.show');
        Route::get('marks/{assignment}/{exam}/template', [MarksController::class, 'template'])->name('marks.template');
        Route::post('marks/{assignment}/{exam}', [MarksController::class, 'store'])->name('marks.store');
        Route::get('marks/{assignment}/{exam}/students/{student}/edit', [MarksController::class, 'editMark'])->name('marks.edit-mark');
        Route::put('marks/{assignment}/{exam}/students/{student}', [MarksController::class, 'updateMark'])->name('marks.update-mark');

        Route::get('electives', [ElectiveEnrollmentController::class, 'index'])->name('electives.index');
        Route::get('electives/{assignment}', [ElectiveEnrollmentController::class, 'show'])->name('electives.show');
        Route::put('electives/{assignment}', [ElectiveEnrollmentController::class, 'update'])->name('electives.update');

        Route::get('class-exams', [ClassExamController::class, 'index'])->name('class-exams.index');
        Route::get('class-exams/{exam}', [ClassExamController::class, 'show'])->name('class-exams.show');
        Route::patch('class-exams/{exam}/submit', [ClassExamController::class, 'submit'])->name('class-exams.submit');

        Route::get('/timetable', [TeacherTimetableController::class, 'index'])->name('timetable');

        Route::get('notices', [TeacherNoticeController::class, 'index'])->name('notices.index');
        Route::get('notices/create', [TeacherNoticeController::class, 'create'])->name('notices.create');
        Route::post('notices', [TeacherNoticeController::class, 'store'])->name('notices.store');
        Route::delete('notices/{notice}', [TeacherNoticeController::class, 'destroy'])->name('notices.destroy');
    });

/*
|--------------------------------------------------------------------------
| Parent routes (Phase 5)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');

        Route::get('/children/{student}/results', [ResultsController::class, 'index'])->name('results');
        Route::get('/children/{student}/fees', [FeesController::class, 'index'])->name('fees');
        Route::get('/children/{student}/notices', [NoticesController::class, 'index'])->name('notices');
    });
