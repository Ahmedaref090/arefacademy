@php
    $courseTitle = $course->rawTranslations('title');
    $courseDesc = $course->rawTranslations('description');
@endphp
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="title_ar">{{ __('Title') }} (AR)</label>
        <input class="input" id="title_ar" name="title_ar" value="{{ old('title_ar', $courseTitle['ar'] ?? '') }}" required>
    </div>
    <div>
        <label class="label" for="title_en">{{ __('Title') }} (EN)</label>
        <input class="input" id="title_en" name="title_en" value="{{ old('title_en', $courseTitle['en'] ?? '') }}">
    </div>
</div>
<div>
    <label class="label" for="slug">{{ __('Slug (optional — auto-generated from title)') }}</label>
    <input class="input font-mono" id="slug" name="slug" value="{{ old('slug', $course->slug) }}" dir="ltr">
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="description_ar">{{ __('Description') }} (AR)</label>
        <textarea class="input" id="description_ar" name="description_ar" rows="4">{{ old('description_ar', $courseDesc['ar'] ?? '') }}</textarea>
    </div>
    <div>
        <label class="label" for="description_en">{{ __('Description') }} (EN)</label>
        <textarea class="input" id="description_en" name="description_en" rows="4">{{ old('description_en', $courseDesc['en'] ?? '') }}</textarea>
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="price">{{ __('Price (EGP)') }}</label>
        <input class="input" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $course->price ?? 0) }}" required>
    </div>
    <div>
        <label class="label" for="sale_price">{{ __('Sale Price (EGP, optional)') }}</label>
        <input class="input" id="sale_price" name="sale_price" type="number" step="0.01" min="0" value="{{ old('sale_price', $course->sale_price) }}">
        <p class="mt-1 text-xs text-gray-400">{{ __('Leave empty for no discount. Must be lower than the regular price.') }}</p>
    </div>
</div>
<div>
    <label class="label" for="pricing_type">{{ __('Pricing Type') }}</label>
    <select class="input" id="pricing_type" name="pricing_type" required>
        <option value="" disabled @selected(old('pricing_type', $course->pricing_type?->value) === null)>{{ __('Select pricing type…') }}</option>
        @foreach($pricingTypes as $type)
            <option value="{{ $type->value }}" @selected(old('pricing_type', $course->pricing_type?->value) === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
    @error('pricing_type')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
    <p class="mt-1 text-xs text-gray-400">{{ __('Lifetime = one-time purchase of the whole course. Per Month = the course is split into months that students subscribe to individually.') }}</p>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="label" for="duration_weeks">{{ __('Duration (weeks)') }}</label>
        <input class="input" id="duration_weeks" name="duration_weeks" type="number" min="1" value="{{ old('duration_weeks', $course->duration_weeks) }}">
    </div>
    <div>
        <label class="label" for="grade_level">{{ __('Grade Level') }}</label>
        <select class="input" id="grade_level" name="grade_level">
            <option value="">{{ __('All grades') }}</option>
            @foreach($grades as $grade)
                <option value="{{ $grade->value }}" @selected(old('grade_level', $course->grade_level?->value) === $grade->value)>{{ $grade->label() }}</option>
            @endforeach
        </select>
    </div>
</div>
<div>
    <label class="label" for="whatsapp_group_link">{{ __('WhatsApp Group Link (optional)') }}</label>
    <input class="input font-mono" id="whatsapp_group_link" name="whatsapp_group_link" type="url"
        value="{{ old('whatsapp_group_link', $course->whatsapp_group_link) }}" dir="ltr"
        placeholder="https://chat.whatsapp.com/...">
    <p class="mt-1 text-xs text-gray-400">{{ __('Only students with an active, non-expired subscription will see the join button.') }}</p>
</div>
<div>
    <label class="label" for="thumbnail">{{ __('Thumbnail') }}</label>
    @if($course->thumbnail)
        <img src="{{ Storage::disk('public')->url($course->thumbnail) }}" alt="" class="mb-2 h-24 rounded-lg object-cover">
    @endif
    <input class="input" id="thumbnail" name="thumbnail" type="file" accept="image/*">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_published" value="1" class="rounded" @checked(old('is_published', $course->is_published))>
    {{ __('Published (visible to students)') }}
</label>
