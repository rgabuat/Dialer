<?php

namespace App\Http\Controllers\Livewire\Auth;

use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use App\Services\AuditLogService;
use App\Services\AgentStatusService;
use App\Models\AgentStatusType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('livewire.auth.login');
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
                event: 'login_failed',
                action: 'Failed login attempt',
                actor: null,
                subject: null,
                properties: [
                    // NEVER log passwords
                    'email' => $request->input('email'),
                ]
            );

            return back()
                ->withErrors([
                    'email' => __('Invalid credentials.'),
                ])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        ActivityLogger::info(
            type: 'security',
            event: 'login_successful',
            action: 'User logged in',
            actor: auth()->user(),
            subject: auth()->user()
        );

        return redirect()->route('campaign.select');
    }

    public function logout(Request $request)
    {
        ActivityLogger::info(
            type: 'security',
            event: 'logout',
            action: 'User logged out',
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
        // active_campaign_id is cleared with the session above

        return redirect('/login');
    }


}
