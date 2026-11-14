<?php

namespace App\Http\Middleware;

use App\Models\Auditoria;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! $usuario->hasRole(...$roles)) {
            Auditoria::registrar(
                'Seguridad',
                'ACCESO_DENEGADO',
                'Intento de acceso sin autorización a "'.$request->path().'". Roles requeridos: '.implode(', ', $roles),
                $usuario->id_usuario
            );

            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
