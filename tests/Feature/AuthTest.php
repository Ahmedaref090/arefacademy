<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
