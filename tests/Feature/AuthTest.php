<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_with_phone(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'phone' => '01111111111',
            'governorate' => 'Cairo',
            'grade_level' => '1st_secondary',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'phone' => '01111111111',
            'role' => 'student',
            'governorate' => 'Cairo',
            'grade_level' => '1st_secondary',
        ]);
    }

    public function test_phone_must_be_unique(): void
    {
        User::factory()->create(['phone' => '01111111111']);

        $this->post('/register', [
            'name' => 'Duplicate',
            'phone' => '01111111111',
            'governorate' => 'Giza',
            'grade_level' => '1st_bac',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('phone');
    }

    public function test_student_can_login_with_phone(): void
    {
        User::factory()->create(['phone' => '01222222222']);

        $this->post('/login', [
            'phone' => '01222222222',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', [
            'phone' => $admin->phone,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', [
            'phone' => $user->phone,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_blocked_student_cannot_login(): void
    {
        $user = User::factory()->create();
        $user->blockLogin();

        $this->from('/login')->post('/login', [
            'phone' => $user->phone,
            'password' => 'password',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'login_blocked_at' => $user->fresh()->login_blocked_at,
        ]);
    }

    public function test_unblocking_lifts_the_login_block(): void
    {
        $user = User::factory()->create();
        $user->blockLogin();

        $this->assertTrue($user->fresh()->isLoginBlocked());

        $user->unblockLogin();

        $this->assertFalse($user->fresh()->isLoginBlocked());
        $this->assertNull($user->fresh()->login_blocked_at);
    }

    public function test_admin_can_never_be_login_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $admin->forceFill(['login_blocked_at' => now()])->save();

        $this->assertFalse($admin->fresh()->isLoginBlocked());
    }

    public function test_student_can_login_from_a_fourth_device_without_limit(): void
    {
        $user = User::factory()->create();

        // Simulate the account already holding three registered devices.
        for ($i = 0; $i < 3; $i++) {
            $user->devices()->create([
                'device_uuid' => (string) Str::uuid(),
                'device_name' => 'Mozilla/5.0',
                'last_seen_ip' => '127.0.0.1',
            ]);
        }

        $this->post('/login', [
            'phone' => $user->phone,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertSame(4, $user->devices()->count());
    }

    public function test_admin_can_block_and_unblock_a_student(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.students.login.block', $student))
            ->assertRedirect();

        $this->assertNotNull($student->fresh()->login_blocked_at);

        $this->actingAs($admin)
            ->post(route('admin.students.login.unblock', $student))
            ->assertRedirect();

        $this->assertNull($student->fresh()->login_blocked_at);
    }
}
