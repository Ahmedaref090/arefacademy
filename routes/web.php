<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AttachmentDownloadController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredStudentController;
use App\Http\Controllers\CourseThumbnailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Student;
use Illuminate\Support\Facades\Route;

// ── Public landing page ───────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

// ── SEO: dynamic sitemap (crawlers fetch this frequently) ─────
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ── Course thumbnails (streamed from the private disk) ────────
Route::get('courses/{course}/thumbnail', CourseThumbnailController::class)->name('courses.thumbnail');

// ── Locale switcher (ar default, en optional) ─────────────────
Route::get('locale/{locale}', LocaleController::class)->name('locale.switch');

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

// ── Student area ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('dashboard', Student\DashboardController::class)->name('dashboard');

    Route::get('courses', [Student\CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/my', [Student\CourseController::class, 'my'])->name('courses.my');
    Route::get('courses/{course:id}/months', [Student\CourseController::class, 'months'])->name('courses.months');
    Route::get('courses/{course:slug}', [Student\CourseController::class, 'show'])->name('courses.show');

    Route::get('my-invoices', [Student\InvoiceController::class, 'index'])->name('invoices.index');

    Route::get('lessons/{lesson}', [Student\LessonController::class, 'show'])->name('lessons.show');
    Route::post('lessons/{lesson}/complete', [Student\LessonController::class, 'complete'])->name('lessons.complete');
    Route::post('lessons/{lesson}/progress', [Student\LessonController::class, 'progress'])->name('lessons.progress');

    Route::get('attachments/{attachment}/download', AttachmentDownloadController::class)->name('attachments.download');

    // Secure R2 playback — temporary URL for streaming a lesson video.
    Route::get('lessons/{lesson}/video-url', [Student\LessonController::class, 'videoUrl'])->name('lessons.video-url');

    Route::get('quizzes/{quiz}', [Student\QuizController::class, 'show'])->name('quizzes.show');
    Route::post('quizzes/{quiz}/start', [Student\QuizController::class, 'start'])->name('quizzes.start');
    Route::post('quizzes/{quiz}/answer', [Student\QuizController::class, 'saveAnswer'])->name('quizzes.answer');
    Route::post('quizzes/{quiz}', [Student\QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('quiz-attempts/{attempt}', [Student\QuizController::class, 'result'])->name('quizzes.result');

    Route::post('assignments/{assignment}/submit', [Student\AssignmentController::class, 'store'])->name('assignments.submit');
    Route::get('submissions/{submission}/download', [Student\AssignmentController::class, 'download'])->name('submissions.download');

    // ── Account section (vertical nav menu) ───────────────────
    Route::get('contact', [Student\ContactController::class, 'index'])->name('contact');

    Route::get('profile', [Student\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [Student\ProfileController::class, 'update'])->name('profile.update');
    Route::get('account/security', [Student\AccountController::class, 'security'])->name('account.security');
    Route::put('account/password', [Student\ProfileController::class, 'updatePassword'])->name('account.password.update');
    Route::get('account/courses', [Student\ProfileController::class, 'courses'])->name('account.courses');
    Route::get('account/exams', [Student\AccountController::class, 'examResults'])->name('account.exams');
    Route::get('account/assignments', [Student\AccountController::class, 'assignmentResults'])->name('account.assignments');
    Route::get('account/videos', [Student\AccountController::class, 'videoViews'])->name('account.videos');

    // ── Hybrid purchasing: purchase requests ──────────────────
    Route::post('courses/{course:slug}/purchase', [Student\PurchaseController::class, 'store'])->name('courses.purchase');

    // ── Month subscription requests (per-month courses) ───────
    Route::post('enrollments', [Student\EnrollmentController::class, 'store'])->name('enrollments.store');

    Route::get('courses/{course:slug}/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::post('courses/{course:slug}/pay', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
});

// ── Admin (teacher) area ──────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::resource('courses', Admin\CourseController::class)->except(['show']);
    Route::resource('courses.lessons', Admin\LessonController::class)->shallow()->except(['index', 'show']);
    Route::resource('courses.months', Admin\CourseMonthController::class)->shallow()->only(['store', 'update', 'destroy']);
    Route::resource('lessons.quizzes', Admin\QuizController::class)->shallow()->except(['index', 'show']);
    Route::resource('lessons.assignments', Admin\AssignmentController::class)->shallow()->only(['store', 'update', 'destroy']);
    Route::delete('attachments/{attachment}', [Admin\LessonController::class, 'destroyAttachment'])->name('attachments.destroy');

    // ── Subscription approval workflow ────────────────────────
    Route::get('subscriptions', [Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions/course-requests/{id}/approve', [Admin\SubscriptionController::class, 'approveCourse'])->name('subscriptions.course.approve');
    Route::post('subscriptions/course-requests/{id}/reject', [Admin\SubscriptionController::class, 'rejectCourse'])->name('subscriptions.course.reject');
    Route::post('subscriptions/month-requests/{id}/approve', [Admin\SubscriptionController::class, 'approveMonth'])->name('subscriptions.month.approve');
    Route::post('subscriptions/month-requests/{id}/reject', [Admin\SubscriptionController::class, 'rejectMonth'])->name('subscriptions.month.reject');

    // Private files (receipts, submissions, attachments) — admin only.
    // The '.*' constraint allows slashes in the {path} parameter.
    Route::get('files/{path}', [Admin\FileController::class, 'show'])
        ->where('path', '.*')
        ->name('files.show');

    Route::get('students', [Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('students/{user}', [Admin\StudentController::class, 'show'])->name('students.show');
    Route::post('students/{user}/password', [Admin\StudentController::class, 'resetPassword'])->name('students.password');

    Route::get('students/{user}/devices', [Admin\DeviceController::class, 'index'])->name('students.devices');
    Route::delete('students/{user}/devices/{device}', [Admin\DeviceController::class, 'destroy'])->name('students.devices.destroy');

    Route::get('submissions', [Admin\SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('submissions/{submission}/grade', [Admin\SubmissionController::class, 'grade'])->name('submissions.grade');

    // Cloudflare R2 — presigned upload (file goes straight from the browser).
    Route::post('videos/presigned-upload', [Admin\VideoController::class, 'presignedUpload'])->name('videos.presigned-upload');

    Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/approve', [Admin\PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('payments/{payment}/reject', [Admin\PaymentController::class, 'reject'])->name('payments.reject');

    Route::post('enrollments', [Admin\EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::delete('enrollments/{enrollment}', [Admin\EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
    Route::post('students/{user}/months/{courseMonth}/revoke', [Admin\EnrollmentController::class, 'destroyMonth'])->name('enrollments.month.revoke');
    Route::get('courses/{course:id}/months', [Admin\EnrollmentController::class, 'months'])->name('courses.months');
});
