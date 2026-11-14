@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Gestión de tickets</h3>
        <p class="text-muted small mb-0">{{ $alcance }}</p>
    </div>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Ticket
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('tickets.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label for="q" class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" name="q" id="q" class="form-control form-control-sm"
                       value="{{ request('q') }}" placeholder="Código o asunto del ticket">
            </div>
            <div class="col-6 col-lg-2">
                <label for="id_estado" class="form-label small fw-semibold mb-1">Estado</label>
                <select name="id_estado" id="id_estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado }}" @selected((string) request('id_estado') === (string) $estado->id_estado)>
                            {{ $estado->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label for="id_prioridad" class="form-label small fw-semibold mb-1">Prioridad</label>
                <select name="id_prioridad" id="id_prioridad" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach ($prioridades as $prioridad)
                        <option value="{{ $prioridad->id_prioridad }}" @selected((string) request('id_prioridad') === (string) $prioridad->id_prioridad)>
                            {{ $prioridad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label for="id_servicio" class="form-label small fw-semibold mb-1">Servicio</label>
                <select name="id_servicio" id="id_servicio" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($servicios as $servicio)
                        <option value="{{ $servicio->id_servicio }}" @selected((string) request('id_servicio') === (string) $servicio->id_servicio)>
                            {{ $servicio->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-ticket-detailed-fill text-primary me-1"></i> Bandeja de tickets</h6>
        <span class="badge bg-light text-dark border">{{ $tickets->total() }} tickets</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Código / Fecha</th>
                    <th>Servicio y asunto</th>
                    <th>Solicitante</th>
                    <th>Técnico</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="fw-bold text-primary text-decoration-none small">
                                {{ $ticket->codigo_ticket }}
                            </a>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $ticket->fecha_registro?->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $ticket->servicio->nombre }}</span>
                            <div class="fw-semibold small mt-1">{{ $ticket->asunto }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $ticket->categoria->nombre }}</div>
                        </td>
                        <td class="small">{{ $ticket->solicitante->nombre_completo }}</td>
                        <td class="small">
                            @if ($ticket->tecnicoAsignado)
                                {{ $ticket->tecnicoAsignado->nombre_completo }}
                            @else
                                <span class="text-muted">Sin asignar</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $ticket->prioridad->color_badge }}">{{ $ticket->prioridad->nombre }}</span></td>
                        <td><span class="badge bg-{{ $ticket->estado->color_badge }}">{{ $ticket->estado->nombre }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                Ver <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No se encontraron tickets con los filtros seleccionados.
                            @if (request()->hasAny(['q', 'id_estado', 'id_prioridad', 'id_servicio']))
                                <div class="small mt-2">
                                    <a href="{{ route('tickets.index') }}">Limpiar filtros</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($tickets->hasPages())
        <div class="card-footer bg-white">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
