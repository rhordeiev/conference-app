<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdminOrAnnouncer
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user->type !== 'announcer'
            && $request->user->type !== 'admin'
        ) {
            return response()->json([
                'message' => 'Unauthorized.',
            ])->setStatusCode(403);
        }
        $request->merge(['user' => $request->user]);

        return $next($request);
    }
}
