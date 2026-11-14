<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenteRagFuente extends Model
{
    protected $table = 'asistente_rag_fuentes';

    protected $primaryKey = 'id_fuente';

    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion_rag',
        'id_articulo',
        'posicion',
        'score',
        'fragmento',
    ];

    protected $casts = [
        'posicion' => 'integer',
        'score' => 'float',
        'fecha_recuperacion' => 'datetime',
    ];

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionRag::class, 'id_evaluacion_rag', 'id_evaluacion_rag');
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(BaseConocimiento::class, 'id_articulo', 'id_articulo');
    }
}
