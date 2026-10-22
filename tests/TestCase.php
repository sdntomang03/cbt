<?php

namespace Tests;

use App\Models\User;
use App\Services\SingleLoginManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    public function actingAs($user, $guard = null): static
    {
        if ($user instanceof User) {
            $loginId = $this->app['session']->get(SingleLoginManager::SESSION_KEY)
                ?? (string) Str::uuid();
            $user->forceFill(['active_login_id' => $loginId])->save();
            $this->withSession(['active_login_id' => $loginId]);

            if ($guard === 'sanctum') {
                $token = $user->createToken('test-session', [
                    '*',
                    SingleLoginManager::TOKEN_ABILITY_PREFIX.$loginId,
                ]);
                $user->withAccessToken($token->accessToken);
            }
        }

        return parent::actingAs($user, $guard);
    }
}
