<?php

namespace App\Services\Rag;

use App\Models\BaseConocimiento;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KnowledgeRetriever
{
    public const MAX_FUENTES = 5;

    /**
     *
     * @var list<string>
     */
    private const COLUMNAS_FULLTEXT = [
        'titulo',
        'sintomas_problema',
        'pasos_solucion',
        'palabras_clave',
    ];

    /**
     *
     * @return Collection<int, BaseConocimiento>
     */
    public function recuperar(Ticket $ticket, int $limite = self::MAX_FUENTES): Collection
    {
        $ticket->loadMissing(['servicio', 'categoria']);

        $limite = max(1, min($limite, 10));
        $consulta = $this->textoConsulta($ticket);
        $terminos = $this->terminosBooleanos($consulta);

        $articulos = collect();

        if ($terminos !== '') {
            $articulos = $this->buscarFulltext($terminos, (int) $ticket->id_servicio, $limite);

            $restantes = $limite - $articulos->count();

            if ($restantes > 0) {
                $articulos = $articulos->merge($this->buscarFulltext(
                    $terminos,
                    null,
                    $restantes,
                    $articulos->pluck('id_articulo')->all()
                ));
            }
        }

        if ($articulos->count() < $limite) {
            $articulos = $articulos->merge($this->buscarPorCatalogo(
                $ticket,
                $limite - $articulos->count(),
                $articulos->pluck('id_articulo')->all()
            ));
        }

        return $articulos
            ->unique('id_articulo')
            ->take($limite)
            ->values();
    }

    public function textoConsulta(Ticket $ticket): string
    {
        return Str::limit(trim(implode(' ', array_filter([
            $ticket->asunto,
            $ticket->descripcion,
            $ticket->categoria?->nombre,
            $ticket->servicio?->nombre,
        ]))), 4000, '');
    }

    private function terminosBooleanos(string $texto): string
    {
        return collect(preg_split('/\s+/u', mb_strtolower($texto)) ?: [])
            ->map(fn (string $termino): ?string => preg_replace('/[+\-<>()~*"@,.;:!?¡¿]+/u', '', $termino))
            ->filter(fn (?string $termino): bool => is_string($termino) && mb_strlen($termino) >= 3)
            ->unique()
            ->take(15)
            ->implode(' ');
    }

    /**
     * @param  list<int>  $excluir
     * @return Collection<int, BaseConocimiento>
     */
    private function buscarFulltext(string $terminos, ?int $idServicio, int $limite, array $excluir = []): Collection
    {
        if ($limite <= 0) {
            return collect();
        }

        $columnas = implode(', ', self::COLUMNAS_FULLTEXT);

        return BaseConocimiento::query()
            ->activos()
            ->with(['servicio', 'categoria'])
            ->select('base_conocimiento.*')
            ->selectRaw(
                "MATCH({$columnas}) AGAINST (? IN BOOLEAN MODE) AS score",
                [$terminos]
            )
            ->whereFullText(self::COLUMNAS_FULLTEXT, $terminos, ['mode' => 'boolean'])
            ->when($idServicio, fn ($query) => $query->where('id_servicio', $idServicio))
            ->when($excluir !== [], fn ($query) => $query->whereNotIn('id_articulo', $excluir))
            ->orderByDesc('score')
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_articulo')
            ->limit($limite)
            ->get();
    }

    /**
     * @param  list<int>  $excluir
     * @return Collection<int, BaseConocimiento>
     */
    private function buscarPorCatalogo(Ticket $ticket, int $limite, array $excluir = []): Collection
    {
        if ($limite <= 0) {
            return collect();
        }

        $base = BaseConocimiento::query()
            ->activos()
            ->with(['servicio', 'categoria'])
            ->when($excluir !== [], fn ($query) => $query->whereNotIn('id_articulo', $excluir));

        $porCategoria = (clone $base)
            ->where('id_categoria', $ticket->id_categoria)
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_articulo')
            ->limit($limite)
            ->get();

        $restantes = $limite - $porCategoria->count();

        if ($restantes <= 0) {
            return $porCategoria;
        }

        $idsCategoria = $porCategoria->pluck('id_articulo')->all();

        $porServicio = (clone $base)
            ->where('id_servicio', $ticket->id_servicio)
            ->when($idsCategoria !== [], fn ($query) => $query->whereNotIn('id_articulo', $idsCategoria))
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_articulo')
            ->limit($restantes)
            ->get();

        return $porCategoria->merge($porServicio);
    }
}
