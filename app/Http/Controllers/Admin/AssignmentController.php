<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function store(Request $request, Lesson $lesson)
    {
        $lesson->assignments()->create($this->validateAssignment($request));

        return back()->with('status', __('Assignment created.'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $assignment->update($this->validateAssignment($request));

        return back()->with('status', __('Assignment updated.'));
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return back()->with('status', __('Assignment deleted.'));
    }

    protected function validateAssignment(Request $request): array
    {
        $data = $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'max_score' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
        ]);

        $data['title'] = $this->translationsFrom($data, 'title');
        $data['description'] = $this->translationsFrom($data, 'description');

        return $data;
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
