<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
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
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Profile
    Route::get('/profile', [PageController::class, 'profile'])->name('page.profile');
    Route::get('/profile/user-info', [PageController::class, 'profileUserInfo'])->name('profile.user-info');
    Route::get('/profile/verification', [PageController::class, 'profileVerification'])->name('profile.verification');

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

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed', 'throttle:email-verify'])
    ->name('verification.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/verified-test', function () {
        return 'Verified user access granted.';
    })->name('test.verified');


});
