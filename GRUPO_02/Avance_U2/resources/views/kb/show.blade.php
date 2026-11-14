@extends('layouts.app')

@section('title', $articulo->codigo_articulo)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <a href="{{ route('kb.index') }}" class="btn btn-sm btn-outline-secondary" title="Volver a la base de conocimiento">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="fw-bold mb-0">{{ $articulo->codigo_articulo }}</h3>
            <span class="badge bg-{{ $articulo->activo ? 'success' : 'secondary' }}">
                {{ $articulo->activo ? 'Activo' : 'Inactivo' }}
            </span>
            <span class="badge bg-light text-dark border">{{ $articulo->tipo_documento_label }}</span>
        </div>
        <p class="text-muted mb-0">{{ $articulo->titulo }}</p>
    </div>

    @if ($puedeGestionar)
        <div class="d-flex gap-2">
            <a href="{{ route('kb.edit', $articulo) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
            <form method="POST" action="{{ route('kb.destroy', $articulo) }}"
                  data-confirm="¿Eliminar definitivamente el artículo {{ $articulo->codigo_articulo }}?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
        </div>
    @endif
</div>

@if (! $articulo->activo)
    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Este artículo está inactivo y no se muestra a los técnicos en la bandeja de consulta.
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard2-pulse text-primary me-1"></i> Síntomas o problema</h6>
            </div>
            <div class="card-body">
                <p class="pre-wrap-text mb-0">{{ $articulo->sintomas_problema }}</p>
            </div>
        </div>

        <div class="card mb-4 border-start border-success border-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-check2-circle text-success me-1"></i> Pasos de solución</h6>
            </div>
            <div class="card-body">
                <p class="pre-wrap-text mb-0">{{ $articulo->pasos_solucion }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-key text-primary me-1"></i> Palabras clave</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach (preg_split('/\s+/u', trim($articulo->palabras_clave)) as $palabra)
                        <span class="badge bg-light text-dark border">{{ $palabra }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle text-primary me-1"></i> Detalles del artículo</h6>
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">Servicio</dt>
                    <dd class="col-7 fw-semibold">{{ $articulo->servicio->nombre }}</dd>

                    <dt class="col-5 text-muted fw-normal">Categoría</dt>
                    <dd class="col-7">{{ $articulo->categoria->nombre }}</dd>

                    <dt class="col-5 text-muted fw-normal">Tipo</dt>
                    <dd class="col-7">{{ $articulo->tipo_documento_label }}</dd>

                    <dt class="col-5 text-muted fw-normal">Autor</dt>
                    <dd class="col-7">{{ $articulo->autor?->nombre_completo ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Publicación</dt>
                    <dd class="col-7">{{ $articulo->fecha_publicacion?->format('d/m/Y H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Estado</dt>
                    <dd class="col-7 mb-0">
                        <span class="badge bg-{{ $articulo->activo ? 'success' : 'secondary' }}">
                            {{ $articulo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card rag-box p-3">
            <h6 class="fw-bold text-primary"><i class="bi bi-robot me-1"></i> Uso en el asistente RAG</h6>
            <p class="small text-muted mb-0">
                Este artículo será considerado por el asistente cuando el índice FULLTEXT recupere su contenido
                como contexto relevante para una consulta técnica.
            </p>
        </div>
    </div>
</div>
@endsection
