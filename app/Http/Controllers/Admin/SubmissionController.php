<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $submissions = Submission::with('user', 'assignment.lesson.course')
            ->when($request->string('status')->toString() === 'graded', fn ($q) => $q->whereNotNull('graded_at'))
            ->when($request->string('status')->toString() === 'pending', fn ($q) => $q->whereNull('graded_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.submissions.index', compact('submissions'));
    }

    public function grade(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'graded_at' => now(),
        ]);

        return back()->with('status', __('Submission graded.'));
    }
}
