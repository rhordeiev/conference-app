<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class IsAdminOrCreatorConference
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
        $id = $request->route('id')
            ? $request->route('id')
            : $request->get(
                'id'
            );

        if (($user->type !== 'admin'
                && ($user->type === 'announcer'
                    && ! $user->hasConference($id)))
            && $request->user->type !== 'admin'
        ) {
            return response()->json([
                'message' => 'Unauthorized.',
            ])->setStatusCode(403);
        }

        $request->merge(['user' => $user]);

        return $next($request);
    }
}
