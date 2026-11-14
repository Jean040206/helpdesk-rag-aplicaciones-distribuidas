@extends('layouts.app')

@section('title', 'Evaluaciones RAG')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Evaluaciones RAG</h3>
        <p class="text-muted small mb-0">
            Bitácora de las interacciones reales con el asistente: contexto recuperado, respuesta del modelo y decisión del técnico.
        </p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al dashboard
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rag.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label for="q" class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text" name="q" id="q" class="form-control form-control-sm"
                       value="{{ $texto }}" maxlength="100"
                       placeholder="Código de ticket, asunto o modelo">
            </div>
            <div class="col-6 col-lg-2">
                <label for="estado" class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" id="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($estados as $valor)
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>
                            {{ $valor === 'exitosa' ? 'Exitosa' : 'Error' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-3">
                <label for="decision" class="form-label small fw-semibold mb-1">Decisión</label>
                <select name="decision" id="decision" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach ($decisiones as $valor)
                        <option value="{{ $valor }}" @selected(request('decision') === $valor)>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="{{ route('rag.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-robot text-primary me-1"></i> Interacciones registradas</h6>
        <span class="badge bg-light text-dark border">{{ $evaluaciones->total() }} evaluaciones</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Fecha</th>
                    <th>Ticket</th>
                    <th>Modelo</th>
                    <th>Estado</th>
                    <th>Confianza</th>
                    <th>Decisión</th>
                    <th>Evaluador</th>
                    <th>Fuentes</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($evaluaciones as $evaluacion)
                    <tr>
                        <td class="small">
                            {{ $evaluacion->fecha_evento?->format('d/m/Y H:i') }}
                            <div class="text-muted" style="font-size: 0.72rem;">
                                {{ number_format($evaluacion->tiempo_procesamiento_ms / 1000, 2) }} s
                            </div>
                        </td>
                        <td>
                            @if ($evaluacion->ticket)
                                <a href="{{ route('tickets.show', $evaluacion->ticket) }}"
                                   class="fw-bold text-primary text-decoration-none small">
                                    {{ $evaluacion->ticket->codigo_ticket }}
                                </a>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ \Illuminate\Support\Str::limit($evaluacion->ticket->asunto, 60) }}
                                </div>
                            @else
                                <span class="text-muted small">Ticket eliminado</span>
                            @endif
                        </td>
                        <td class="small">
                            {{ $evaluacion->modelo_utilizado ?? '—' }}
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $evaluacion->proveedor ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $evaluacion->estado === 'exitosa' ? 'success' : 'danger' }}">
                                {{ $evaluacion->estado === 'exitosa' ? 'Exitosa' : 'Error' }}
                            </span>
                        </td>
                        <td class="small">
                            {{ $evaluacion->confianza !== null ? number_format($evaluacion->confianza, 1).' %' : '—' }}
                        </td>
                        <td>
                            @php($colorDecision = match ($evaluacion->decision_tecnico) {
                                'Aceptada' => 'success',
                                'Modificada' => 'warning',
                                'Descartada' => 'secondary',
                                default => 'info',
                            })
                            <span class="badge bg-{{ $colorDecision }}">{{ $evaluacion->decision_tecnico }}</span>
                        </td>
                        <td class="small">{{ $evaluacion->tecnicoEvaluador?->nombre_completo ?? '—' }}</td>
                        <td class="small">{{ $evaluacion->fuentes_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('rag.show', $evaluacion) }}" class="btn btn-sm btn-outline-primary">
                                Ver <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-robot fs-3 d-block mb-2"></i>
                            No hay evaluaciones RAG registradas con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($evaluaciones->hasPages())
        <div class="card-footer bg-white">
            {{ $evaluaciones->links() }}
        </div>
    @endif
</div>
@endsection
