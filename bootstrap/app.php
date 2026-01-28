<?php

use App\Http\Middleware\EnsureUserIsFullyVerified;
use App\Http\Middleware\RedirectAdminFromProfile;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
            'verified.full' => EnsureUserIsFullyVerified::class,
            'redirect.admin' => RedirectAdminFromProfile::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 401 - not authenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->view('errors.401', [], 401);
        });

        // 403 - authenticated, but forbidden
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            return response()->view('errors.403', [
                'message' => 'წვდომა შეზღუდულია, ამჟამად თქვენ არ გაქვთ წვდომა.',
            ], 403);
        });

        // 419 - CSRF/session expired
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch or session expired.'], 419);
            }

            return response()->view('errors.419', [
                'message' => 'სესიის ვადა ამოიწურა. განაახლეთ გვერდი და სცადეთ თავიდან.',
            ], 419);
        });

        // Expired/invalid signed URL
        $exceptions->render(function (InvalidSignatureException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid or expired link.'], 403);
            }

            return response()->view('errors.403', [
                'message' => 'ბმული ვადაგასულია ან არასწორია.',
            ], 403);
        });

        // 429 - too many requests
        $exceptions->render(function (TooManyRequestsHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Too many requests. Try again later.'], 429);
            }

            return response()->view('errors.429', [
                'message' => 'ცდების ლიმიტი ამოიწურა. სცადეთ მოგვიანებით.',
            ], 429);
        });

        // 503 - maintenance / unavailable
        $exceptions->render(function (ServiceUnavailableHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Service unavailable.'], 503);
            }

            return response()->view('errors.503', [], 503);
        });
    })->create();
