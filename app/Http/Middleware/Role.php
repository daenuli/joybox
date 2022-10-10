<?php

namespace App\Http\Middleware;

use Closure;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->role == 'librarian') {
            return $next($request);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Only librarian can access this route'
            ], 401);
        }
    }
}
