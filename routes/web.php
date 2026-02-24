<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Api\MessageAttachmentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', [PageController::class, 'home'])->name('page.home');
Route::view('/terms', 'pages.terms')->name('page.terms');

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

    // Profile
    Route::get('/profile', [ProfileController::class, 'profile'])
        ->name('page.profile');

    Route::get('/profile/verification', [ProfileController::class, 'profileVerification'])
        ->name('profile.verification');

    // Keep user-info routes inaccessible for admins.
    Route::middleware('redirect.admin')->group(function () {
        Route::get('/profile/user-info', [ProfileController::class, 'show'])
            ->name('profile.user-info');

        Route::patch('/profile/user-info', [ProfileController::class, 'update'])
            ->name('profile.user-info.update');

        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password.update');
    });

    Route::get('/profile/badges', [ProfileController::class, 'profileBadges'])
        ->name('profile.badges');

    Route::get('/profile/messages', [ProfileController::class, 'profileMessages'])
        ->middleware('verified.full')
        ->name('profile.messages');

    Route::get('/profile/activity', [ProfileController::class, 'profileActivity'])->middleware(['verified.full'])->name('profile.activity');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Email 
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:email-resend')
        ->name('verification.send');

    Route::get('/email/verify', [VerificationController::class, 'notice'])
        ->name('verification.notice');


    // Phone 
    Route::post('/phone/verification-notification', [VerificationController::class, 'sendPhoneCode'])
        ->middleware('throttle:otp-send')
        ->name('verification.phone.send');

    Route::post('/phone/verify', [VerificationController::class, 'verifyPhoneCode'])
        ->middleware('throttle:otp-verify')
        ->name('verification.phone.verify');

    // Topics
    Route::post('/categories/{category}/topics', [TopicController::class, 'store'])
        ->middleware(['verified.full'])
        ->name('categories.topics.store');

});

// Topics
Route::get('/categories/{category}/topics', [TopicController::class, 'category'])->name('categories.topics');
Route::get('/topic/{topic:slug}', [TopicController::class, 'show'])->name('topics.show');

// Email
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed', 'throttle:email-verify'])
    ->name('verification.verify');

// Messaging (server-side JSON endpoints)
Route::middleware(['auth', 'verified.full'])->group(function () {
    Route::post('/topics/{topic}/messages', [MessageController::class, 'sendTopicMessage'])
        ->middleware('throttle:chat-send');
    Route::post('/users/{user}/messages', [MessageController::class, 'sendPrivateMessage'])
        ->middleware('throttle:chat-send');

    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'listConversationMessages']);

    Route::post('/messages/{message}/like', [MessageController::class, 'likeMessage'])
        ->middleware('throttle:chat-like');
    Route::delete('/messages/{message}/like', [MessageController::class, 'unlikeMessage'])
        ->middleware('throttle:chat-like');

    Route::delete('/messages/{message}', [MessageController::class, 'deleteMessage'])
        ->middleware('throttle:chat-delete');

    Route::post('/topics/{topic}/notifications', [NotificationController::class, 'updateTopic']);
});

// Notifications
Route::middleware('auth')->group(function () {
    Route::get('/messages/attachments/{attachment}', [MessageAttachmentController::class, 'download'])
        ->middleware('verified.full')
        ->name('messages.attachments.download');

    Route::delete('/notifications/history', [NotificationController::class, 'clearHistory']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications', [NotificationController::class, 'clearAll']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'visit'])
        ->name('notifications.visit');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});
