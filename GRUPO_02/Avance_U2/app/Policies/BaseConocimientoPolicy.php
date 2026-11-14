<?php

namespace App\Policies;

use App\Models\BaseConocimiento;
use App\Models\Usuario;

class BaseConocimientoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor', 'Tecnico');
    }

    public function view(Usuario $usuario, BaseConocimiento $articulo): bool
    {
        if (! $this->viewAny($usuario)) {
            return false;
        }

        return $articulo->activo
            || $usuario->hasRole('Administrador', 'Supervisor');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor');
    }

    public function update(Usuario $usuario, BaseConocimiento $articulo): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor');
    }

    public function delete(Usuario $usuario, BaseConocimiento $articulo): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor');
    }
}
