<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreLogInRequest;
use App\Http\Requests\StoreSignUpRequest;
use App\Models\Settings;
use App\Services\PhoneVerificationService;
use App\Services\UserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected UserRegistrationService $userRegistrationService,
    ) {
    }

    // ////////////
    // pages
    public function login()
    {
        return view('auth.login');
    }

    public function signup()
    {
        $is_phone_verification_enabled = Settings::shouldPhoneVerify();
        return view('auth.register', compact('is_phone_verification_enabled'));
    }

    // ////////////
    // Logic
    public function authenticate(StoreLogInRequest $request)
    {
        $attributes = $request->validated();
        $remember = $request->boolean('remember');
        $identifier = mb_strtolower(trim((string) $attributes['email']));

        $authenticated = Auth::attempt([
            'email' => $identifier,
            'password' => $attributes['password'],
        ], $remember);

        if (!$authenticated) {
            $authenticated = Auth::attempt([
                'nickname' => $identifier,
                'password' => $attributes['password'],
            ], $remember);
        }

        if (!$authenticated) {
            return back()->withInput()->with(['error' => 'ავტორიზაციის მონაცემები არასწორია.']);
        }

        // Deny admin login
        if (Auth::user()->role === 'admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput()
                ->withErrors(['error' => 'ავტორიზაციის მონაცემები არასწორია.']);
        }

        $request->session()->regenerate();
        return redirect('/')->with('success', 'ავტორიზაცია წარმატებით დასრულდა!');
    }

    public function register(StoreSignUpRequest $request)
    {
        $attrs = $request->validated();
        $user = $this->userRegistrationService->register($attrs);

        Auth::login($user);
        $request->session()->regenerate();


        $is_email_verification_enabled = Settings::shouldEmailVerify();
        $is_phone_verification_enabled = Settings::shouldPhoneVerify();

        $messages = [
            'success' => 'რეგისტრაცია წარმატებით დასრულდა!',
        ];

        $redirectToVerification = false;

        if ($is_email_verification_enabled) {
            $redirectToVerification = true;
            try {
                $user->sendEmailVerificationNotification();
                $messages['info'] = 'ვერიფიკაციის ბმული წარმატებით გაიგზავნა ელ.ფოსტაზე';
                $request->session()->put('email_verification_sent', true);
            } catch (\Throwable $e) {
                $messages['warning'] = 'წარმოიქმნა ხარვეზი გთხოვთ მოგვიანებით სცადეთ, ან დაგვიკავშირდით.';
            }
        } elseif ($is_phone_verification_enabled) {
            $redirectToVerification = true;
            $result = app(PhoneVerificationService::class)->sendCode($request, $user);
            if ($result['ok']) {
                $messages['info'] = 'კოდი წარმატებით გაიგზავნა ტელეფონზე.';
            } else {
                $messages['warning'] = $result['message'];
            }
        }

        if ($redirectToVerification) {
            return redirect()->route('profile.verification')->with($messages);
        }

        return redirect('/')->with($messages);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
