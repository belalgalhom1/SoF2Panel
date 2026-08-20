<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:0,1',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status == '1',
        ]);

        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'action' => 'Create Global User',
            'target' => $request->username,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function searchExternal(Request $request)
    {
        if (!\App\Models\Setting::get('external_auth_enabled', false)) {
            return response()->json([]);
        }

        $query = $request->input('q', '');
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $externalAuth = app(\App\Services\ExternalAuthService::class);
        $results = $externalAuth->search($query);

        return response()->json($results);
    }

    public function importExternal(Request $request)
    {
        $request->validate([
            'external_username' => 'required|string|max:255',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:0,1',
        ]);

        $externalAuth = app(\App\Services\ExternalAuthService::class);
        $externalUser = $externalAuth->lookup($request->external_username);

        if (!$externalUser) {
            return back()->with('error', 'Could not find that user in the external database.');
        }

        $exists = User::where('email', $externalUser->email)->orWhere('username', $externalUser->username)->first();
        if ($exists) {
            return back()->with('error', 'That user already exists in the panel database.');
        }

        User::create([
            'username' => $externalUser->username,
            'email' => $externalUser->email,
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'role' => $request->role,
            'status' => $request->status == '1',
            'is_external' => true,
        ]);

        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'action' => 'Import External User',
            'target' => $externalUser->username,
            'ip' => request()->ip()
        ]);

        return back()->with('success', "Imported {$externalUser->username} from the external database successfully.");
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if ($request->has('role') && $user->id !== auth()->id()) {
            $request->validate([
                'role' => 'required|in:admin,user',
                'status' => 'required|in:0,1',
            ]);
            
            $user->role = $request->role;
            $user->status = $request->status == '1';
        }

        $user->update([
            'username' => $request->username,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'action' => 'Update Global User',
            'target' => $user->username,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        \App\Models\Log::create([
            'user_id' => auth()->id(),
            'action' => 'Delete Global User',
            'target' => $user->username,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'User deleted successfully.');
    }
}
