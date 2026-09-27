<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_registration_requires_email_verification(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test Student',
            'username' => 'test-student',
            'email' => 'student@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticated();

        $user = User::where('email', 'student@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_student_api_registration_sends_verification_and_does_not_issue_a_token(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/student/register', [
            'name' => 'API Student',
            'username' => 'api-student',
            'email' => 'api-student@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email_verified', false)
            ->assertJsonMissingPath('data.token');

        $user = User::where('email', 'api-student@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertCount(0, $user->tokens);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_student_api_login_is_blocked_until_email_is_verified(): void
    {
        Role::findOrCreate('siswa', 'web');
        $user = User::factory()->create([
            'username' => 'pending-student',
            'email' => 'pending@example.com',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => null,
        ]);
        $user->assignRole('siswa');

        $this->postJson('/api/v1/student/login', [
            'username' => 'pending-student',
            'password' => 'Password123!',
        ])->assertForbidden()
            ->assertJsonPath('data.email_verified', false);

        $user->markEmailAsVerified();

        $this->postJson('/api/v1/student/login', [
            'username' => 'pending-student',
            'password' => 'Password123!',
        ])->assertOk()
            ->assertJsonStructure(['data' => ['token']]);
    }
}
