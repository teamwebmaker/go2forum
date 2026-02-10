<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsFullyVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->isVerified()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Verification is required.',
                ], 403);
            }

            return redirect()->route('profile.verification')
                ->with('info', 'გთხოვთ დაასრულოთ ვერიფიკაციის პროცესი.');
        }

        return $next($request);
    }
}
