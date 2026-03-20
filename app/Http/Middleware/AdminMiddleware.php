<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isPrivileged()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Доступ запрещён'], 403);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
