<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    public function create(Lesson $lesson)
    {
        return view('admin.quizzes.create', [
            'lesson' => $lesson,
            'quiz' => new Quiz,
        ]);
    }

    public function store(Request $request, Lesson $lesson)
    {
        $data = $this->validateQuiz($request);

        $quiz = $lesson->quizzes()->create([
            'title' => $this->translationsFrom($data, 'title'),
            'description' => $this->translationsFrom($data, 'description'),
            'pass_score' => $data['pass_score'],
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'max_attempts' => $data['max_attempts'] ?? null,
        ]);

        $this->syncQuestions($quiz, $data['questions']);

        return redirect()->route('admin.lessons.edit', $lesson)->with('status', __('Quiz created.'));
    }

    public function edit(Quiz $quiz)
    {
        $quiz->load('lesson', 'questions');

        return view('admin.quizzes.edit', [
            'quiz' => $quiz,
            'stats' => $quiz->stats(),
            'recentAttempts' => $quiz->attempts()->with('user')->whereNotNull('completed_at')->latest()->limit(10)->get(),
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $data = $this->validateQuiz($request);

        $quiz->update([
            'title' => $this->translationsFrom($data, 'title'),
            'description' => $this->translationsFrom($data, 'description'),
            'pass_score' => $data['pass_score'],
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'max_attempts' => $data['max_attempts'] ?? null,
        ]);

        $this->syncQuestions($quiz, $data['questions']);

        return redirect()->route('admin.lessons.edit', $quiz->lesson)->with('status', __('Quiz updated.'));
    }

    public function destroy(Quiz $quiz)
    {
        $lesson = $quiz->lesson;
        $quiz->delete();

        return redirect()->route('admin.lessons.edit', $lesson)->with('status', __('Quiz deleted.'));
    }

    protected function validateQuiz(Request $request): array
    {
        $data = $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'pass_score' => ['required', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:4'],
            'questions.*.options.*' => ['required', 'string', 'max:255'],
            'questions.*.correct' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['questions'] as $i => $question) {
            if ((int) $question['correct'] >= count($question['options'])) {
                throw ValidationException::withMessages([
                    "questions.$i.correct" => __('The correct option is out of range.'),
                ]);
            }
        }

        return $data;
    }

    /**
     * Turn flat "<column>_ar"/"<column>_en" request data into a translatable
     * {ar, en} array, removing the flat keys from the payload.
     */
    protected function translationsFrom(array &$data, string $column): array
    {
        $translations = [
            'ar' => $data["{$column}_ar"] ?? '',
            'en' => $data["{$column}_en"] ?? '',
        ];

        unset($data["{$column}_ar"], $data["{$column}_en"]);

        return $translations;
    }

    protected function syncQuestions(Quiz $quiz, array $questions): void
    {
        $quiz->questions()->delete();

        foreach (array_values($questions) as $i => $question) {
            $quiz->questions()->create([
                'question_text' => $question['text'],
                'options' => array_values($question['options']),
                'correct_option' => (int) $question['correct'],
                'sort_order' => $i,
            ]);
        }
    }
}
