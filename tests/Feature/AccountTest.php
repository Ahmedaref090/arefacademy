<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function enrolledStudent(): array
    {
        $student = User::factory()->create();
        $course = Course::factory()->create();

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
        ]);

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        return [$student, $course, $lesson];
    }

    public function test_account_pages_require_authentication(): void
    {
        $this->get(route('account.security'))->assertRedirect(route('login'));
        $this->get(route('account.exams'))->assertRedirect(route('login'));
        $this->get(route('account.assignments'))->assertRedirect(route('login'));
        $this->get(route('account.videos'))->assertRedirect(route('login'));
    }

    public function test_exam_results_page_lists_quiz_attempts(): void
    {
        [$student, $course, $lesson] = $this->enrolledStudent();

        $quiz = Quiz::factory()->create(['lesson_id' => $lesson->id, 'title' => 'PHP Basics Exam']);
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => '2 + 2 = ?',
            'options' => ['3', '4'],
            'correct_option' => 1,
        ]);

        $this->actingAs($student)->post(route('quizzes.submit', $quiz), [
            'answers' => [$question->id => 1],
        ]);

        $this->actingAs($student)
            ->get(route('account.exams'))
            ->assertOk()
            ->assertSee('PHP Basics Exam')
            ->assertSee('Passed');
    }

    public function test_assignment_results_page_lists_submissions(): void
    {
        [$student, $course, $lesson] = $this->enrolledStudent();

        $assignment = Assignment::create([
            'lesson_id' => $lesson->id,
            'title' => 'Build a CLI app',
            'max_score' => 100,
        ]);

        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'code' => '<?php echo "hi";',
            'score' => 85,
            'feedback' => 'Great work!',
            'graded_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('account.assignments'))
            ->assertOk()
            ->assertSee('Build a CLI app')
            ->assertSee('85/100')
            ->assertSee('Great work!');
    }

    public function test_video_views_page_lists_watch_history(): void
    {
        [$student, $course, $lesson] = $this->enrolledStudent();

        $lesson->update(['title' => 'Intro to Variables']);
        $student->recordWatchTime($lesson, 300);

        $this->actingAs($student)
            ->get(route('account.videos'))
            ->assertOk()
            ->assertSee('Intro to Variables')
            ->assertSee('5 min');
    }

    public function test_login_is_recorded_in_history(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('login_histories', [
            'user_id' => $user->id,
        ]);
    }

    public function test_security_page_shows_login_history(): void
    {
        $user = User::factory()->create();

        $user->loginHistories()->create([
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
        ]);

        $this->actingAs($user)
            ->get(route('account.security'))
            ->assertOk()
            ->assertSee('Windows')
            ->assertSee('Chrome')
            ->assertSee('127.0.0.1');
    }

    public function test_student_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('account.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertSessionHasErrors('current_password');
    }
}
