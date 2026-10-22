<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\SingleLoginManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, SingleLoginManager $singleLoginManager): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $singleLoginManager->startWebSession(Auth::guard('web')->user(), $request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, SingleLoginManager $singleLoginManager): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $loginId = $request->session()->get(SingleLoginManager::SESSION_KEY);

        Auth::guard('web')->logout();
        $singleLoginManager->endLogin($user, $loginId);

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
