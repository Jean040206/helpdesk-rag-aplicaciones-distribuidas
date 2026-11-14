<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    public const ESTADO_ABIERTO = 1;

    public const ESTADO_ASIGNADO = 2;

    public const ESTADO_EN_PROCESO = 3;

    public const ESTADO_RESUELTO = 4;

    public const ESTADO_CERRADO = 5;

    protected $table = 'tickets';

    protected $primaryKey = 'id_ticket';

    public $timestamps = false;

    protected $fillable = [
        'codigo_ticket',
        'id_solicitante',
        'id_tecnico_asignado',
        'id_supervisor_asignador',
        'id_servicio',
        'id_categoria',
        'id_prioridad',
        'id_estado',
        'asunto',
        'descripcion',
        'diagnostico_tecnico',
        'solucion_aplicada',
        'fecha_asignacion',
        'fecha_resolucion',
    ];

    protected $casts = [
        'id_estado' => 'integer',
        'fecha_registro' => 'datetime',
        'fecha_asignacion' => 'datetime',
        'fecha_resolucion' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_ticket';
    }

    public function scopeVisiblesPara(Builder $query, Usuario $usuario): Builder
    {
        if ($usuario->hasRole('Solicitante')) {
            return $query->where('id_solicitante', $usuario->id_usuario);
        }

        if ($usuario->hasRole('Tecnico')) {
            return $query->where('id_tecnico_asignado', $usuario->id_usuario);
        }

        return $query;
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_solicitante', 'id_usuario');
    }

    public function tecnicoAsignado(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_tecnico_asignado', 'id_usuario');
    }

    public function supervisorAsignador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_supervisor_asignador', 'id_usuario');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function prioridad(): BelongsTo
    {
        return $this->belongsTo(Prioridad::class, 'id_prioridad', 'id_prioridad');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(TicketEvidencia::class, 'id_ticket', 'id_ticket');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(TicketSeguimiento::class, 'id_ticket', 'id_ticket')
            ->orderBy('fecha_registro');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(TicketHistorial::class, 'id_ticket', 'id_ticket')
            ->orderBy('fecha_cambio');
    }

    public function evaluacionesRag(): HasMany
    {
        return $this->hasMany(EvaluacionRag::class, 'id_ticket', 'id_ticket')
            ->orderByDesc('fecha_evento');
    }

    public function ultimaEvaluacionRag(): HasOne
    {
        return $this->hasOne(EvaluacionRag::class, 'id_ticket', 'id_ticket')
            ->latestOfMany('id_evaluacion_rag');
    }
}
