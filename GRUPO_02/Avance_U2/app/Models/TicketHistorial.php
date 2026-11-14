<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistorial extends Model
{
    protected $table = 'ticket_historial';

    protected $primaryKey = 'id_historial';

    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'id_estado_anterior',
        'id_estado_nuevo',
        'id_usuario_responsable',
        'observacion',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function estadoAnterior(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado_anterior', 'id_estado');
    }

    public function estadoNuevo(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado_nuevo', 'id_estado');
    }

    public function usuarioResponsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_responsable', 'id_usuario');
    }
}
