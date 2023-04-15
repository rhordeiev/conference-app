<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsUnauthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            return response()->json([
                'message' => 'Forbidden.',
            ])->setStatusCode(403);
        }

        return $next($request);
    }
}
