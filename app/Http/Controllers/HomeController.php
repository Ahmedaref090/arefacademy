<?php

namespace App\Http\Controllers;

use App\Models\Course;

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

        return view('welcome', compact('courses'));
    }
}
