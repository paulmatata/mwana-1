<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage in routes: ->middleware('role:super_admin') or ->middleware('role:principal,teacher')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'You are not authorized to access this section of Mwana.');
        }

        if ($user->status === 'suspended') {
            abort(403, 'Your account has been suspended. Contact your school administrator.');
        }

        return $next($request);
    }
}
