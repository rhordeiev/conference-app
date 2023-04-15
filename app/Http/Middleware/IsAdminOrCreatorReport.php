<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class IsAdminOrCreatorReport
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = User::findUserByEmail($request->user->email);
        $conferenceId = $request->conferenceId;

        if (($user->type !== 'admin'
                && ($user->type === 'listener'
                    && ! $user->hasReport($conferenceId)))
        ) {
            return response()->json([
                'message' => 'Unauthorized.',
            ])->setStatusCode(403);
        }

        $request->merge(['user' => $user]);
        $request->merge(['conference_id' => $request->conferenceId]);

        return $next($request);
    }
}
