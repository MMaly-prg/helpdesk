<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Brak uprawnień.'], 403);
            }
            abort(403, 'Brak uprawnień do tego zasobu.');
        }

        return $next($request);
    }
}
