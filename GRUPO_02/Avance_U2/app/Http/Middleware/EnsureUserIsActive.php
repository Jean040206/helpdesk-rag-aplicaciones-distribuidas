<?php

namespace App\Http\Middleware;

use App\Models\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->activo) {
            Auditoria::registrar(
                'Seguridad',
                'SESION_INACTIVA',
                'Se cerró la sesión de una cuenta desactivada: '.$usuario->email,
                $usuario->id_usuario
            );

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'La cuenta fue desactivada. Contacte al administrador del sistema.',
                ]);
        }

        return $next($request);
    }
}
