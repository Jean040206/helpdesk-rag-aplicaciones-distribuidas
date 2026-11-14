@extends('layouts.app')

@section('title', 'Editar '.$articulo->codigo_articulo)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Editar {{ $articulo->codigo_articulo }}</h3>
        <p class="text-muted small mb-0">Actualiza el contenido, la clasificación o el estado del artículo.</p>
    </div>
    <a href="{{ route('kb.show', $articulo) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al artículo
    </a>
</div>

@include('kb._form')
@endsection
