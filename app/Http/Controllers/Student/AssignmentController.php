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

        // Lock: once a submission exists, the student cannot resubmit or modify it.
        if ($assignment->submissionFor($user)) {
            return back()->withErrors(['file' => 'You have already submitted this assignment.']);
        }

        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
            'code' => ['nullable', 'string'],
        ]);

        if (! $request->hasFile('file') && blank($data['code'] ?? null)) {
            return back()->withErrors(['file' => 'Upload a file or paste your code.']);
        }

        // Submissions are PRIVATE — students download their own via
        // submissions.download, admins via admin.files.show.
        $path = $request->hasFile('file')
            ? $request->file('file')->store("submissions/{$assignment->id}", 'local')
            : null;

        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'code' => $data['code'] ?? null,
        ]);

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
