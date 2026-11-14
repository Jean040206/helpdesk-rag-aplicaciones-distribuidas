@extends('layouts.app')

@section('title', 'Nuevo ticket')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Nuevo ticket de soporte</h3>
        <p class="text-muted small mb-0">Registra la incidencia con el mayor detalle posible para facilitar su atención.</p>
    </div>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a la bandeja
    </a>
</div>

@include('tickets._form')
@endsection
