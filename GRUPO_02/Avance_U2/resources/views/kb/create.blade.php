@extends('layouts.app')

@section('title', 'Nuevo artículo de conocimiento')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Nuevo artículo de conocimiento</h3>
        <p class="text-muted small mb-0">Documenta un procedimiento o solución reutilizable para el soporte técnico.</p>
    </div>
    <a href="{{ route('kb.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a la base
    </a>
</div>

@include('kb._form')
@endsection
