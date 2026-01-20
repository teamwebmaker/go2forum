<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = mb_strtolower(trim((string) $request->email), 'UTF-8');

        $request->merge([
            'email' => $email,
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_THROTTLED) {
            return back()->with('warning', 'გთხოვთ სცადოთ მოგვიანებით.');
        }

        return back()->with('success', 'თუ ასეთი ანგარიში არსებობს, ელ.ფოსტაზე მიიღებთ პაროლის აღდგენის ბმულს.');
    }

    public function showResetForm(Request $request, string $token)
    {
        $email = (string) $request->query('email', '');

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->with('error', 'აღდგენის ბმული არასწორია ან ვადაგასულია.');
        }

        /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
        $broker = Password::broker();
        $user = $broker->getUser(['email' => $email]);
        $validToken = $user && $broker->getRepository()->exists($user, $token);



        if (!$validToken) {
            return redirect()
                ->route('password.request')
                ->with('error', 'აღდგენის ბმული არასწორია ან ვადაგასულია.');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('login')
            ->with('success', 'პაროლი წარმატებით განახლდა. გთხოვთ შეხვიდეთ სისტემაში.');
    }
}
