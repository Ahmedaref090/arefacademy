@php
    $initialQuestions = old('questions') ?? (isset($quiz) && $quiz->exists
        ? $quiz->questions->map(fn ($q) => ['text' => $q->question_text, 'options' => array_values($q->options), 'correct' => $q->correct_option])->values()->all()
        : [['text' => '', 'options' => ['', ''], 'correct' => 0]]);
@endphp

<div class="card space-y-4">
    <div>
        @php $quizTitle = $quiz->rawTranslations('title'); $quizDesc = $quiz->rawTranslations('description'); @endphp
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label" for="title_ar">{{ __('Quiz Title') }} (AR)</label>
                <input class="input" id="title_ar" name="title_ar" value="{{ old('title_ar', $quizTitle['ar'] ?? '') }}" required>
            </div>
            <div>
                <label class="label" for="title_en">{{ __('Quiz Title') }} (EN)</label>
                <input class="input" id="title_en" name="title_en" value="{{ old('title_en', $quizTitle['en'] ?? '') }}">
            </div>
        </div>
    </div>
    <div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label" for="description_ar">{{ __('Description') }} (AR)</label>
                <textarea class="input" id="description_ar" name="description_ar" rows="2">{{ old('description_ar', $quizDesc['ar'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="label" for="description_en">{{ __('Description') }} (EN)</label>
                <textarea class="input" id="description_en" name="description_en" rows="2">{{ old('description_en', $quizDesc['en'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="label" for="pass_score">{{ __('Pass Score (%)') }}</label>
            <input class="input" id="pass_score" name="pass_score" type="number" min="0" max="100" value="{{ old('pass_score', $quiz->pass_score ?? 50) }}" required>
        </div>
        <div>
            <label class="label" for="time_limit_minutes">{{ __('Time Limit (min, optional)') }}</label>
            <input class="input" id="time_limit_minutes" name="time_limit_minutes" type="number" min="1" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}">
        </div>
        <div>
            <label class="label" for="max_attempts">{{ __('Max Attempts (optional)') }}</label>
            <input class="input" id="max_attempts" name="max_attempts" type="number" min="1" value="{{ old('max_attempts', $quiz->max_attempts) }}" placeholder="{{ __('Unlimited') }}">
        </div>
    </div>
</div>

<div x-data='{ questions: @json($initialQuestions) }' class="space-y-4">
    <template x-for="(q, qi) in questions" :key="qi">
        <div class="card space-y-3">
            <div class="flex items-center justify-between">
                <span class="font-semibold" x-text="'{{ __('Question') }} ' + (qi + 1)"></span>
                <button type="button" class="btn-danger" x-show="questions.length > 1" @click="questions.splice(qi, 1)">{{ __('Remove') }}</button>
            </div>
            <input type="text" class="input" :name="`questions[${qi}][text]`" x-model="q.text" placeholder="{{ __('Question text') }}" required>
            <div class="space-y-2">
                <template x-for="(opt, oi) in q.options" :key="oi">
                    <div class="flex items-center gap-2">
                        <input type="radio" :name="`questions[${qi}][correct]`" :value="oi" x-model.number="q.correct" class="accent-green-600" title="{{ __('Mark as correct answer') }}">
                        <input type="text" class="input" :name="`questions[${qi}][options][${oi}]`" x-model="q.options[oi]" :placeholder="'{{ __('Option') }} ' + (oi + 1)" required>
                        <button type="button" class="btn-danger" x-show="q.options.length > 2" @click="q.options.splice(oi, 1); if (q.correct >= q.options.length) q.correct = 0">×</button>
                    </div>
                </template>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ __('Select the radio next to the correct answer.') }}</span>
                <button type="button" class="btn-secondary" @click="q.options.push('')">{{ __('+ Add Option') }}</button>
            </div>
        </div>
    </template>
    <button type="button" class="btn-secondary" @click="questions.push({ text: '', options: ['', ''], correct: 0 })">{{ __('+ Add Question') }}</button>
</div>
