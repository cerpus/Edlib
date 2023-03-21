<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use function assert;
use function back;
use function is_array;
use function redirect;

final class LoginController extends Controller
{
    public function login(): View
    {
        return view('login.index');
    }

    public function check(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        assert(is_array($validated));

        if (Auth::attempt($validated)) {
            $request->session()->regenerate();

            return redirect()->intended();
        }

        return back()->withErrors([
            'email' => 'Something something credentials',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        return redirect()->route('home');
    }
}
