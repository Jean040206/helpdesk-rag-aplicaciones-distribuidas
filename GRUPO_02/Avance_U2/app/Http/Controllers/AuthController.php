<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function create(): View
    {
        $usuariosDemo = app()->environment(['local', 'testing'])
            ? Usuario::query()
                ->with('rol')
                ->where('activo', true)
                ->orderBy('id_rol')
                ->orderBy('nombre_completo')
                ->get()
            : collect();

        return view('auth.login', compact('usuariosDemo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'string', 'email', 'max:120'],
                'password' => ['required', 'string', 'max:255'],
            ],
            [
                'email.required' => 'Ingrese su correo institucional.',
                'email.email' => 'El correo institucional no tiene un formato válido.',
                'email.max' => 'El correo institucional no puede superar los 120 caracteres.',
                'password.required' => 'Ingrese su contraseña.',
                'password.max' => 'La contraseña no puede superar los 255 caracteres.',
            ]
        );

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Auditoria::registrar(
                'Autenticación',
                'LOGIN_BLOQUEADO',
                'Acceso bloqueado por límite de intentos para: '.$credentials['email']
            );

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos fallidos. Intente nuevamente en {$seconds} segundos.",
            ]);
        }

        $authenticated = Auth::attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'activo' => true,
            ],
            $request->boolean('remember')
        );

        if (! $authenticated) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            Auditoria::registrar(
                'Autenticación',
                'LOGIN_FALLIDO',
                'Intento fallido de inicio de sesión para: '.$credentials['email']
            );

            throw ValidationException::withMessages([
                'email' => 'El correo institucional o la contraseña son incorrectos, o la cuenta está inactiva.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        Auditoria::registrar(
            'Autenticación',
            'LOGIN_EXITOSO',
            'Ingreso al sistema: '.$credentials['email'],
            Auth::id()
        );

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        Auditoria::registrar(
            'Autenticación',
            'LOGOUT',
            'Cierre de sesión: '.$usuario->email,
            $usuario->id_usuario
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Sesión cerrada correctamente.');
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower((string) $request->input('email')).'|'.$request->ip()
        );
    }
}
