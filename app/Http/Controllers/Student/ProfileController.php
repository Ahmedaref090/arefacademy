<?php

namespace App\Http\Controllers\Student;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
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
            'parent_phone' => ['nullable', 'string', 'max:20'],
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

        return back()->with('status', 'Profile updated.');
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

        return back()->with('status', 'Password updated successfully.');
    }
}
