<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Manually enroll a student in a course (cash sales, scholarships...).
     * Grants a monthly subscription: 30 days of access.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        Enrollment::updateOrCreate(
            ['user_id' => $data['user_id'], 'course_id' => $data['course_id']],
            [
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => now(),
                'expires_at' => now()->addDays(30),
            ]
        );

        return back()->with('status', 'Student enrolled successfully (30-day subscription).');
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return back()->with('status', 'Enrollment removed.');
    }
}
