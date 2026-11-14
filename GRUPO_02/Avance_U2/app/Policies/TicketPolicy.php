<?php

namespace App\Policies;

use App\Models\Estado;
use App\Models\Ticket;
use App\Models\Usuario;

class TicketPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function view(Usuario $usuario, Ticket $ticket): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor')
            || (int) $ticket->id_solicitante === (int) $usuario->id_usuario
            || (int) $ticket->id_tecnico_asignado === (int) $usuario->id_usuario;
    }

    public function create(Usuario $usuario): bool
    {
        return true;
    }

    public function update(Usuario $usuario, Ticket $ticket): bool
    {
        if ($usuario->hasRole('Administrador')) {
            return true;
        }

        return (int) $ticket->id_solicitante === (int) $usuario->id_usuario
            && (int) $ticket->id_estado === Ticket::ESTADO_ABIERTO;
    }

    public function delete(Usuario $usuario, Ticket $ticket): bool
    {
        return $usuario->hasRole('Administrador');
    }

    public function assign(Usuario $usuario, Ticket $ticket): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor')
            && ! in_array((int) $ticket->id_estado, [
                Ticket::ESTADO_RESUELTO,
                Ticket::ESTADO_CERRADO,
            ], true);
    }

    public function updateStatus(Usuario $usuario, Ticket $ticket, Estado $nuevoEstado): bool
    {
        $estadoActual = (int) $ticket->id_estado;
        $estadoNuevo = (int) $nuevoEstado->id_estado;

        if ($estadoActual === $estadoNuevo) {
            return false;
        }

        if ($usuario->hasRole('Administrador', 'Supervisor')) {
            if ($estadoActual === Ticket::ESTADO_CERRADO) {
                return false;
            }

            if ($estadoNuevo === Ticket::ESTADO_ABIERTO) {
                return false;
            }

            if (in_array($estadoNuevo, [
                Ticket::ESTADO_ASIGNADO,
                Ticket::ESTADO_EN_PROCESO,
                Ticket::ESTADO_RESUELTO,
                Ticket::ESTADO_CERRADO,
            ], true) && ! $ticket->id_tecnico_asignado) {
                return false;
            }

            return true;
        }

        if ($usuario->hasRole('Tecnico')) {
            if ((int) $ticket->id_tecnico_asignado !== (int) $usuario->id_usuario) {
                return false;
            }

            return ($estadoActual === Ticket::ESTADO_ASIGNADO && $estadoNuevo === Ticket::ESTADO_EN_PROCESO)
                || ($estadoActual === Ticket::ESTADO_EN_PROCESO && $estadoNuevo === Ticket::ESTADO_RESUELTO);
        }

        if ($usuario->hasRole('Solicitante')) {
            return (int) $ticket->id_solicitante === (int) $usuario->id_usuario
                && $estadoActual === Ticket::ESTADO_RESUELTO
                && $estadoNuevo === Ticket::ESTADO_CERRADO;
        }

        return false;
    }

    public function comment(Usuario $usuario, Ticket $ticket): bool
    {
        if ((int) $ticket->id_estado === Ticket::ESTADO_CERRADO) {
            return false;
        }

        return $usuario->hasRole('Administrador', 'Supervisor')
            || (int) $ticket->id_solicitante === (int) $usuario->id_usuario
            || (int) $ticket->id_tecnico_asignado === (int) $usuario->id_usuario;
    }

    public function viewRag(Usuario $usuario, Ticket $ticket): bool
    {
        return $usuario->hasRole('Administrador', 'Supervisor')
            || (int) $ticket->id_tecnico_asignado === (int) $usuario->id_usuario;
    }

    public function generateRag(Usuario $usuario, Ticket $ticket): bool
    {
        return $this->viewRag($usuario, $ticket)
            && (int) $ticket->id_estado !== Ticket::ESTADO_CERRADO;
    }

    public function decideRag(Usuario $usuario, Ticket $ticket): bool
    {
        return $this->generateRag($usuario, $ticket);
    }
}
