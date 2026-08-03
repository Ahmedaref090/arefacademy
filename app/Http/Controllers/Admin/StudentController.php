<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = User::where('role', UserRole::Student)
            ->when($request->filled('course'), fn ($q) => $q->whereHas('enrollments', fn ($e) => $e
                ->where('course_id', $request->integer('course'))
                ->where('status', EnrollmentStatus::Active)))
            ->when($request->filled('governorate'), fn ($q) => $q
                ->where('governorate', $request->string('governorate')->toString()))
            ->when($request->filled('grade'), fn ($q) => $q
                ->where('grade_level', $request->string('grade')->toString()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->string('search')->toString() . '%';
                $q->where(fn ($s) => $s->where('name', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->withCount('enrollments')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'courses' => Course::orderBy('title')->get(),
            'governorates' => config('governorates'),
            'grades' => GradeLevel::cases(),
        ]);
    }

    public function show(User $user)
    {
        abort_unless($user->isStudent(), 404);

        $user->load([
            'enrollments.course.lessons',
            'quizAttempts.quiz.lesson.course',
            'submissions.assignment.lesson.course',
        ]);

        return view('admin.students.show', compact('user'));
    }
}
