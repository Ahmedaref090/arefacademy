<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class CourseThumbnailController extends Controller
{
    /**
     * Stream a course thumbnail from the private disk.
     *
     * Note: course thumbnails appear on public pages (landing, catalog),
     * so this route must stay public — the win here is that files are no
     * longer exposed through the public storage symlink, not that they're
     * behind auth. If thumbnails should ever become members-only, add
     * the auth middleware to the route.
     */
    public function __invoke(Course $course)
    {
        $path = $course->thumbnail;

        abort_if(! $path || ! Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
