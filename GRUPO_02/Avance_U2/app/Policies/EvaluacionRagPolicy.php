<?php

namespace App\Policies;

use App\Models\EvaluacionRag;
use App\Models\Usuario;

class EvaluacionRagPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor');
    }

    public function view(Usuario $usuario, EvaluacionRag $evaluacion): bool
    {
        if ($usuario->hasRole('Administrador', 'Supervisor')) {
            return true;
        }

        return $usuario->hasRole('Tecnico')
            && (int) $evaluacion->ticket?->id_tecnico_asignado === (int) $usuario->id_usuario;
    }
}
