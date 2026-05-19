<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->getEmail();

            // ── 1. Try web (User) guard ───────────────────────────────────────
            $user = User::where('email', $email)->first();

            if ($user) {
                Auth::guard('web')->login($user);
                return redirect()->route('dashboard')->with('success', 'Welcome, ' . $user->fname . '!');
            }

            // ── 2. Try employee guard ─────────────────────────────────────────
            $employee = Employee::where('org_email', $email)->first();

            if ($employee) {
                if ($employee->stat_1 != 1) {
                    return redirect()->route('getLogin')->with('error', 'Your account has been suspended.');
                }

                Auth::guard('employee')->login($employee);
                return redirect()->route('empPDS')->with('success', 'Welcome, ' . $employee->fname . '!');
            }

            return redirect()->route('getLogin')
                ->with('error', 'No account found for this Google email. Please contact HR.');

        } catch (\Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());
            return redirect()->route('getLogin')->with('error', 'Google sign-in failed. Please try again.');
        }
    }

    // ── Legacy stubs kept for route backward-compatibility ────────────────────

    public function verifyForm(Request $request)
    {
        return redirect()->route('getLogin');
    }

    public function verify(Request $request)
    {
        return redirect()->route('getLogin');
    }
}
