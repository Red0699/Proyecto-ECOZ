<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1) Validación con mensajes en español
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingresa un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // 2) Rate limit (5 intentos por minuto por IP+email)
        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'auth' => "Demasiados intentos. Inténtalo de nuevo en {$seconds} segundos.",
            ])->status(429);
        }

        $remember = $request->boolean('remember');

        // 3) Intento de autenticación con "remember"
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            // 3.a) (Opcional) Si tienes un flag 'is_active' en users
            if (property_exists(Auth::user(), 'is_active') && !Auth::user()->is_active) {
                $this->forceLogout($request);
                throw ValidationException::withMessages([
                    'auth' => 'Tu usuario está inactivo. Contacta al administrador.',
                ]);
            }

            // 3.c) Redirección intended segura
            return redirect()->intended(route('inicio'));
        }

        // Si falla: sumar intento y devolver error genérico
        RateLimiter::hit($throttleKey, 60); // decae en 60s
        throw ValidationException::withMessages([
            'email' => 'Las credenciales no son correctas.',
        ])->redirectTo(route('login'))->errorBag('default')->status(422);
    }

    public function logout(Request $request)
    {
        $this->forceLogout($request);
        return redirect('/login')->with('status', 'Sesión cerrada correctamente.');
    }

    private function forceLogout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
