<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prioridad extends Model
{
    protected $table = 'prioridades';

    protected $primaryKey = 'id_prioridad';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'nivel_orden',
        'sla_horas',
        'color_badge',
    ];

    protected $casts = [
        'nivel_orden' => 'integer',
        'sla_horas' => 'integer',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_prioridad', 'id_prioridad');
    }
}
