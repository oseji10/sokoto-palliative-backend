<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerifyJwtToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('access_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token'], 401);
        }


// Inside the handle() method:
try {
    $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

    // Example assumes `sub` holds the user ID
    $userId = $decoded->sub;
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // ✅ Manually log in the user for the current request
    Auth::login($user);

    // Optionally attach payload to request if needed
    $request->merge(['jwt_payload' => (array) $decoded]);

} catch (\Exception $e) {
    return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
}


        return $next($request);
    }
}
