<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEvidencia extends Model
{
    protected $table = 'ticket_evidencias';

    protected $primaryKey = 'id_evidencia';

    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'nombre_original',
        'ruta_archivo',
        'extension',
        'tamano_kb',
    ];

    protected $casts = [
        'tamano_kb' => 'integer',
        'fecha_subida' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_evidencia';
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }
}
