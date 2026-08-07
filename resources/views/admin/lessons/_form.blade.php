@php
    $lessonTitle = $lesson->rawTranslations('title');
    $lessonDesc = $lesson->rawTranslations('description');
@endphp
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="title_ar">{{ __('Title') }} (AR)</label>
        <input class="input" id="title_ar" name="title_ar" value="{{ old('title_ar', $lessonTitle['ar'] ?? '') }}" required>
    </div>
    <div>
        <label class="label" for="title_en">{{ __('Title') }} (EN)</label>
        <input class="input" id="title_en" name="title_en" value="{{ old('title_en', $lessonTitle['en'] ?? '') }}">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="description_ar">{{ __('Description') }} (AR)</label>
        <textarea class="input" id="description_ar" name="description_ar" rows="3">{{ old('description_ar', $lessonDesc['ar'] ?? '') }}</textarea>
    </div>
    <div>
        <label class="label" for="description_en">{{ __('Description') }} (EN)</label>
        <textarea class="input" id="description_en" name="description_en" rows="3">{{ old('description_en', $lessonDesc['en'] ?? '') }}</textarea>
    </div>
</div>
{{-- Month assignment — rendered ONLY for per-month courses.
     NOTE: $course->pricing_type is a PricingType enum (cast on the model),
     so a string comparison like $course->pricing_type === 'per_month' would
     never match. $course->isPerMonth() is the correct check. --}}
@if($course->isPerMonth())
    <div>
        <label class="label" for="course_month_id">{{ __('Month') }}</label>
        <select class="input" id="course_month_id" name="course_month_id" required>
            <option value="" disabled @selected(old('course_month_id', $lesson->course_month_id) === null)>{{ __('Select month…') }}</option>
            @foreach($months as $month)
                <option value="{{ $month->id }}" @selected((int) old('course_month_id', $lesson->course_month_id) === $month->id)>{{ $month->name }}</option>
            @endforeach
        </select>
        @error('course_month_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
        @if($months->isEmpty())
            <p class="mt-1 text-xs text-amber-500">
                {{ __('This course has no months yet — add the months on the') }}
                <a class="underline" href="{{ route('admin.courses.edit', $course) }}">{{ __('course edit page') }}</a>.
            </p>
        @endif
    </div>
@endif
<div>
    <label class="label" for="video_url">{{ __('Video URL (YouTube / Vimeo embed link)') }}</label>
    <input class="input font-mono" id="video_url" name="video_url" type="url" dir="ltr" value="{{ old('video_url', $lesson->video_url) }}" placeholder="https://www.youtube.com/watch?v=…">
</div>

{{-- Self-hosted video: either a direct Cloudflare R2 upload or a manual path. --}}
@include('admin.lessons._vid_upload', ['lesson' => $lesson])
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="duration_minutes">{{ __('Duration (minutes)') }}</label>
        <input class="input" id="duration_minutes" name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $lesson->duration_minutes) }}">
    </div>
    <div>
        <label class="label" for="sort_order">{{ __('Sort Order') }}</label>
        <input class="input" id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $lesson->sort_order ?? 0) }}">
    </div>
</div>
<div>
    <label class="label" for="attachments">{{ __('Attachments (PDFs, code, zips — multiple allowed)') }}</label>
    <input class="input" id="attachments" name="attachments[]" type="file" multiple>
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_free" value="1" class="rounded" @checked(old('is_free', $lesson->is_free))>
    {{ __('Free preview (visible without enrollment)') }}
</label>
