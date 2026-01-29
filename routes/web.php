<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', [PageController::class, 'home'])->name('page.home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'signup'])->name('register');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->middleware('throttle:login')
        ->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('auth.register');

    // Password reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'updatePassword'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Profile (redirect admins to /admin)
    Route::middleware('redirect.admin')->group(function () {
        Route::get('/profile', [PageController::class, 'profile'])
            ->name('page.profile');

        Route::get('/profile/verification', [PageController::class, 'profileVerification'])
            ->name('profile.verification');

        Route::get('/profile/user-info', [ProfileController::class, 'show'])
            ->name('profile.user-info');

        Route::get('/profile/badges', [PageController::class, 'profileBadges'])
            ->name('profile.badges');

        Route::patch('/profile/user-info', [ProfileController::class, 'update'])
            ->name('profile.user-info.update');

        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password.update');

        Route::delete('/profile', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');

    });

    // Email 
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:email-resend')
        ->name('verification.send');

    // Just In case if we use verify middleware
    Route::get('/email/verify', [VerificationController::class, 'notice'])
        ->name('verification.notice');


    // Phone 
    Route::post('/phone/verification-notification', [VerificationController::class, 'sendPhoneCode'])
        ->middleware('throttle:otp-send')
        ->name('verification.phone.send');

    Route::post('/phone/verify', [VerificationController::class, 'verifyPhoneCode'])
        ->middleware('throttle:otp-verify')
        ->name('verification.phone.verify');
});

// Topics
Route::get('/categories/{category}/topics', [TopicController::class, 'category'])->name('categories.topics');
// Topics
Route::get('/topic/{topic:slug}', [TopicController::class, 'show'])
    ->middleware('verified.full')->name('topics.show');

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed', 'throttle:email-verify'])
    ->name('verification.verify');