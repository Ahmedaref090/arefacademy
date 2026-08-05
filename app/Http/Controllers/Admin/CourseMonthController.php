<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMonth;
use Illuminate\Http\Request;

class CourseMonthController extends Controller
{
    /**
     * Add a month to a per-month course (e.g. "August", "September").
     */
    public function store(Request $request, Course $course)
    {
        abort_unless($course->isPerMonth(), 422, 'Months can only be added to per-month courses.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $course->months()->create($data);

        return back()->with('status', 'Month added.');
    }

    public function update(Request $request, CourseMonth $month)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $month->update($data);

        return back()->with('status', 'Month updated.');
    }

    public function destroy(CourseMonth $month)
    {
        // Lessons in this month are kept but become unassigned
        // (course_month_id is set to null by the FK).
        $month->delete();

        return back()->with('status', 'Month deleted.');
    }
}
