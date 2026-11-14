<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseConocimientoRequest;
use App\Models\Auditoria;
use App\Models\BaseConocimiento;
use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BaseConocimientoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BaseConocimiento::class);

        $usuario = $request->user();
        $puedeGestionar = $usuario->can('create', BaseConocimiento::class);

        $filtroActivo = (string) $request->input('activo', '1');
        if (! $puedeGestionar) {
            $filtroActivo = '1';
        }

        $texto = trim((string) $request->input('q'));
        if (mb_strlen($texto) > 100) {
            $texto = mb_substr($texto, 0, 100);
        }

        $articulos = BaseConocimiento::query()
            ->with(['servicio', 'categoria', 'autor'])
            ->buscar($texto)
            ->when($request->filled('id_servicio'), fn ($query) => $query->where('id_servicio', $request->integer('id_servicio')))
            ->when($request->filled('id_categoria'), fn ($query) => $query->where('id_categoria', $request->integer('id_categoria')))
            ->when($request->filled('tipo_documento'), fn ($query) => $query->where('tipo_documento', $request->input('tipo_documento')))
            ->when($filtroActivo === '1', fn ($query) => $query->where('activo', true))
            ->when($filtroActivo === '0', fn ($query) => $query->where('activo', false))
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_articulo')
            ->paginate(10)
            ->withQueryString();

        return view('kb.index', [
            'articulos' => $articulos,
            'servicios' => Servicio::orderBy('nombre')->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tiposDocumento' => BaseConocimiento::TIPOS_DOCUMENTO,
            'puedeGestionar' => $puedeGestionar,
            'texto' => $texto,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BaseConocimiento::class);

        return view('kb.create', $this->catalogos());
    }

    public function store(BaseConocimientoRequest $request): RedirectResponse
    {
        $this->authorize('create', BaseConocimiento::class);

        $datos = $request->validated();

        $articulo = BaseConocimiento::create([
            'codigo_articulo' => $this->generarCodigo(),
            'id_servicio' => $datos['id_servicio'],
            'id_categoria' => $datos['id_categoria'],
            'id_autor' => $request->user()->id_usuario,
            'tipo_documento' => $datos['tipo_documento'],
            'titulo' => $datos['titulo'],
            'sintomas_problema' => $datos['sintomas_problema'],
            'pasos_solucion' => $datos['pasos_solucion'],
            'palabras_clave' => $datos['palabras_clave'],
            'activo' => (bool) ($datos['activo'] ?? false),
        ]);

        Auditoria::registrar(
            'Base de conocimiento',
            'CREAR_ARTICULO_KB',
            'Se creó el artículo '.$articulo->codigo_articulo.' — '.$articulo->titulo,
            $request->user()->id_usuario
        );

        return redirect()
            ->route('kb.show', $articulo)
            ->with('status', 'Artículo de conocimiento creado correctamente.');
    }

    public function show(BaseConocimiento $articulo): View
    {
        $this->authorize('view', $articulo);

        $articulo->load(['servicio', 'categoria', 'autor']);

        return view('kb.show', [
            'articulo' => $articulo,
            'puedeGestionar' => auth()->user()->can('update', $articulo),
        ]);
    }

    public function edit(BaseConocimiento $articulo): View
    {
        $this->authorize('update', $articulo);

        return view('kb.edit', array_merge(
            ['articulo' => $articulo],
            $this->catalogos()
        ));
    }

    public function update(BaseConocimientoRequest $request, BaseConocimiento $articulo): RedirectResponse
    {
        $this->authorize('update', $articulo);

        $datos = $request->validated();

        $articulo->update([
            'id_servicio' => $datos['id_servicio'],
            'id_categoria' => $datos['id_categoria'],
            'tipo_documento' => $datos['tipo_documento'],
            'titulo' => $datos['titulo'],
            'sintomas_problema' => $datos['sintomas_problema'],
            'pasos_solucion' => $datos['pasos_solucion'],
            'palabras_clave' => $datos['palabras_clave'],
            'activo' => (bool) ($datos['activo'] ?? false),
        ]);

        Auditoria::registrar(
            'Base de conocimiento',
            'ACTUALIZAR_ARTICULO_KB',
            'Se actualizó el artículo '.$articulo->codigo_articulo.' — '.$articulo->titulo,
            $request->user()->id_usuario
        );

        return redirect()
            ->route('kb.show', $articulo)
            ->with('status', 'Artículo de conocimiento actualizado correctamente.');
    }

    public function destroy(Request $request, BaseConocimiento $articulo): RedirectResponse
    {
        $this->authorize('delete', $articulo);

        $codigo = $articulo->codigo_articulo;

        if ($articulo->evaluacionesRag()->exists() || $articulo->fuentesRag()->exists()) {
            return redirect()
                ->route('kb.show', $articulo)
                ->with('error', 'El artículo tiene evaluaciones RAG asociadas. Desactívelo en lugar de eliminarlo.');
        }

        $articulo->delete();

        Auditoria::registrar(
            'Base de conocimiento',
            'ELIMINAR_ARTICULO_KB',
            'Se eliminó el artículo '.$codigo,
            $request->user()->id_usuario
        );

        return redirect()
            ->route('kb.index')
            ->with('status', 'Artículo de conocimiento eliminado correctamente.');
    }

    private function catalogos(): array
    {
        return [
            'servicios' => Servicio::query()
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
            'categorias' => Categoria::query()
                ->orderBy('nombre')
                ->get(),
            'tiposDocumento' => BaseConocimiento::TIPOS_DOCUMENTO,
        ];
    }

    private function generarCodigo(): string
    {
        $prefijo = 'KB-'.now()->year.'-';
        $secuencia = 1;

        do {
            $codigo = sprintf('%s%03d', $prefijo, $secuencia);
            $secuencia++;
        } while (BaseConocimiento::where('codigo_articulo', $codigo)->exists());

        return $codigo;
    }
}
