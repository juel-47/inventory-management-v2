<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\TwoFactorService;
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
    public function store(LoginRequest $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Check if user is Admin
        if (!$user->hasRole('Admin')) {
            Auth::logout();
            return redirect('/')->with('error', 'Only admins can login via this portal.');
        }

        $intended = $request->session()->get('url.intended');
        if (! is_string($intended) || ! str_contains($intended, '/admin')) {
            $intended = route('admin.dashboard');
        }

        $twoFactorService->send($user);
        $request->session()->put('two_factor_user_id', $user->id);
        $request->session()->put('two_factor_remember', $request->boolean('remember'));
        $request->session()->put('two_factor_intended', $intended);

        Auth::logout();

        return redirect()->route('admin.two-factor.challenge');

        //without 2FA
        
        // $request->session()->regenerate();
        // return redirect()->route('admin.dashboard');

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
