<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $login_type = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $login_type => $request->input('login'),
            'password' => $request->input('password')
        ];

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                return $this->handleSuccessfulLogin($request);
            }

            if (\App\Models\Setting::get('external_auth_enabled', false)) {
                $localUser = \App\Models\User::where(function ($q) use ($request) {
                    $q->where('email', $request->input('login'))->orWhere('username', $request->input('login'));
                })->where('is_external', true)->first();

                if ($localUser) {
                    $externalAuth = app(\App\Services\ExternalAuthService::class);
                    $externalUser = $externalAuth->attempt($localUser->username, $request->input('password')) 
                                 ?? $externalAuth->attempt($localUser->email, $request->input('password'));

                    if ($externalUser) {
                        $localUser->update([
                            'password' => \Illuminate\Support\Facades\Hash::make($request->input('password'))
                        ]);

                        Auth::login($localUser, $request->boolean('remember'));
                        return $this->handleSuccessfulLogin($request);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors(['login' => 'An internal system error occurred during login.']);
        }

        try {
            \App\Models\Log::create([
                'user_id' => null,
                'action' => 'Failed Login',
                'target' => $request->input('login'),
                'ip' => $request->ip()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to log failed login attempt: ' . $e->getMessage());
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    private function handleSuccessfulLogin(Request $request)
    {
        if (Auth::user()->status === false) {
            Auth::logout();
            return back()->withErrors(['login' => 'Your account is disabled.'])->onlyInput('login');
        }

        $request->session()->regenerate();
        
        \App\Models\Log::create([
            'user_id' => Auth::id(),
            'action' => 'Login',
            'target' => 'System',
            'ip' => $request->ip()
        ]);

        return redirect()->intended('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
