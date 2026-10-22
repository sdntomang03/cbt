<?php

namespace App\Http\Middleware;

use App\Services\SingleLoginManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $isApi = $request->is('api/*');
        $isLoginRequest = $request->isMethod('post')
            && in_array($request->path(), [
                'login',
                'api/login',
                'api/v1/student/login',
            ], true);
        $accessToken = null;

        if ($isApi && $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());

            if (! $accessToken) {
                return $next($request);
            }

            $user = $accessToken->tokenable;
            if (! $user) {
                return $next($request);
            }

            $user->withAccessToken($accessToken);
        } else {
            $user = $request->user('sanctum');
            $accessToken = $user?->currentAccessToken();

            if ($isApi && ! $request->hasSession() && ! $accessToken instanceof PersonalAccessToken) {
                return $next($request);
            }
        }

        if (! $user) {
            return $next($request);
        }

        $activeLoginId = $user->active_login_id;

        if ($accessToken instanceof PersonalAccessToken) {
            $validLogin = $activeLoginId !== null
                && in_array(
                    SingleLoginManager::TOKEN_ABILITY_PREFIX.$activeLoginId,
                    $accessToken->abilities ?? [],
                    true
                );
        } else {
            $validLogin = $activeLoginId !== null
                && $request->hasSession()
                && $request->session()->get(SingleLoginManager::SESSION_KEY) === $activeLoginId;
        }

        if ($validLogin) {
            return $next($request);
        }

        if ($accessToken instanceof PersonalAccessToken) {
            $accessToken->delete();
        } elseif ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($isLoginRequest) {
            return $next($request);
        }

        if ($request->expectsJson() || $isApi) {
            return response()->json([
                'message' => 'Akun ini telah login di perangkat lain. Silakan login kembali.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()->route('login')->withErrors([
            'login' => 'Akun ini telah login di perangkat lain. Silakan login kembali.',
        ]);
    }
}
