@extends('layouts.app')

@section('title', 'Evaluación RAG #'.$evaluacion->id_evaluacion_rag)

@section('content')
@php($colorDecision = match ($evaluacion->decision_tecnico) {
    'Aceptada' => 'success',
    'Modificada' => 'warning',
    'Descartada' => 'secondary',
    default => 'info',
})

<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <a href="{{ route('rag.index') }}" class="btn btn-sm btn-outline-secondary" title="Volver a la bitácora">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="fw-bold mb-0">Evaluación RAG #{{ $evaluacion->id_evaluacion_rag }}</h3>
            <span class="badge bg-{{ $evaluacion->estado === 'exitosa' ? 'success' : 'danger' }}">
                {{ $evaluacion->estado === 'exitosa' ? 'Exitosa' : 'Error' }}
            </span>
            <span class="badge bg-{{ $colorDecision }}">Decisión: {{ $evaluacion->decision_tecnico }}</span>
        </div>
        @if ($evaluacion->ticket)
            <p class="text-muted mb-0">
                Ticket
                <a href="{{ route('tickets.show', $evaluacion->ticket) }}" class="text-decoration-none">
                    {{ $evaluacion->ticket->codigo_ticket }}
                </a>
                — {{ $evaluacion->ticket->asunto }}
            </p>
        @endif
    </div>
    @if ($evaluacion->ticket)
        <a href="{{ route('tickets.show', $evaluacion->ticket) }}" class="btn btn-outline-primary">
            <i class="bi bi-ticket-detailed"></i> Ver ticket
        </a>
    @endif
</div>

@if ($evaluacion->estado === 'error')
    <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>La interacción falló:</strong> {{ $evaluacion->error_mensaje }}
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        @if ($evaluacion->estado === 'exitosa')
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightbulb text-primary me-1"></i> Recomendación del asistente</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-2 h-100 bg-light">
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Categoría</div>
                                <div class="small fw-semibold">{{ $evaluacion->categoria_sugerida ?? 'No indicada' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2 h-100 bg-light">
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Prioridad</div>
                                <div class="small fw-semibold">{{ $evaluacion->prioridad_sugerida ?? 'No indicada' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2 h-100 bg-light">
                                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Servicio</div>
                                <div class="small fw-semibold">{{ $evaluacion->servicio_sugerido ?? 'No indicado' }}</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold small text-uppercase text-muted mb-1">Posible causa</h6>
                    <p class="pre-wrap-text small mb-3">{{ $evaluacion->posible_causa }}</p>

                    <h6 class="fw-bold small text-uppercase text-muted mb-1">Solución recomendada</h6>
                    <div class="border-start border-success border-4 bg-light rounded p-3">
                        <p class="pre-wrap-text small mb-0">{{ $evaluacion->solucion_recomendada }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-journal-code text-primary me-1"></i> Fuentes recuperadas</h6>
                <span class="badge bg-light text-dark border">{{ $evaluacion->fuentes->count() }} artículos</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>#</th>
                            <th>Artículo</th>
                            <th>Score</th>
                            <th>Fragmento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evaluacion->fuentes as $fuente)
                            <tr>
                                <td class="small">{{ $fuente->posicion }}</td>
                                <td class="small">
                                    @if ($fuente->articulo)
                                        <a href="{{ route('kb.show', $fuente->articulo) }}" class="text-decoration-none fw-semibold">
                                            {{ $fuente->articulo->codigo_articulo }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $fuente->articulo->titulo }}</div>
                                    @else
                                        <span class="text-muted">No disponible</span>
                                    @endif
                                </td>
                                <td class="small">{{ $fuente->score !== null ? number_format($fuente->score, 4) : '—' }}</td>
                                <td class="small text-muted">{{ \Illuminate\Support\Str::limit($fuente->fragmento, 180) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4 small">
                                    No se recuperaron artículos para esta evaluación.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-code-slash text-primary me-1"></i> Trazabilidad técnica</h6>
            </div>
            <div class="card-body">
                <details class="mb-3">
                    <summary class="fw-semibold small">Prompt enviado</summary>
                    <pre class="pre-wrap-text small bg-light border rounded p-3 mt-2 mb-0">{{ $evaluacion->prompt_enviado ?? 'No registrado' }}</pre>
                </details>
                <details class="mb-3">
                    <summary class="fw-semibold small">Respuesta estructurada</summary>
                    <pre class="pre-wrap-text small bg-light border rounded p-3 mt-2 mb-0">{{ $evaluacion->respuesta_estructurada ? json_encode($evaluacion->respuesta_estructurada, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'No disponible' }}</pre>
                </details>
                <details>
                    <summary class="fw-semibold small">Respuesta cruda del proveedor</summary>
                    <pre class="pre-wrap-text small bg-light border rounded p-3 mt-2 mb-0">{{ $evaluacion->respuesta_cruda ?? 'No disponible' }}</pre>
                </details>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle text-primary me-1"></i> Datos de la interacción</h6>
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">Proveedor</dt>
                    <dd class="col-7 fw-semibold">{{ $evaluacion->proveedor ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Modelo</dt>
                    <dd class="col-7">{{ $evaluacion->modelo_utilizado ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Método</dt>
                    <dd class="col-7">{{ $evaluacion->metodo_ia }}</dd>

                    <dt class="col-5 text-muted fw-normal">Fecha</dt>
                    <dd class="col-7">{{ $evaluacion->fecha_evento?->format('d/m/Y H:i:s') ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Latencia</dt>
                    <dd class="col-7">{{ number_format($evaluacion->tiempo_procesamiento_ms / 1000, 2) }} s</dd>

                    <dt class="col-5 text-muted fw-normal">Tokens</dt>
                    <dd class="col-7">
                        {{ $evaluacion->tokens_entrada ?? '—' }} entrada /
                        {{ $evaluacion->tokens_salida ?? '—' }} salida
                    </dd>

                    <dt class="col-5 text-muted fw-normal">Confianza</dt>
                    <dd class="col-7">{{ $evaluacion->confianza !== null ? number_format($evaluacion->confianza, 2).' %' : '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Dato sintético</dt>
                    <dd class="col-7 mb-0">{{ $evaluacion->es_dato_sintetico ? 'Sí' : 'No' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-person-check text-primary me-1"></i> Decisión del técnico</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $colorDecision }}">{{ $evaluacion->decision_tecnico }}</span>
                    <span class="small text-muted">
                        {{ $evaluacion->fecha_decision?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                    </span>
                </div>
                <div class="small mb-2">
                    <span class="text-muted">Evaluador:</span>
                    {{ $evaluacion->tecnicoEvaluador?->nombre_completo ?? '—' }}
                </div>
                @if ($evaluacion->solucion_final_tecnico)
                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.68rem;">Solución final</div>
                    <div class="border rounded bg-light p-2 mb-2">
                        <p class="pre-wrap-text small mb-0">{{ $evaluacion->solucion_final_tecnico }}</p>
                    </div>
                @endif
                @if ($evaluacion->comentario_tecnico)
                    <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.68rem;">Comentario</div>
                    <p class="small mb-0">{{ $evaluacion->comentario_tecnico }}</p>
                @endif
            </div>
        </div>

        <div class="card rag-box p-3">
            <h6 class="fw-bold text-primary"><i class="bi bi-shield-check me-1"></i> Registro persistente</h6>
            <p class="small text-muted mb-0">
                Esta evaluación conserva el prompt, la respuesta del proveedor, las fuentes recuperadas y la decisión
                humana para auditoría. No se almacenan recomendaciones simuladas.
            </p>
        </div>
    </div>
</div>
@endsection
