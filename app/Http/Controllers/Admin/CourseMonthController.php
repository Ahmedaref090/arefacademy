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
        abort_unless($course->isPerMonth(), 422, __('Months can only be added to per-month courses.'));

        $data = $this->validateMonth($request);
        $data['name'] = $this->translationsFrom($data, 'name');

        $course->months()->create($data);

        return back()->with('status', __('Month added.'));
    }

    public function update(Request $request, CourseMonth $month)
    {
        $data = $this->validateMonth($request);
        $data['name'] = $this->translationsFrom($data, 'name');

        $month->update($data);

        return back()->with('status', __('Month updated.'));
    }

    public function destroy(CourseMonth $month)
    {
        // Lessons in this month are kept but become unassigned
        // (course_month_id is set to null by the FK).
        $month->delete();

        return back()->with('status', __('Month deleted.'));
    }

    protected function validateMonth(Request $request): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * Turn flat "<column>_ar"/"<column>_en" request data into a translatable
     * {ar, en} array, removing the flat keys from the payload.
     */
    protected function translationsFrom(array &$data, string $column): array
    {
        $translations = [
            'ar' => $data["{$column}_ar"] ?? '',
            'en' => $data["{$column}_en"] ?? '',
        ];

        unset($data["{$column}_ar"], $data["{$column}_en"]);

        return $translations;
    }
}
