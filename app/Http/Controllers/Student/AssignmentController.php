<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentController extends Controller
{
    public function store(Request $request, Assignment $assignment)
    {
        $user = $request->user();

        abort_unless($user->isEnrolledIn($assignment->lesson->course) || $assignment->lesson->is_free, 403);

        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
            'code' => ['nullable', 'string'],
        ]);

        $existing = $assignment->submissionFor($user);

        if (! $request->hasFile('file') && blank($data['code'] ?? null) && ! $existing) {
            return back()->withErrors(['file' => 'Upload a file or paste your code.']);
        }

        $path = $existing?->file_path;
        if ($request->hasFile('file')) {
            // Submissions are PRIVATE — students download their own via
            // submissions.download, admins via admin.files.show.
            $path = $request->file('file')->store("submissions/{$assignment->id}", 'local');
        }

        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => $user->id],
            [
                'file_path' => $path,
                'code' => $data['code'] ?? $existing?->code,
                // Resubmission resets grading.
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
            ]
        );

        return back()->with('status', 'Assignment submitted.');
    }

    /**
     * Download a submission file from the PRIVATE disk.
     * Students may only download their OWN submissions;
     * admins use the admin.files.show route instead.
     */
    public function download(Request $request, Submission $submission): StreamedResponse
    {
        abort_unless($submission->user_id === $request->user()->id, 403);
        abort_unless($submission->file_path, 404);
        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path);
    }
}
