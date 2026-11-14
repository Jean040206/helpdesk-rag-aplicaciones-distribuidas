<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluacionRag extends Model
{
    protected $table = 'asistente_rag_evaluaciones';

    protected $primaryKey = 'id_evaluacion_rag';

    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'id_articulo_recuperado',
        'id_tecnico_evaluador',
        'metodo_ia',
        'proveedor',
        'modelo_utilizado',
        'categoria_sugerida',
        'prioridad_sugerida',
        'servicio_sugerido',
        'posible_causa',
        'solucion_recomendada',
        'decision_tecnico',
        'solucion_final_tecnico',
        'tiempo_procesamiento_ms',
        'es_dato_sintetico',
        'prompt_enviado',
        'respuesta_cruda',
        'respuesta_estructurada',
        'estado',
        'error_mensaje',
        'tokens_entrada',
        'tokens_salida',
        'parametros',
        'confianza',
        'fecha_decision',
        'comentario_tecnico',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_evaluacion_rag';
    }

    protected $casts = [
        'respuesta_estructurada' => 'array',
        'parametros' => 'array',
        'es_dato_sintetico' => 'boolean',
        'confianza' => 'float',
        'tiempo_procesamiento_ms' => 'integer',
        'tokens_entrada' => 'integer',
        'tokens_salida' => 'integer',
        'fecha_evento' => 'datetime',
        'fecha_decision' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id_ticket');
    }

    public function articuloRecuperado(): BelongsTo
    {
        return $this->belongsTo(BaseConocimiento::class, 'id_articulo_recuperado', 'id_articulo');
    }

    public function tecnicoEvaluador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_tecnico_evaluador', 'id_usuario');
    }

    public function fuentes(): HasMany
    {
        return $this->hasMany(AsistenteRagFuente::class, 'id_evaluacion_rag', 'id_evaluacion_rag')
            ->orderBy('posicion');
    }
}
