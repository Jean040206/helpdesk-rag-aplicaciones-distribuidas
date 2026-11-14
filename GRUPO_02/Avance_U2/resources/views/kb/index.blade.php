@extends('layouts.app')

@section('title', 'Base de conocimiento')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Base de conocimiento</h3>
        <p class="text-muted small mb-0">
            Procedimientos, manuales y soluciones reutilizables para la atención de incidencias.
        </p>
    </div>
    @if ($puedeGestionar)
        <a href="{{ route('kb.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo artículo
        </a>
    @endif
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('kb.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label for="q" class="form-label small fw-semibold mb-1">Buscar en la base de conocimiento</label>
                <input type="text" name="q" id="q" class="form-control form-control-sm"
                       value="{{ $texto }}" maxlength="100"
                       placeholder="Título, síntomas, solución o palabras clave">
                <div class="form-text">La búsqueda usa el índice FULLTEXT del contenido.</div>
            </div>
            <div class="col-6 col-lg-2">
                <label for="id_servicio" class="form-label small fw-semibold mb-1">Servicio</label>
                <select name="id_servicio" id="id_servicio" class="form-select form-select-sm"
                        data-service-select>
                    <option value="">Todos</option>
                    @foreach ($servicios as $servicio)
                        <option value="{{ $servicio->id_servicio }}" @selected((string) request('id_servicio') === (string) $servicio->id_servicio)>
                            {{ $servicio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label for="id_categoria" class="form-label small fw-semibold mb-1">Categoría</label>
                <select name="id_categoria" id="id_categoria" class="form-select form-select-sm"
                        data-category-select>
                    <option value="">Todas</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id_categoria }}"
                                data-service="{{ $categoria->id_servicio_default }}"
                            @selected((string) request('id_categoria') === (string) $categoria->id_categoria)>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label for="tipo_documento" class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="tipo_documento" id="tipo_documento" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($tiposDocumento as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('tipo_documento') === $valor)>
                            {{ $etiqueta }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if ($puedeGestionar)
                <div class="col-6 col-lg-2">
                    <label for="activo" class="form-label small fw-semibold mb-1">Estado</label>
                    <select name="activo" id="activo" class="form-select form-select-sm">
                        <option value="1" @selected((string) request('activo', '1') === '1')>Activos</option>
                        <option value="0" @selected((string) request('activo') === '0')>Inactivos</option>
                        <option value="" @selected(request()->has('activo') && request('activo') === '')>Todos</option>
                    </select>
                </div>
            @endif
            <div class="col-6 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('kb.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-journal-code text-primary me-1"></i> Artículos de conocimiento</h6>
        <span class="badge bg-light text-dark border">{{ $articulos->total() }} artículos</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Código / Publicación</th>
                    <th>Título y síntomas</th>
                    <th>Servicio y categoría</th>
                    <th>Tipo</th>
                    <th>Autor</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($articulos as $articulo)
                    <tr>
                        <td>
                            <a href="{{ route('kb.show', $articulo) }}" class="fw-bold text-primary text-decoration-none small">
                                {{ $articulo->codigo_articulo }}
                            </a>
                            <div class="text-muted" style="font-size: 0.72rem;">
                                {{ $articulo->fecha_publicacion?->format('d/m/Y') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $articulo->titulo }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                {{ \Illuminate\Support\Str::limit($articulo->sintomas_problema, 110) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $articulo->servicio->nombre }}</span>
                            <div class="text-muted small mt-1">{{ $articulo->categoria->nombre }}</div>
                        </td>
                        <td class="small">{{ $articulo->tipo_documento_label }}</td>
                        <td class="small">{{ $articulo->autor?->nombre_completo ?? '—' }}</td>
                        <td>
                            @if ($articulo->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('kb.show', $articulo) }}" class="btn btn-sm btn-outline-primary">
                                Ver <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                            No se encontraron artículos con los filtros seleccionados.
                            @if (request()->hasAny(['q', 'id_servicio', 'id_categoria', 'tipo_documento', 'activo']))
                                <div class="small mt-2">
                                    <a href="{{ route('kb.index') }}">Limpiar filtros</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($articulos->hasPages())
        <div class="card-footer bg-white">
            {{ $articulos->links() }}
        </div>
    @endif
</div>
@endsection
