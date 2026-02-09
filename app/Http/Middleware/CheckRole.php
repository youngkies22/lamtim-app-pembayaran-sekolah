<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelper;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::error('Unauthorized', 401);
            }
            return redirect('/login');
        }

        // Check if user role is in allowed roles
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::error('Anda tidak memiliki akses untuk melakukan aksi ini', 403);
            }
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini');
        }

        return $next($request);
    }
}
