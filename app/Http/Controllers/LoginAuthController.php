<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAuthController extends Controller
{
    // ─── Show Login Pages ─────────────────────────────────────────────────────

    public function getLoginAdmin()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }
        if (Auth::guard('employee')->check()) {
            return redirect()->route('drive');
        }
        return view('login');
    }

    public function getLogin()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }
        if (Auth::guard('employee')->check()) {
            return redirect()->route('drive');
        }
        return view('login');   // single unified login page
    }

    // ─── Handle Login ─────────────────────────────────────────────────────────

    public function postLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        // ── 1. Try web (User) guard with email ───────────────────────────────
        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();

            // Payroll Administrator → redirect to external payroll system
            if ($user->isPayrollAdmin()) {
                Auth::guard('web')->logout();
                return redirect("https://hris.cpsu.edu.ph/pms/hr-payroll-login/{$user->email}/{$request->password}");
            }

            return redirect()->route('dashboard')->with('success', 'Login successful.');
        }

        // ── 2. Try employee guard using org_email ────────────────────────────
        $employee = Employee::where('org_email', $request->email)
                            ->where('stat_1', 1)
                            ->first();

        if ($employee) {
            if (Auth::guard('employee')->attempt([
                'org_email' => $request->email,
                'password'  => $request->password,
            ])) {
                return redirect()->route('empPDS')->with('success', 'Login successful.');
            }

            return redirect()->back()->with('error', 'Invalid credentials.');
        }

        return redirect()->back()->with('error', 'Invalid credentials. Please check your email and password.');
    }
}
