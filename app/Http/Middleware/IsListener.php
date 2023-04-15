<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IsListener
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response|RedirectResponse) $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if ($request->user->type !== 'listener') {
            return response()->json([
                'message' => 'Unauthorized.',
            ])->setStatusCode(403);
        }
        $request->merge(['user' => $request->user]);

        return $next($request);
    }
}
