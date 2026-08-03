#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# Aref Academy – LMS scaffolding (run from project root)
# ─────────────────────────────────────────────────────────────

# 1) Models + Migrations
php artisan make:model Course -m
php artisan make:model Lesson -m
php artisan make:model Attachment -m
php artisan make:model Quiz -m
php artisan make:model Question -m
php artisan make:model QuizAttempt -m
php artisan make:model Assignment -m
php artisan make:model Submission -m
php artisan make:model Enrollment -m
php artisan make:model Payment -m

# Progress tracking pivot (no model needed)
php artisan make:migration create_lesson_user_table

# 2) Admin (Teacher) controllers
php artisan make:controller Admin/DashboardController --invokable
php artisan make:controller Admin/CourseController --resource
php artisan make:controller Admin/LessonController --resource
php artisan make:controller Admin/QuizController --resource
php artisan make:controller Admin/AssignmentController --resource
php artisan make:controller Admin/StudentController
php artisan make:controller Admin/SubmissionController

# 3) Student controllers
php artisan make:controller Student/DashboardController --invokable
php artisan make:controller Student/CourseController
php artisan make:controller Student/LessonController
php artisan make:controller Student/QuizController
php artisan make:controller Student/AssignmentController
php artisan make:controller Student/ProfileController

# 4) Phone-based auth + Fawry payments
php artisan make:controller Auth/RegisteredStudentController
php artisan make:controller Auth/AuthenticatedSessionController
php artisan make:controller PaymentController
php artisan make:controller Webhooks/FawryWebhookController --invokable

# 5) Fawry service (plain class, no generator)
mkdir -p app/Services
# → app/Services/FawryPaymentService.php will be created in a later step

# 6) Run the migrations
php artisan migrate
