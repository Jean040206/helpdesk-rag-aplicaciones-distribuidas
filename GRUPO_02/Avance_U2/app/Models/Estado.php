<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estado extends Model
{
    protected $table = 'estados';

    protected $primaryKey = 'id_estado';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_cerrado',
        'color_badge',
    ];

    protected $casts = [
        'es_cerrado' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_estado', 'id_estado');
    }

    public function historialAnterior(): HasMany
    {
        return $this->hasMany(TicketHistorial::class, 'id_estado_anterior', 'id_estado');
    }

    public function historialNuevo(): HasMany
    {
        return $this->hasMany(TicketHistorial::class, 'id_estado_nuevo', 'id_estado');
    }
}
