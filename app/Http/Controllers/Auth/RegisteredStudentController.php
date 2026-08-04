<?php

namespace App\Http\Controllers\Auth;

use App\Enums\GradeLevel;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisteredStudentController extends Controller
{
    public function create()
    {
        return view('auth.register', [
            'governorates' => config('governorates'),
            'grades' => GradeLevel::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'governorate' => ['required', Rule::in(config('governorates'))],
            'grade_level' => ['required', Rule::enum(GradeLevel::class)],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'parent_phone' => $data['parent_phone'] ?? null,
            'governorate' => $data['governorate'],
            'grade_level' => $data['grade_level'],
            'role' => UserRole::Student,
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
