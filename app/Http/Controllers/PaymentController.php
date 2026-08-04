<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\FawryPaymentService;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function checkout(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);
        abort_if($request->user()->hasActiveSubscriptionTo($course), 403);

        return view('payments.checkout', compact('course'));
    }

    public function pay(Request $request, Course $course, FawryPaymentService $fawry)
    {
        abort_unless($course->