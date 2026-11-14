@php($evaluacion = $ultimaEvaluacionRag ?? null)
@php($pendiente = $evaluacion && $evaluacion->estado === 'exitosa' && $evaluacion->decision_tecnico === 'Pendiente')
@php($colorDecision = match ($evaluacion?->decision_tecnico) {
    'Aceptada' => 'success',
    'Modificada' => 'warning',
    'Descartada' => 'secondary',
    default => 'info',
})

<div class="card mb-4 rag-box" id="rag">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-robot text-primary me-1"></i> Asistente RAG
            </h6>
            <div class="text-muted" style="font-size: 0.75rem;">
                Recomendación generada con Google Gemini a partir de la base de conocimiento.
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-light text-dark border">{{ $ragEvaluacionesTotal }} evaluaciones</span>

            @can('viewAny', App\Models\EvaluacionRag::class)
                <a href="{{ route('rag.index', ['q' => $ticket->codigo_ticket]) }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-list-ul"></i> Historial
                </a>
            @endcan

            @if ($puedeGenerarRag)
                <form method="POST" action="{{ route('tickets.rag.generate', $ticket) }}"
                      data-confirm="¿Generar una nueva recomendación con Gemini? La evaluación quedará registrada.">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary" @disabled(! $ragDisponible)>
                        <i class="bi bi-stars"></i>
                        {{ $evaluacion ? 'Regenerar recomendación' : 'Generar recomendación' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if (! $ragDisponible)
            <div class="alert alert-warning small mb-0" role="alert">
                <i class="bi bi-key-fill me-1"></i>
                Configure <code>GEMINI_API_KEY</code> en el archivo <code>.env</code> para habilitar el asistente real.
                No se generan recomendaciones simuladas.
            </div>
        @elseif (! $evaluacion)
            <p class="text-muted small mb-0">
                Todavía no se ha generado una recomendación para este ticket. Use el botón
                <strong>Generar recomendación</strong> para consultar la base de conocimiento con Gemini.
            </p>
        @else
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge bg-{{ $evaluacion->estado === 'exitosa' ? 'success' : 'danger' }}">
                    <i class="bi bi-{{ $evaluacion->estado === 'exitosa' ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
                    {{ $evaluacion->estado === 'exitosa' ? 'Recomendación generada' : 'Error de generación' }}
                </span>
                <span class="badge bg-{{ $colorDecision }}">Decisión: {{ $evaluacion->decision_tecnico }}</span>
                <span class="badge bg-light text-dark border">{{ $evaluacion->proveedor }} · {{ $evaluacion->modelo_utilizado }}</span>
                <span class="text-muted small">
                    {{ $evaluacion->fecha_evento?->format('d/m/Y H:i') }}
                    · {{ number_format($evaluacion->tiempo_procesamiento_ms / 1000, 2) }} s
                    @if ($evaluacion->tokens_entrada !== null || $evaluacion->tokens_salida !== null)
                        · {{ (int) $evaluacion->tokens_entrada + (int) $evaluacion->tokens_salida }} tokens
                    @endif
                </span>
            </div>

            @if ($evaluacion->estado === 'error')
                <div class="alert alert-danger small" role="alert">
                    <strong>No se generó una recomendación.</strong>
                    {{ $evaluacion->error_mensaje }}
                </div>
            @else
                @if ($evaluacion->confianza !== null)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Confianza declarada por el modelo</span>
                            <span>{{ number_format($evaluacion->confianza, 1) }} %</span>
                        </div>
                        <div class="progress" style="height: 6px;" role="progressbar"
                             aria-label="Confianza de la recomendación"
                             aria-valuenow="{{ $evaluacion->confianza }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-{{ $evaluacion->confianza >= 70 ? 'success' : ($evaluacion->confianza >= 40 ? 'warning' : 'danger') }}"
                                 style="width: {{ $evaluacion->confianza }}%;"></div>
                        </div>
                    </div>
                @endif

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-2 h-100 bg-light">
                            <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Categoría sugerida</div>
                            <div class="small fw-semibold">{{ $evaluacion->categoria_sugerida ?? 'No indicada' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2 h-100 bg-light">
                            <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Prioridad sugerida</div>
                            <div class="small fw-semibold">{{ $evaluacion->prioridad_sugerida ?? 'No indicada' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2 h-100 bg-light">
                            <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Servicio sugerido</div>
                            <div class="small fw-semibold">{{ $evaluacion->servicio_sugerido ?? 'No indicado' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold small text-uppercase text-muted mb-1">Posible causa</h6>
                    <p class="pre-wrap-text small mb-0">{{ $evaluacion->posible_causa }}</p>
                </div>

                <div class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                        <h6 class="fw-bold small text-uppercase text-muted mb-0">Solución recomendada</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-rag-copy="{{ $evaluacion->solucion_recomendada }}">
                            <i class="bi bi-clipboard-check"></i> Copiar a solución aplicada
                        </button>
                    </div>
                    <div class="border-start border-success border-4 bg-light rounded p-3">
                        <p class="pre-wrap-text small mb-0">{{ $evaluacion->solucion_recomendada }}</p>
                    </div>
                </div>
            @endif

            @if ($evaluacion->fuentes->isNotEmpty())
                <div class="mb-3">
                    <h6 class="fw-bold small text-uppercase text-muted mb-2">
                        <i class="bi bi-journal-code me-1"></i> Fuentes recuperadas de la base de conocimiento
                    </h6>
                    <div class="list-group list-group-flush border rounded">
                        @foreach ($evaluacion->fuentes as $fuente)
                            <div class="list-group-item py-2">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                                    <div class="small">
                                        <span class="badge bg-light text-dark border me-1">#{{ $fuente->posicion }}</span>
                                        @if ($fuente->articulo)
                                            @can('view', $fuente->articulo)
                                                <a href="{{ route('kb.show', $fuente->articulo) }}"
                                                   class="fw-semibold text-decoration-none">
                                                    {{ $fuente->articulo->codigo_articulo }} — {{ $fuente->articulo->titulo }}
                                                </a>
                                            @else
                                                <span class="fw-semibold">
                                                    {{ $fuente->articulo->codigo_articulo }} — {{ $fuente->articulo->titulo }}
                                                </span>
                                            @endcan
                                        @else
                                            <span class="text-muted">Artículo no disponible</span>
                                        @endif
                                    </div>
                                    @if ($fuente->score !== null)
                                        <span class="badge bg-info-subtle text-info-emphasis border">
                                            score {{ number_format($fuente->score, 2) }}
                                        </span>
                                    @endif
                                </div>
                                @if ($fuente->fragmento)
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">
                                        {{ \Illuminate\Support\Str::limit($fuente->fragmento, 220) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($pendiente && $puedeDecidirRag)
                <hr>
                <form method="POST" action="{{ route('tickets.rag.decision', [$ticket, $evaluacion]) }}">
                    @csrf
                    <h6 class="fw-bold small text-uppercase text-muted mb-2">Decisión del técnico</h6>

                    <div class="row g-2 mb-3">
                        @foreach ([
                            'Aceptada' => ['success', 'Aceptar', 'La recomendación es correcta y se aplicará.'],
                            'Modificada' => ['warning', 'Modificar', 'Se aplicará una versión ajustada.'],
                            'Descartada' => ['secondary', 'Descartar', 'No aplica al caso o no es segura.'],
                        ] as $valor => [$color, $titulo, $descripcion])
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="decision_tecnico"
                                       id="decision_{{ $valor }}" value="{{ $valor }}"
                                       data-rag-decision
                                       @checked(old('decision_tecnico', 'Aceptada') === $valor)>
                                <label class="btn btn-outline-{{ $color }} w-100 h-100 text-start p-2" for="decision_{{ $valor }}">
                                    <span class="fw-semibold small d-block">{{ $titulo }}</span>
                                    <span class="d-block" style="font-size: 0.72rem;">{{ $descripcion }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('decision_tecnico')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror

                    <div class="mb-3" data-rag-final-wrapper>
                        <label for="solucion_final_tecnico" class="form-label small fw-semibold">
                            Solución final del técnico
                        </label>
                        <textarea name="solucion_final_tecnico" id="solucion_final_tecnico" rows="4"
                                  class="form-control form-control-sm @error('solucion_final_tecnico') is-invalid @enderror"
                                  maxlength="10000">{{ old('solucion_final_tecnico', $evaluacion->solucion_recomendada) }}</textarea>
                        <div class="form-text">
                            Si acepta sin cambios, se usará la recomendación. Si modifica, este campo es obligatorio.
                        </div>
                        @error('solucion_final_tecnico')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2" data-rag-comment-wrapper>
                        <label for="comentario_tecnico" class="form-label small fw-semibold">
                            Comentario de la decisión
                        </label>
                        <textarea name="comentario_tecnico" id="comentario_tecnico" rows="2"
                                  class="form-control form-control-sm @error('comentario_tecnico') is-invalid @enderror"
                                  maxlength="2000"
                                  placeholder="Obligatorio si descarta la recomendación">{{ old('comentario_tecnico') }}</textarea>
                        @error('comentario_tecnico')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check2-circle me-1"></i> Guardar decisión
                        </button>
                    </div>
                </form>
            @elseif ($evaluacion->decision_tecnico !== 'Pendiente')
                <hr>
                <h6 class="fw-bold small text-uppercase text-muted mb-2">Decisión registrada</h6>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $colorDecision }}">{{ $evaluacion->decision_tecnico }}</span>
                    <span class="text-muted small">
                        {{ $evaluacion->fecha_decision?->format('d/m/Y H:i') }}
                        · {{ $evaluacion->tecnicoEvaluador?->nombre_completo ?? 'Usuario no disponible' }}
                    </span>
                </div>
                @if ($evaluacion->solucion_final_tecnico)
                    <div class="border rounded bg-light p-3 mb-2">
                        <p class="pre-wrap-text small mb-0">{{ $evaluacion->solucion_final_tecnico }}</p>
                    </div>
                @endif
                @if ($evaluacion->comentario_tecnico)
                    <p class="small text-muted mb-0">
                        <i class="bi bi-chat-left-text me-1"></i> {{ $evaluacion->comentario_tecnico }}
                    </p>
                @endif
            @endif
        @endif
    </div>
</div>
