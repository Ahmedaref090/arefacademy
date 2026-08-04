<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentDownloadController extends Controller
{
    /**
     * Serve a lesson attachment from the private disk — only to admins,
     * enrolled students, or anyone when the lesson is a free preview.
     */
    public function __invoke(Request $request, Attachment $attachment)
    {
        $user = $request->user();
        $lesson = $attachment->lesson;

        abort_unless($user->isAdmin() || $lesson->is_free || $user->isEnrolledIn($lesson->course), 403);
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->title);
    }
}
