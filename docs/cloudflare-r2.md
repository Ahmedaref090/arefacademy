# Cloudflare R2 Direct Video Upload

Very large videos are uploaded **straight from the browser** to Cloudflare R2 so they
never consume server bandwidth or storage. The app server only issues short-lived
**presigned URLs** (PUT for upload, GET for playback) and stores the R2 **object key** in
the `lessons.video_path` column. The bucket stays **private**; playback uses expiring
signed URLs, and the file is removed from R2 automatically when its lesson is deleted.

## Flow

1. Admin picks a video in the lesson form.
2. The frontend calls `POST /admin/videos/presigned-upload` → Laravel returns a
   time-limited presigned **PUT** URL and the final object key.
3. The browser uploads the file **directly to R2** (never through the app server),
   showing upload progress.
4. The R2 key is saved into the lesson's `video_path` and persists with the normal
   lesson save.
5. When a student opens the lesson, `Lesson::videoSrc()` detects the R2 key and returns a
   **3-hour temporary** signed URL for playback. (The lesson view already uses
   `videoSrc()`, so no view changes were needed.)
6. When the lesson is deleted, the `static::deleting` model event removes the file
   from R2 automatically.

## Configuration

Add to `.env`:

    CLOUDFLARE_BUCKET="arefacademy"
    CLOUDFLARE_ACCESS_KEY="..."
    CLOUDFLARE_SECRET_KEY="..."
    CLOUDFLARE_ENDPOINT="https://<account>.r2.cloudflarestorage.com"
    CLOUDFLARE_REGION="auto"
    CLOUDFLARE_PUBLIC_BASE_URL=""
    R2_VIDEO_DIR="videos"

The disk is defined in `config/filesystems.php` under `disks.r2` (an S3-compatible driver
pointed at the R2 endpoint, `visibility` = `private`).

## CORS configuration (apply in the R2 dashboard)

Because the browser writes directly to R2, you must allow your app's origin on the bucket.
In **Cloudflare R2 → your bucket → Settings → CORS Policy**, add a rule like this:

```json
[
    {
        "AllowedOrigins": [
            "https://your-domain.com",
            "http://localhost:8000"
        ],
        "AllowedMethods": [
            "GET",
            "PUT"
        ],
        "AllowedHeaders": [
            "Content-Type"
        ],
        "ExposeHeaders": [
            "ETag"
        ],
        "MaxAgeSeconds": 3600
    }
]
```

Notes:

- **PUT** is required for direct uploads; **GET** allows the browser/trackers to read.
- Replace the origins with your real app URL(s). Include `http://localhost:8000` while
  developing (`php artisan serve`).
- If videos are streamed with `Range` requests (they are, for `<video>` playback), keep
  `GET` allowed — R2 handles range requests automatically.
- Rotate the credentials in `.env` immediately if this document is ever committed to a
  public repository — never store production secrets in source control.

## Relevant files

- `config/filesystems.php` — the `r2` disk.
- `app/Http/Controllers/Admin/VideoController.php` — presigned upload URL endpoint.
- `app/Http/Controllers/Student/LessonController.php` (`videoUrl`) — temporary playback URL.
- `resources/views/admin/lessons/_vid_upload.blade.php` — direct-upload UI (vanilla JS).
- `app/Models/Lesson.php` — `isStoredOnR2()`, `tempVideoUrl()`, `videoSrc()`, `deleting` event.
- `tests/Unit/LessonR2Test.php` — unit coverage for R2 detection + deletion cleanup.