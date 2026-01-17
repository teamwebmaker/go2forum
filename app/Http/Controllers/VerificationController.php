<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyPhoneCodeRequest;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings;
use App\Services\PhoneVerificationService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VerificationController extends Controller
{

    public function __construct(private PhoneVerificationService $phoneVerificationService)
    {
    }

    /**
     * Returns the verification notice view.
     * Just In case if we use verify middleware
     * We do this because verification handling is inside profile verification view
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function notice(): RedirectResponse
    {
        return redirect()->route('profile.verification');
    }



    /**
     * Verify the email address of the user.
     *
     * @param \Illuminate\Foundation\Auth\EmailVerificationRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        // Forget the email verification sent flag after successful verification
        $request->session()->forget('email_verification_sent');

        return redirect()->route('profile.verification')->with('success', 'თქვენი ემაილი ვერიფიკაციებულია.');
    }


    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        if (!Settings::shouldEmailVerify()) {
            return back()->with('error', 'ელ.ფოსტის ვერიფიკაცია წარუმატებელია.');
        }

        if (Auth::user()->hasVerifiedEmail()) {
            return back()->with('info', 'თქვენი ემაილი უკვე ვერიფიკაციებულია.');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            $request->session()->put('email_verification_sent', true);
        } catch (\Throwable $e) {
            return back()->with('warning', 'წარმოიქმნა ხარვეზი გთხოვთ მოგვიანებით სცადოთ, ან დაგვიკავშირდით.');
        }

        return back()->with('success', 'სავერიფიკაციო მესიჯი გაიგზავნა.');
    }



    /**
     * Send a phone verification code to the user.
     *
     * If phone verification is disabled, it will return an error message.
     * Keep in mind route have limit of 10 requests per minute
     * But each code have max 5 attempts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendPhoneCode(Request $request)
    {
        if (!Settings::shouldPhoneVerify()) {
            return back()->with('error', 'ვერიფიკაციის კოდის გაგზავნა ვერ მოხერხდა.');
        }

        $result = $this->phoneVerificationService->sendCode($request, $request->user());
        if (!$result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'კოდი წარმატებით გაიგზავნა ტელეფონზე.');
    }


    /**
     * Verify the phone verification code sent to the user.
     *
     * If phone verification is disabled, it will return an error message.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyPhoneCode(VerifyPhoneCodeRequest $request)
    {
        if (!Settings::shouldPhoneVerify()) {
            return back()->with('error', 'ტელეფონის ვერიფიკაცია გამორთულია.');
        }

        $result = $this->phoneVerificationService->verifyCode(
            $request,
            $request->user(),
            $request->validated('phone_code')
        );

        if (!$result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'ტელეფონის ვერიფიკაცია წარმატებით დასრულდა.');
    }
}


