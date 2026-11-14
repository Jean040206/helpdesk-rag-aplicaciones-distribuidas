<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    public $timestamps = false;

    protected $fillable = [
        'id_servicio_default',
        'nombre',
        'descripcion',
    ];

    public function servicioDefault(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio_default', 'id_servicio');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_categoria', 'id_categoria');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(BaseConocimiento::class, 'id_categoria', 'id_categoria');
    }
}
