<div>
    <label class="label" for="title">Title</label>
    <input class="input" id="title" name="title" value="{{ old('title', $lesson->title) }}" required>
</div>
<div>
    <label class="label" for="description">Description</label>
    <textarea class="input" id="description" name="description" rows="3">{{ old('description', $lesson->description) }}</textarea>
</div>
<div>
    <label class="label" for="video_url">Video URL (YouTube / Vimeo embed link)</label>
    <input class="input font-mono" id="video_url" name="video_url" type="url" dir="ltr" value="{{ old('video_url', $lesson->video_url) }}" placeholder="https://www.youtube.com/watch?v=…">
</div>
<div>
    <label class="label" for="video_path">…or Video Path (file already on the server)</label>
    @if($lesson->video_path)
        <p class="mb-1 text-xs text-gray-400">Current: <span class="font-mono" dir="ltr">{{ $lesson->video_path }}</span></p>
    @endif
    <input class="input font-mono" id="video_path" name="video_path" type="text" dir="ltr"
        value="{{ old('video_path', $lesson->video_path) }}"
        placeholder="courses/videos/intro.mp4">
    <p class="mt-1 text-xs text-gray-400">
        Large videos are not uploaded through this form. Upload the file via FTP/SSH/cPanel into
        <span class="font-mono">storage/app/public/</span> (e.g. <span class="font-mono">storage/app/public/courses/videos/intro.mp4</span>),
        then type its path here relative to the public disk.
    </p>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="duration_minutes">Duration (minutes)</label>
        <input class="input" id="duration_minutes" name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $lesson->duration_minutes) }}">
    </div>
    <div>
        <label class="label" for="sort_order">Sort Order</label>
        <input class="input" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $lesson->sort_order ?? 0) }}">
    </div>
</div>
<div>
    <label class="label" for="attachments">Attachments (PDFs, code, zips — multiple allowed)</label>
    <input class="input" id="attachments" name="attachments[]" type="file" multiple>
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_free" value="1" class="rounded" @checked(old('is_free', $lesson->is_free))>
    Free preview (visible without enrollment)
</label>
