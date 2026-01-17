<?php

use App\Http\Middleware\EnsureUserIsFullyVerified;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle Expired Email Verification link
        $exceptions->render(function (InvalidSignatureException $e) {
            return redirect()
                ->route('profile.verification')
                ->with(['verification_expired' => true, 'error' => 'ვერიფიკაციის ბმულის ვადაგასულია. გთხოვთ, გააგზავნოთ ახალი ბმული.']);
        });

        // Handle Too Many Attempt
        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return back()->with('warning', 'ცდების ლიმიტი ამოიწურა. სცადეთ მოგვიანებით.');
        });

    })->create();
