<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    /**
     * Show the password confirmation view.
     */
    public function showConfirmForm()
    {
        return view('auth.passwords.confirm');
    }

    /**
     * Confirm the user's password.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function confirm(Request $request)
    {
        if (! Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [__('auth.password')],
            ]);
        }

        $request->session()->passwordConfirmed();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the post-confirm redirect path.
     */
    protected function redirectPath(): string
    {
        return $this->redirectTo;
    }
}
