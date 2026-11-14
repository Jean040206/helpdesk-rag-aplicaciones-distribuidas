@extends('layouts.app')

@section('title', 'Editar '.$ticket->codigo_ticket)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Editar {{ $ticket->codigo_ticket }}</h3>
        <p class="text-muted small mb-0">Actualiza los datos de la incidencia. El estado y la asignación se gestionan desde el detalle.</p>
    </div>
    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al ticket
    </a>
</div>

@include('tickets._form')
@endsection
