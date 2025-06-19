<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        // Define allowed origins
        $allowedOrigins = [
            'https://portal.sokotopalliativeshop.com.ng',
            'http://localhost:3000', // Add for local development if needed
        ];

        // Get the origin from the request
        $origin = $request->header('Origin');

        // Set the Access-Control-Allow-Origin header if the origin is allowed
        $response = $request->getMethod() === 'OPTIONS'
            ? response('', 200)
            : $next($request);

        if (in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->header('Access-Control-Max-Age', '1728000');
            $response->header('Access-Control-Allow-Credentials', 'true'); // Optional, for cookies or credentials
        }

        return $response;
    }
}