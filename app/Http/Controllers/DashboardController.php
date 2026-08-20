<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $serversCount = \App\Models\Server::count();
            $hostsCount = \App\Models\Host::count();
            $usersCount = \App\Models\User::count();
            $logs = \App\Models\Log::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        } else {
            $serversCount = $user->servers()->count();
            $hostsCount = 0;
            $usersCount = 0;
            $logs = \App\Models\Log::with('user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->take(5)->get();
        }

        return view('dashboard.index', compact('serversCount', 'hostsCount', 'usersCount', 'logs'));
    }
}
