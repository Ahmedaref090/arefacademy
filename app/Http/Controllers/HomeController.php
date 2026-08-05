<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Public landing page — shows published courses to everyone.
     */
    public function __invoke()
    {
        $courses = Course::where('is_published', true)
            ->withCount('lessons')
            ->latest()
            ->take(9)
            ->get();

        // Platform stats for the animated counters. Cached for an hour so
        // the landing page doesn't run COUNT queries on every visit.
        $stats = Cache::remember('landing.stats', now()->addHour(), fn () => [
            'students' => User::students()->count(),
            'courses' => Course::where('is_published', true)->count(),
            'enrollments' => Enrollment::count(),
        ]);

        return view('welcome', compact('courses', 'stats'));
    }
}
