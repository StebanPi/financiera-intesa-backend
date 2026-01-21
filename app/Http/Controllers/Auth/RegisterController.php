<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    /**
     * Show the application registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol: si no existe ningún super-admin, este usuario será super-admin
        // De lo contrario, asignar rol secretaria por defecto
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $hasSuperAdmin = User::whereHas('roles', function ($query) {
            $query->where('slug', 'super-admin');
        })->exists();

        if (!$hasSuperAdmin && $superAdminRole) {
            $user->assignRole('super-admin');
        } else {
            // Por defecto: secretaria
            $secretariaRole = Role::where('slug', 'secretaria')->first();
            if ($secretariaRole) {
                $user->assignRole('secretaria');
            }
        }

        Auth::login($user);

        return redirect($this->redirectPath());
    }

    /**
     * Get the post-register redirect path.
     */
    protected function redirectPath(): string
    {
        return $this->redirectTo;
    }
}
