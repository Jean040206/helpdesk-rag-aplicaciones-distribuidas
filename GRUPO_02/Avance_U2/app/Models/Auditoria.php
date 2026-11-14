<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $primaryKey = 'id_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'modulo',
        'accion',
        'descripcion_evento',
        'ip_origen',
        'user_agent',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
    ];

    public static function registrar(
        string $modulo,
        string $accion,
        string $descripcion,
        ?int $idUsuario = null
    ): self {
        $userAgent = request()->userAgent();

        return self::create([
            'id_usuario' => $idUsuario ?? Auth::id(),
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion_evento' => $descripcion,
            'ip_origen' => request()->ip() ?? '127.0.0.1',
            'user_agent' => $userAgent ? Str::limit($userAgent, 255) : null,
        ]);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
