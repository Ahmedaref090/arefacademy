<div>
    <label class="label" for="title">Title</label>
    <input class="input" id="title" name="title" value="{{ old('title', $course->title) }}" required>
</div>
<div>
    <label class="label" for="slug">Slug (optional — auto-generated from title)</label>
    <input class="input font-mono" id="slug" name="slug" value="{{ old('slug', $course->slug) }}" dir="ltr">
</div>
<div>
    <label class="label" for="description">Description</label>
    <textarea class="input" id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="price">Price (EGP)</label>
        <input class="input" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $course->price ?? 0) }}" required>
    </div>
    <div>
        <label class="label" for="duration_weeks">Duration (weeks)</label>
        <input class="input" id="duration_weeks" name="duration_weeks" type="number" min="1" value="{{ old('duration_weeks', $course->duration_weeks) }}">
    </div>
</div>
<div>
    <label class="label" for="grade_level">Grade Level</label>
    <select class="input" id="grade_level" name="grade_level">
        <option value="">All grades</option>
        @foreach($grades as $grade)
            <option value="{{ $grade->value }}" @selected(old('grade_level', $course->grade_level?->value) === $grade->value)>{{ $grade->label() }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="label" for="thumbnail">Thumbnail</label>
    @if($course->thumbnail)
        <img src="{{ Storage::disk('public')->url($course->thumbnail) }}" alt="" class="mb-2 h-24 rounded-lg object-cover">
    @endif
    <input class="input" id="thumbnail" name="thumbnail" type="file" accept="image/*">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_published" value="1" class="rounded" @checked(old('is_published', $course->is_published))>
    Published (visible to students)
</label>
