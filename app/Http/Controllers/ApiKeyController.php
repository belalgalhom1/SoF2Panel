<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index()
    {
        if (auth()->user()->isAdmin()) {
            $keys = ApiKey::with('user')->latest()->get();
        } else {
            $keys = auth()->user()->apiKeys()->latest()->get();
        }
        
        if (auth()->user()->isAdmin()) {
            $logs = \App\Models\Log::with('user')
                ->where('action', 'like', 'API:%')
                ->latest()
                ->limit(50)
                ->get();
        } else {
            $logs = \App\Models\Log::with('user')
                ->where('user_id', auth()->id())
                ->where('action', 'like', 'API:%')
                ->latest()
                ->limit(50)
                ->get();
        }
            
        return view('api.index', compact('keys', 'logs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = Str::random(60);

        ApiKey::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'token' => hash('sha256', $token),
        ]);

        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'action' => 'Generated API Key',
            'target' => $request->name,
            'ip' => request()->ip()
        ]);

        return back()->with('new_token', $token);
    }

    public function destroy(ApiKey $apiKey)
    {
        if ($apiKey->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $keyName = $apiKey->name;
        $apiKey->delete();

        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'action' => 'Deleted API Key',
            'target' => $keyName,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'API Key deleted successfully.');
    }
}
