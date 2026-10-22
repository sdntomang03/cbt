<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SingleLoginManager
{
    public const SESSION_KEY = 'active_login_id';

    public const TOKEN_ABILITY_PREFIX = 'active-login:';

    public function startWebSession(User $user, Request $request): void
    {
        DB::transaction(function () use ($user, $request): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $loginId = (string) Str::uuid();

            $lockedUser->forceFill(['active_login_id' => $loginId])->save();
            $lockedUser->tokens()->delete();
            $request->session()->put(self::SESSION_KEY, $loginId);
        });
    }

    public function createApiToken(User $user, string $tokenName): string
    {
        return DB::transaction(function () use ($user, $tokenName): string {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $loginId = (string) Str::uuid();

            $lockedUser->forceFill(['active_login_id' => $loginId])->save();
            $lockedUser->tokens()->delete();

            return $lockedUser->createToken($tokenName, [
                '*',
                self::TOKEN_ABILITY_PREFIX.$loginId,
            ])->plainTextToken;
        });
    }

    public function endLogin(User $user, ?string $loginId): void
    {
        if ($loginId === null) {
            return;
        }

        User::query()
            ->whereKey($user->getKey())
            ->where('active_login_id', $loginId)
            ->update(['active_login_id' => null]);
    }
}
