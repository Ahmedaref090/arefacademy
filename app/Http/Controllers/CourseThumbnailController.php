<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class CourseThumbnailController extends Controller
{
    /**
     * Stream a course thumbnail from the private (local) disk.
     *
     * Note: course thumbnails appear on public pages (landing, catalog),
     * so this route must stay public — the win here is that files are no
     * longer exposed through the public storage symlink, not that they're
     * behind auth. If thumbnails should ever become members-only, add
     * the auth middleware to the route.
     */
    public function __invoke(Course $course)
    {
        // Normalize the stored path (strip any leading slashes).
        $path = ltrim((string) $course->thumbnail, '/');

        // 404 when the course has no thumbnail or the file is missing
        // from the private disk.
        abort_if($path === '' || ! Storage::disk('local')->exists($path), 404);

        // Stream the file as a binary response. Storage::response()
        // automatically sets the correct Content-Type from the file's
        // detected MIME type, and streams it (no full read into memory).
        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
