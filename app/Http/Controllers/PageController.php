<?php

namespace App\Http\Controllers;

use App\Models\PhoneVerificationOtp;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function profile()
    {
        return redirect()->route('profile.user-info');
    }


    public function profileVerification(Request $request)
    {
        $user = Auth::user();

        if ($user->shouldVerify() === false) {
            $referer = $request->headers->get('referer');
            return $referer
                ? redirect()->back()
                : redirect()->route('profile.user-info');
        }
        // Email status
        $email_verified = method_exists($user, 'hasVerifiedEmail')
            ? $user->hasVerifiedEmail()
            : !is_null($user->email_verified_at);
        $email_pending = session('email_verification_sent', false);
        $is_email_verification_enabled = Settings::shouldEmailVerify();

        // Phone status (adjust field names to your DB)
        $phone_verified = !is_null($user->phone_verified_at ?? null);
        $phone_pending = PhoneVerificationOtp::activeFor($user->id, 'phone')->exists();
        $phone_expired = PhoneVerificationOtp::expiredFor($user->id, 'phone')->exists();

        $is_phone_verification_enabled = Settings::shouldPhoneVerify();

        // InvalidSignatureException is thrown from InvalidSignatureException handler in app.php
        $email_expired = (bool) session('verification_expired');

        // Forget email verification sent flag if verification link is expired
        if ($email_expired) {
            $request->session()->forget('email_verification_sent');
            $email_pending = false;
        }

        return view('profile.verification', [
            'user' => $user,

            'email_verified' => $email_verified,
            'email_pending' => $email_pending,
            'email_expired' => $email_expired,
            'is_email_verification_enabled' => $is_email_verification_enabled,

            'phone_verified' => $phone_verified,
            'phone_pending' => $phone_pending,
            'phone_expired' => $phone_expired,
            'is_phone_verification_enabled' => $is_phone_verification_enabled,
        ]);
    }
}
