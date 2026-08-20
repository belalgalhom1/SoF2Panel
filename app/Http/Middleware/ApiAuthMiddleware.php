<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Auth;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $hashedToken = hash('sha256', $token);
        $apiKey = ApiKey::where('token', $hashedToken)->first();

        if (!$apiKey) {
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        if ($apiKey->user->status === false) {
            return response()->json(['error' => 'Your account is disabled.'], 403);
        }

        Auth::loginUsingId($apiKey->user_id);
        
        $apiKey->update(['last_used_at' => now()]);

        return $next($request);
    }
}
