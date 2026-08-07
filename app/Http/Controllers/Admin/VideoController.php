<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    /**
     * The R2 object key prefix for lessons videos, e.g. "videos/v1.mp4".
     */
    protected const VIDEO_DIR = 'videos';

    /**
     * How long a browser has to finish uploading (10 minutes is generous
     * even for multi-GB files on slow connections).
     */
    protected const UPLOAD_EXPIRY = 10;

    /**
     * How long a playback URL stays valid for a student (3 hours).
     */
    protected const PLAYBACK_EXPIRY = 180;

    /**
     * Issue a presigned PUT URL so the browser can upload the video straight
     * to Cloudflare R2 without the file ever touching our server. We return
     * the URL and the R2 object key; the frontend uploads to the URL and then
     * saves the key into the lesson's video_path.
     */
    public function presignedUpload(Request $request)
    {
        $request->validate([
            'filename' => ['required', 'string', 'max:255'],
        ]);

        $disk = Storage::disk('r2');

        // Sanitize the original name to a safe key: keep the extension,
        // strip everything else, and namespace by date to avoid collisions.
        $ext = strtolower(pathinfo($request->filename, PATHINFO_EXTENSION)) ?: 'mp4';
        $key = self::VIDEO_DIR.'/'.now()->format('Y/m').'/'.Str::uuid().'.'.$ext;

        // Cloudflare R2 expects Content-Type in a lowercase header for signed
        // uploads, otherwise the object is stored as application/octet-stream.
        $client = $disk->getClient();
        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.r2.bucket'),
            'Key' => $key,
            'ContentType' => $request->input('content_type') ?: 'video/mp4',
        ]);

        $url = $client->createPresignedRequest(
            $command,
            now()->addMinutes(self::UPLOAD_EXPIRY)
        )->getUri();

        return response()->json([
            'url' => (string) $url,
            'key' => $key,
            'expires_in' => self::UPLOAD_EXPIRY * 60,
        ]);
    }

    /**
     * Generate a temporary (3-hour) signed URL so a student can stream the
     * video. The bucket stays private — only people with this URL can watch.
     */
    public function playback(Lesson $lesson)
    {
        abort_unless($lesson->video_path, 404);

        $disk = Storage::disk('r2');

        if (! $disk->exists($lesson->video_path)) {
            abort(404);
        }

        return response()->json([
            'url' => $disk->temporaryUrl($lesson->video_path, now()->addMinutes(self::PLAYBACK_EXPIRY)),
            'expires_at' => now()->addMinutes(self::PLAYBACK_EXPIRY)->toIso8601String(),
        ]);
    }
}
