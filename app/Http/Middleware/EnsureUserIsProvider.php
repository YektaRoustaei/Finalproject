<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsProvider
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user() instanceof \App\Models\Seeker) {

            return $next($request);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
