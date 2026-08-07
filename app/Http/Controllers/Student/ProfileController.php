<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Rules\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('student.profile.edit', [
            'user' => $request->user(),
            'governorates' => config('governorates'),
            'grades' => GradeLevel::cases(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['nullable', new Phone],
            'governorate' => ['required', Rule::in(config('governorates'))],
            'grade_level' => ['required', Rule::enum(GradeLevel::class)],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->name = $data['name'];
        $user->parent_phone = $data['parent_phone'] ?? null;
        $user->governorate = $data['governorate'];
        $user->grade_level = $data['grade_level'];

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return back()->with('status', __('Profile updated.'));
    }

    /**
     * Dedicated password change from the Security page —
     * requires the current password (unlike the profile form).
     */
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', __('Password updated successfully.'));
    }

    /**
     * "My Courses" tab in the account section: active enrollments plus
     * per-month courses the student has at least one approved month for.
     */
    public function courses(Request $request)
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with('course.lessons')
            ->where('status', EnrollmentStatus::Active)
            ->latest('enrolled_at')
            ->get();

        $approvedMonths = $user->courseMonths()
            ->with('course')
            ->wherePivot('status', PurchaseStatus::Approved)
            ->get()
            ->sortBy([['course.title', 'asc'], ['sort_order', 'asc']])
            ->values();

        return view('student.courses.my', compact('enrollments', 'approvedMonths'));
    }
}
