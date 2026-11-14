<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSeguimiento extends Model
{
    protected $table = 'ticket_seguimiento';

    protected $primaryKey = 'id_seguimiento';

    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'id_usuario',
        'comentario',
        'tipo_registro',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
