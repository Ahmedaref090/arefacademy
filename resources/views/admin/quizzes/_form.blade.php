@php
    $initialQuestions = old('questions') ?? (isset($quiz) && $quiz->exists
        ? $quiz->questions->map(fn ($q) => ['text' => $q->question_text, 'options' => array_values($q->options), 'correct' => $q->correct_option])->values()->all()
        : [['text' => '', 'options' => ['', ''], 'correct' => 0]]);
@endphp

<div class="card space-y-4">
    <div>
        <label class="label" for="title">Quiz Title</label>
        <input class="input" id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
    </div>
    <div>
        <label class="label" for="description">Description</label>
        <textarea class="input" id="description" name="description" rows="2">{{ old('description', $quiz->description) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="label" for="pass_score">Pass Score (%)</label>
            <input class="input" id="pass_score" name="pass_score" type="number" min="0" max="100" value="{{ old('pass_score', $quiz->pass_score ?? 50) }}" required>
        </div>
        <div>
            <label class="label" for="time_limit_minutes">Time Limit (minutes, optional)</label>
            <input class="input" id="time_limit_minutes" name="time_limit_minutes" type="number" min="1" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}">
        </div>
    </div>
</div>

<div x-data='{ questions: @json($initialQuestions) }' class="space-y-4">
    <template x-for="(q, qi) in questions" :key="qi">
        <div class="card space-y-3">
            <div class="flex items-center justify-between">
                <span class="font-semibold" x-text="'Question ' + (qi + 1)"></span>
                <button type="button" class="btn-danger" x-show="questions.length > 1" @click="questions.splice(qi, 1)">Remove</button>
            </div>
            <input type="text" class="input" :name="`questions[${qi}][text]`" x-model="q.text" placeholder="Question text" required>
            <div class="space-y-2">
                <template x-for="(opt, oi) in q.options" :key="oi">
                    <div class="flex items-center gap-2">
                        <input type="radio" :name="`questions[${qi}][correct]`" :value="oi" x-model.number="q.correct" class="accent-green-600" title="Mark as correct answer">
                        <input type="text" class="input" :name="`questions[${qi}][options][${oi}]`" x-model="q.options[oi]" :placeholder="`Option ${oi + 1}`" required>
                        <button type="button" class="btn-danger" x-show="q.options.length > 2" @click="q.options.splice(oi, 1); if (q.correct >= q.options.length) q.correct = 0">×</button>
                    </div>
                </template>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">Select the radio next to the correct answer.</span>
                <button type="button" class="btn-secondary" @click="q.options.push('')">+ Add Option</button>
            </div>
        </div>
    </template>
    <button type="button" class="btn-secondary" @click="questions.push({ text: '', options: ['', ''], correct: 0 })">+ Add Question</button>
</div>
