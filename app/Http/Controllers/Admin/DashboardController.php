<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                'students' => User::where('role', UserRole::Student)->count(),
                'courses' => Course::count(),
                'active_enrollments' => Enrollment::where('status', EnrollmentStatus::Active)->count(),
                'revenue' => Payment::paid()->sum('amount'),
            ],
            'recentPayments' => Payment::with('user', 'course')->latest()->limit(8)->get(),
            'recentStudents' => User::where('role', UserRole::Student)->latest()->limit(8)->get(),
        ]);
    }
}
