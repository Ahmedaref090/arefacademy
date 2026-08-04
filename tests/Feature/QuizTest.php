<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    protected function enrolledQuiz(User $student): Quiz
    {
        $course = Course::factory()->create();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
        ]);

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        return Quiz::factory()->create(['lesson_id' => $lesson->id, 'pass_score' => 50]);
    }

    public function test_quiz_submission_is_scored_and_recorded(): void
    {
        $student = User::factory()->create();
        $quiz = $this->enrolledQuiz($student);

        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'options' => ['3', '4'],
            'correct_option' => 1,
        ]);
        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'PHP is a …',
            'options' => ['language', 'database'],
            'correct_option' => 0,
        ]);

        // One correct (q1 → option 1), one wrong (q2 → option 1).
        $this->actingAs($student)->post(route('quizzes.submit', $quiz), [
            'answers' => [$q1->id => 1, $q2->id => 1],
        ])->assertRedirect();

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 1,
            'total_questions' => 2,
            'passed' => false,
        ]);
    }

    public function test_perfect_score_passes_the_quiz(): void
    {
        $student = User::factory()->create();
        $quiz = $this->enrolledQuiz($student);

        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'options' => ['3', '4'],
            'correct_option' => 1,
        ]);

        $this->actingAs($student)->post(route('quizzes.submit', $quiz), [
            'answers' => [$q1->id => 1],
        ]);

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 1,
            'passed' => true,
        ]);
    }

    public function test_empty_submission_is_accepted_and_scores_zero(): void
    {
        $student = User::factory()->create();
        $quiz = $this->enrolledQuiz($student);

        Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'options' => ['3', '4'],
            'correct_option' => 1,
        ]);

        // Simulates the countdown timer auto-submitting an untouched form.
        $this->actingAs($student)->post(route('quizzes.submit', $quiz), []);

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'score' => 0,
            'passed' => false,
        ]);
    }

    public function test_student_can_review_their_own_attempt(): void
    {
        $student = User::factory()->create();
        $quiz = $this->enrolledQuiz($student);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'options' => ['3', '4'],
            'correct_option' => 1,
        ]);

        $this->actingAs($student)->post(route('quizzes.submit', $quiz), [
            'answers' => [$question->id => 1],
        ]);

        $attempt = QuizAttempt::where('user_id', $student->id)->first();

        $this->actingAs($student)
            ->get(route('quizzes.result', $attempt))
            ->assertOk()
            ->assertSee('2 + 2 = ?');
    }

    public function test_student_cannot_view_another_students_result(): void
    {
        $student = User::factory()->create();
        $other = User::factory()->create();
        $quiz = $this->enrolledQuiz($student);

        Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'options' => ['3', '4'],
            'correct_option' => 1,
        ]);

        $this->actingAs($student)->post(route('quizzes.submit', $quiz), []);

        $attempt = QuizAttempt::where('user_id', $student->id)->first();

        $this->actingAs($other)->get(route('quizzes.result', $attempt))->assertForbidden();
    }

    public function test_non_enrolled_student_cannot_submit(): void
    {
        $student = User::factory()->create();
        $quiz = Quiz::factory()->create(); // lesson + course created via factories

        $this->actingAs($student)->post(route('quizzes.submit', $quiz), [
            'answers' => [],
        ])->assertForbidden();
    }
}
