<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SingleLoginManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SingleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_session_is_rejected_after_a_new_login_replaces_it(): void
    {
        $user = User::factory()->create();
        $activeLoginId = (string) Str::uuid();
        $user->forceFill(['active_login_id' => $activeLoginId])->save();

        $this->actingAs($user)
            ->withSession(['active_login_id' => (string) Str::uuid()])
            ->get('/profile')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');
    }

    public function test_api_token_is_rejected_after_a_new_login_replaces_it(): void
    {
        $user = User::factory()->create();
        $activeLoginId = (string) Str::uuid();
        $user->forceFill(['active_login_id' => $activeLoginId])->save();
        $oldToken = $user->createToken('old-device', [
            '*',
            SingleLoginManager::TOKEN_ABILITY_PREFIX.(string) Str::uuid(),
        ])->plainTextToken;

        $this->withToken($oldToken)
            ->getJson('/api/v1/student/profile')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Akun ini telah login di perangkat lain. Silakan login kembali.');
    }

    public function test_student_api_login_replaces_the_previous_active_login(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'active_login_id' => (string) Str::uuid(),
        ]);
        $oldToken = $user->createToken('old-device')->plainTextToken;

        $response = $this->postJson('/api/v1/student/login', [
            'username' => $user->username,
            'password' => 'password',
        ])->assertOk();

        $this->assertNotSame($oldToken, $response->json('data.token'));
        $this->assertSame(1, $user->tokens()->count());
        $this->assertNotNull($user->fresh()->active_login_id);

        $this->withToken($oldToken)
            ->getJson('/api/v1/student/profile')
            ->assertUnauthorized();
    }

    public function test_web_login_invalidates_an_existing_api_token(): void
    {
        $user = User::factory()->create();
        $user->createToken('old-device');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_web_login_can_replace_a_stale_authenticated_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->withSession([SingleLoginManager::SESSION_KEY => (string) Str::uuid()])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(
            $user->fresh()->active_login_id,
            session(SingleLoginManager::SESSION_KEY)
        );
    }

    public function test_api_login_can_replace_a_stale_bearer_token(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'active_login_id' => (string) Str::uuid(),
        ]);
        $oldToken = $user->createToken('old-device')->plainTextToken;

        $response = $this->withToken($oldToken)
            ->postJson('/api/v1/student/login', [
                'username' => $user->username,
                'password' => 'password',
            ])
            ->assertOk();

        $this->assertNotSame($oldToken, $response->json('data.token'));
        $this->assertSame(1, $user->tokens()->count());
        $this->assertNotNull($user->fresh()->active_login_id);
    }
}
