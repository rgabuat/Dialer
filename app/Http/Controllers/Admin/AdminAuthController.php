<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\AgentStatusService;
use App\Models\AgentStatusType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            ActivityLogger::warning(
                type: 'security',
                event: 'admin_login_failed',
                action: 'Failed admin login attempt',
                actor: null,
                subject: null,
                properties: ['email' => $request->input('email')]
            );

            return back()
                ->withErrors(['email' => 'Invalid credentials.'])
                ->withInput($request->only('email'));
        }

        if (! auth()->user()->hasRole('Super Admin')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'You do not have admin access.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        ActivityLogger::info(
            type: 'security',
            event: 'admin_login_successful',
            action: 'Admin logged in',
            actor: auth()->user(),
            subject: auth()->user()
        );

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        ActivityLogger::info(
            type: 'security',
            event: 'admin_logout',
            action: 'Admin logged out',
            actor: auth()->user(),
            subject: auth()->user()
        );

        $offlineStatus = AgentStatusType::where('slug', 'offline')->first();
        if ($offlineStatus) {
            AgentStatusService::change(auth()->user(), $offlineStatus->id);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
