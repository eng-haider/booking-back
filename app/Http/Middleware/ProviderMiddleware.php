<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProviderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($request->user()->role !== UserRole::OWNER) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Provider access required.',
            ], 403);
        }

        // Check if user has a provider
        if (!$request->user()->provider) {
            return response()->json([
                'success' => false,
                'message' => 'No provider account found. Please contact admin.',
            ], 403);
        }

        return $next($request);
    }
}
