<?php

namespace App\Providers;

use App\Models\Topic;
use App\Policies\TopicPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Json::encodeUsing(fn($value) => json_encode($value, JSON_UNESCAPED_UNICODE));
        Gate::policy(Topic::class, TopicPolicy::class);
        $chatSendPerMinute = max(1, (int) config('chat.rate_limits.send_per_minute', 10));
        $chatLikePerMinute = max(1, (int) config('chat.rate_limits.like_per_minute', 60));
        $chatDeletePerMinute = max(1, (int) config('chat.rate_limits.delete_per_minute', 30));

        /*
        |--------------------------------------------------------------------------
        | Rate Limiting (Auth & Verification)
        |--------------------------------------------------------------------------
        */

        // Login: 5 attempts/min per email+IP, 30/min per IP
        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by($email . '|' . $ip),
                Limit::perMinute(30)->by($ip),
            ];
        });

        // Register: limit account creation abuse
        RateLimiter::for('register', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(3)->by($ip),
                Limit::perHour(10)->by($ip),
            ];
        });

        // Email verification resend: 6/min per user
        RateLimiter::for('email-resend', function (Request $request) {
            $key = ($request->user()?->id ?? 'guest') . '|' . $request->ip();

            return Limit::perMinute(6)->by($key);
        });

        // Email verification link hits
        RateLimiter::for('email-verify', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // OTP send (SMS): strict to prevent abuse/cost
        RateLimiter::for('otp-send', function (Request $request) {
            $userKey = $request->user()?->id
                ? 'u:' . $request->user()->id
                : 'g:' . $request->ip();

            return [
                Limit::perMinute(2)->by($userKey),
                Limit::perHour(10)->by($userKey),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        // OTP verify: brute-force protection
        RateLimiter::for('otp-verify', function (Request $request) {
            $userKey = $request->user()?->id
                ? 'u:' . $request->user()->id
                : 'g:' . $request->ip();

            return [
                Limit::perMinute(5)->by($userKey),
                Limit::perMinute(30)->by($request->ip()),
            ];
        });

        // Chat send: topic/private message creation
        RateLimiter::for('chat-send', function (Request $request) use ($chatSendPerMinute) {
            $userKey = $request->user()?->id
                ? 'u:' . $request->user()->id
                : 'g:' . $request->ip();

            return [
                Limit::perMinute($chatSendPerMinute)->by('chat-send:' . $userKey),
                Limit::perMinute(120)->by('chat-send-ip:' . $request->ip()),
            ];
        });

        // Chat like/unlike reactions
        RateLimiter::for('chat-like', function (Request $request) use ($chatLikePerMinute) {
            $userKey = $request->user()?->id
                ? 'u:' . $request->user()->id
                : 'g:' . $request->ip();

            return [
                Limit::perMinute($chatLikePerMinute)->by('chat-like:' . $userKey),
                Limit::perMinute(300)->by('chat-like-ip:' . $request->ip()),
            ];
        });

        // Chat deletes (optional harder guard)
        RateLimiter::for('chat-delete', function (Request $request) use ($chatDeletePerMinute) {
            $userKey = $request->user()?->id
                ? 'u:' . $request->user()->id
                : 'g:' . $request->ip();

            return [
                Limit::perMinute($chatDeletePerMinute)->by('chat-delete:' . $userKey),
                Limit::perMinute(120)->by('chat-delete-ip:' . $request->ip()),
            ];
        });
    }
}
