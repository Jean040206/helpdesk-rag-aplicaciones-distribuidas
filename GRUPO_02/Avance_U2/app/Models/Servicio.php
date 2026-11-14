<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $primaryKey = 'id_servicio';

    public $timestamps = false;

    protected $fillable = [
        'codigo_servicio',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function categorias(): HasMany
    {
        return $this->hasMany(Categoria::class, 'id_servicio_default', 'id_servicio');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_servicio', 'id_servicio');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(BaseConocimiento::class, 'id_servicio', 'id_servicio');
    }
}
