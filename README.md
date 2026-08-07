<<<<<<< HEAD
# Aref Academy

A Learning Management System (LMS) for programming courses, built with Laravel, Blade, Tailwind CSS v4, and Alpine.js — with Fawry Pay integration for the Egyptian market.

## Features

### Students
- **Phone-based auth** — registration & login with a unique phone number (no email needed), plus governorate (27 Egyptian governorates) and grade level (1st Secondary / 1st Bac / 2nd Bac).
- **Course catalog** — browse published courses, see price/duration/lessons, free previews.
- **Learning environment** — video lessons (YouTube/Vimeo embed or self-hosted), downloadable resources per lesson, MCQ quizzes with optional countdown timer, assignments with file upload or pasted code.
- **Activity tracking** — dashboard stat cards, 14-day activity chart, per-course progress bars, automatic watch-time tracking.
- **Dark / light mode** toggle, sidebar navigation, mobile-friendly.

### Admin (Teacher)
- Full CRUD for courses, lessons (video + attachments), quizzes (dynamic question builder), assignments.
- Student management with filters (course, governorate, grade, search), per-student progress/quiz/submission overview.
- Manual enrollment (cash sales), enrollment revocation, student password reset.
- Payments dashboard with filters + manual "Mark Paid" (activates enrollment).
- Assignment grading with score + feedback.

### Payments (Fawry)
- `App\Services\FawryPaymentService` generates PayAtFawry charge requests & reference numbers.
- Webhook at `/webhooks/fawry` (GET or POST, CSRF-exempt, signature-verified) handles `PAID` / `UNPAID` / `EXPIRED` / `CANCELED` / `REFUNDED` and unlocks the course automatically on `PAID`.

## Requirements

- PHP 8.2+, Composer, Node 18+, MySQL 8

## Setup

    composer install
    cp .env.example .env          # then fill in DB + Fawry keys (below)
    php artisan key:generate
    npm install
    php artisan migrate --seed
    php artisan storage:link
    npm run dev                   # or: npm run build
    php artisan serve

### Environment (.env)

    DB_DATABASE=aref_academy
    DB_USERNAME=...
    DB_PASSWORD=...

    FAWRY_MERCHANT_CODE=your_merchant_code
    FAWRY_SECURITY_KEY=your_security_key
    FAWRY_BASE_URL=https://atfawry.fawrystaging.com   # production: https://www.atfawry.com
    FAWRY_CURRENCY=EGP
    FAWRY_EXPIRY_HOURS=72

### Default admin account (from seeder)

- **Login:** `aref`  **Password:** `ahmedaref`  — change the password after first login.
- Login is phone-based, so the admin's username is stored in the `phone` field.

### Fawry webhook

Give Fawry this callback URL in your merchant dashboard:

    https://your-domain.com/webhooks/fawry

Test the full flow in the Fawry staging environment before going live, and verify
the signature field order in `FawryPaymentService` against your integration docs.

## Testing

    php artisan test

Feature tests cover: phone auth, admin access control, free/paid enrollment
(Fawry API mocked via `Http::fake()`), quiz scoring, and the Fawry webhook
(signature verification, PAID/EXPIRED handling).

## Security notes

- Lesson **attachments** are stored on the private disk and served through an
  authorized download route (admin / enrolled students / free previews only).
- Uploaded **videos** are on the public disk for simplicity — move them behind
  signed URLs or a CDN (Bunny/CloudFront) for production.
- The Fawry webhook verifies the SHA-256 message signature before any state change.

## Project structure (key paths)

    app/Enums/                     UserRole, GradeLevel, EnrollmentStatus, PaymentStatus
    app/Models/                    User, Course, Lesson, Attachment, Quiz, Question,
                                   QuizAttempt, Assignment, Submission, Enrollment, Payment
    app/Services/FawryPaymentService.php
    app/Http/Controllers/Admin/    Teacher dashboard & management
    app/Http/Controllers/Student/  Learning experience
    app/Http/Controllers/Webhooks/FawryWebhookController.php
    config/fawry.php               Fawry credentials (from .env)
    config/governorates.php        Egyptian governorates list
    resources/views/               Blade + Tailwind v4 + Alpine.js
=======
# arefacademy
>>>>>>> origin/main
