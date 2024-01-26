<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Closure|JsonResponse
    {
        return auth()->check() ? $next($request) : response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
    }
}
