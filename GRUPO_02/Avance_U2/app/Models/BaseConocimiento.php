<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaseConocimiento extends Model
{
    public const TIPOS_DOCUMENTO = [
        'Manual' => 'Manual',
        'Procedimiento' => 'Procedimiento',
        'FAQ' => 'FAQ',
        'Solucion_Anterior' => 'Solución anterior',
        'Doc_Tecnica' => 'Documentación técnica',
    ];

    protected $table = 'base_conocimiento';

    protected $primaryKey = 'id_articulo';

    public $timestamps = false;

    protected $fillable = [
        'codigo_articulo',
        'id_servicio',
        'id_categoria',
        'id_autor',
        'tipo_documento',
        'titulo',
        'sintomas_problema',
        'pasos_solucion',
        'palabras_clave',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_publicacion' => 'datetime',
    ];

    public function getTipoDocumentoLabelAttribute(): string
    {
        return self::TIPOS_DOCUMENTO[$this->tipo_documento] ?? $this->tipo_documento;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        $texto = trim((string) $texto);

        if ($texto === '') {
            return $query;
        }

        $terminos = collect(preg_split('/\s+/u', $texto))
            ->map(fn (string $termino) => preg_replace('/[+\-<>()~*"@]+/u', '', $termino))
            ->filter(fn (?string $termino) => $termino !== null && mb_strlen($termino) >= 3)
            ->map(fn (string $termino) => $termino.'*')
            ->implode(' ');

        if ($terminos === '') {
            $coincidencia = '%'.$texto.'%';

            return $query->where(function (Builder $subquery) use ($coincidencia): void {
                $subquery->where('titulo', 'like', $coincidencia)
                    ->orWhere('palabras_clave', 'like', $coincidencia);
            });
        }

        return $query->whereFullText(
            ['titulo', 'sintomas_problema', 'pasos_solucion', 'palabras_clave'],
            $terminos,
            ['mode' => 'boolean']
        );
    }

    public function getRouteKeyName(): string
    {
        return 'id_articulo';
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_autor', 'id_usuario');
    }

    public function evaluacionesRag(): HasMany
    {
        return $this->hasMany(EvaluacionRag::class, 'id_articulo_recuperado', 'id_articulo');
    }

    public function fuentesRag(): HasMany
    {
        return $this->hasMany(AsistenteRagFuente::class, 'id_articulo', 'id_articulo');
    }
}
