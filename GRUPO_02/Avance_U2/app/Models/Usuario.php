<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'codigo_institucional',
        'nombre_completo',
        'email',
        'password_hash',
        'dependencia_oficina',
        'especialidad_ti',
        'activo',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->rol?->nombre, $roles, true);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function ticketsSolicitados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_solicitante', 'id_usuario');
    }

    public function ticketsAsignados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_tecnico_asignado', 'id_usuario');
    }

    public function ticketsSupervisados(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_supervisor_asignador', 'id_usuario');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(TicketSeguimiento::class, 'id_usuario', 'id_usuario');
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(BaseConocimiento::class, 'id_autor', 'id_usuario');
    }

    public function evaluacionesRag(): HasMany
    {
        return $this->hasMany(EvaluacionRag::class, 'id_tecnico_evaluador', 'id_usuario');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id_usuario');
    }
}
