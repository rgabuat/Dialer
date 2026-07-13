<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Conversation;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'      => User::count(),
            'total_campaigns'  => Campaign::count(),
            'active_campaigns' => Campaign::where('is_active', true)->count(),
            'total_calls'      => Conversation::where('channel', 'voice')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
