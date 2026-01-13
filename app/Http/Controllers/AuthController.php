<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreLogInRequest;
use App\Http\Requests\StoreSignUpRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function signup()
    {
        return view('auth.register');
    }


    public function authenticate(StoreLogInRequest $request)
    {
        $attributes = $request->validated();

        $user = User::where('email', '=', $attributes['email'])->first();

        if (!$user || !Hash::check($attributes['password'], $user->password)) {
            return back()
                ->withInput()
                ->with(['error' => 'ავტორიზაციის მონაცემები არასწორია.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        return redirect('/')->with('success', 'ავტორიზაცია წარმატებით დასრულდა!');
    }

    public function register(StoreSignUpRequest $request)
    {
        $attrs = $request->validated();

        $user = User::create([
            'name' => $attrs['name'],
            'surname' => $attrs['surname'],
            'email' => $attrs['email'],
            'phone' => $attrs['phone'],
            'password' => Hash::make($attrs['password']),
        ]);


        Auth::login($user);

        return redirect('/')->with('success', 'რეგისტრაცია წარმატებით დასრულდა!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
