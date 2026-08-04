<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AttachmentDownloadController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredStudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Student;
use App\Http\Controllers\Webhooks\FawryWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    return redirect()->to(
        $user ? ($user->isAdmin() ? route('admin.dashboard') : route('dashboard')) : route('login')
    );
});

// ── Guest auth (phone-based) ──────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredStudentController::class, 'create'])->name('register');
    Route::post('register', [RegisteredStudentController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Fawry webhook (server-to-server, no auth/CSRF) ────────────
Route::match(['get', 'post'], 'webhooks/fawry', FawryWebhookController::class)->name('webhooks.fawry');

// ── Student area ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('dashboard', Student\DashboardController::class)->name('dashboard');

    Route::get('courses', [Student\CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/my', [Student\CourseController::class, 'my'])->name('courses.my');
    Route::get('courses/{course:slug}', [Student\CourseController::class, 'show'])->name('courses.show');

    Route::get('lessons/{lesson}', [Student\LessonController::class, 'show'])->name('lessons.show');
    Route::post('lessons/{lesson}/complete', [Student\LessonController::class, 'complete'])->name('lessons.complete');
    Route::post('lessons/{lesson}/progress', [Student\LessonController::class, 'progress'])->name('lessons.progress');

    Route::get('attachments/{attachment}/download', AttachmentDownloadController::class)->name('attachments.download');

    Route::get('quizzes/{quiz}', [Student\QuizController::class, 'show'])->name('quizzes.show');
    Route::post('quizzes/{quiz}/start', [Student\QuizController::class, 'start'])->name('quizzes.start');
    Route::post('quizzes/{quiz}/answer', [Student\QuizController::class, 'saveAnswer'])->name('quizzes.answer');
    Route::post('quizzes/{quiz}', [Student\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('quiz-attempts/{attempt}', [Student\QuizController::class, 'result'])->name('quizzes.result');

    Route::post('assignments/{assignment}/submit', [Student\AssignmentController::class, 'store'])->name('assignments.submit');

    // ── Account section (vertical nav menu) ───────────────────
    Route::get('profile', [Student\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [Student\ProfileController::class, 'update'])->name('profile.update');
    Route::get('account/security', [Student\AccountController::class, 'security'])->name('account.security');
    Route::put('account/password', [Student\ProfileController::class, 'updatePassword'])->name('account.password.update');
    Route::get('account/exams', [Student\AccountController::class, 'examResults'])->name('account.exams');
    Route::get('account/assignments', [Student\AccountController::class, 'assignmentResults'])->name('account.assignments');
    Route::get('account/videos', [Student\AccountController::class, 'videoViews'])->name('account.videos');

    Route::get('courses/{course:slug}/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('courses/{course:slug}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::get('payments/{payment:merchant_ref_number}', [PaymentController::class, 'show'])->name('payments.show');
});

// ── Admin (teacher) area ──────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::resource('courses', Admin\CourseController::class)->except(['show']);
    Route::resource('courses.lessons', Admin\LessonController::class)->shallow()->except(['index', 'show']);
    Route::resource('lessons.quizzes', Admin\QuizController::class)->shallow()->except(['index', 'show']);
    Route::resource('lessons.assignments', Admin\AssignmentController::class)->shallow()->only(['store', 'update', 'destroy']);
    Route::delete('attachments/{attachment}', [Admin\LessonController::class, 'destroyAttachment'])->name('attachments.destroy');

    Route::get('students', [Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('students/{user}', [Admin\StudentController::class, 'show'])->name('students.show');
    Route::post('students/{user}/password', [Admin\StudentController::class, 'resetPassword'])->name('students.password');

    Route::get('submissions', [Admin\SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('submissions/{submission}/grade', [Admin\SubmissionController::class, 'grade'])->name('submissions.grade');

    Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/mark-paid', [Admin\PaymentController::class, 'markPaid'])->name('payments.mark-paid');

    Route::post('enrollments', [Admin\EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('enrollments/{enrollment}', [Admin\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
});
