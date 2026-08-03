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

        return back()->with('status', 'Assignment created.');
    }

    public function update(Request $request, Assignment $assignment)
    {
        $assignment->update($this->validateAssignment($request));

        return back()->with('status', 'Assignment updated.');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return back()->with('status', 'Assignment deleted.');
    }

    protected function validateAssignment(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
        ]);
    }
}
