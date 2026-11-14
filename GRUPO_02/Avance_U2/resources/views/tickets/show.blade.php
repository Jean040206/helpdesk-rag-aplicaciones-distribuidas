@extends('layouts.app')

@section('title', $ticket->codigo_ticket)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-2">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary" title="Volver a la bandeja">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h3 class="fw-bold mb-0">{{ $ticket->codigo_ticket }}</h3>
            <span class="badge bg-{{ $ticket->estado->color_badge }}">{{ $ticket->estado->nombre }}</span>
            <span class="badge bg-{{ $ticket->prioridad->color_badge }}">Prioridad {{ $ticket->prioridad->nombre }}</span>
        </div>
        <p class="text-muted mb-0">{{ $ticket->asunto }}</p>
    </div>

    <div class="d-flex gap-2">
        @can('update', $ticket)
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        @endcan

        @can('delete', $ticket)
            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                  data-confirm="¿Eliminar definitivamente el ticket {{ $ticket->codigo_ticket }} y todas sus evidencias?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-card-text text-primary me-1"></i> Descripción de la incidencia</h6>
            </div>
            <div class="card-body">
                <p class="pre-wrap-text mb-0">{{ $ticket->descripcion }}</p>
            </div>
        </div>

        @if ($ticket->diagnostico_tecnico)
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard2-pulse text-primary me-1"></i> Diagnóstico técnico</h6>
                </div>
                <div class="card-body">
                    <p class="pre-wrap-text mb-0">{{ $ticket->diagnostico_tecnico }}</p>
                </div>
            </div>
        @endif

        @if ($ticket->solucion_aplicada)
            <div class="card mb-4 border-start border-success border-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-check2-circle text-success me-1"></i> Solución aplicada</h6>
                </div>
                <div class="card-body">
                    <p class="pre-wrap-text mb-0">{{ $ticket->solucion_aplicada }}</p>
                </div>
            </div>
        @endif

        @if ($puedeVerRag)
            @include('tickets._rag')
        @endif

        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-chat-left-text text-primary me-1"></i> Seguimiento</h6>
                <span class="badge bg-light text-dark border">{{ $ticket->seguimientos->count() }} registros</span>
            </div>
            <div class="card-body">
                @forelse ($ticket->seguimientos as $seguimiento)
                    @php($colorTipo = match ($seguimiento->tipo_registro) {
                        'Diagnostico' => 'info',
                        'Solucion' => 'success',
                        default => 'secondary',
                    })
                    <div class="d-flex gap-3 {{ $loop->last ? '' : 'pb-3 mb-3 border-bottom' }}">
                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 38px; height: 38px;">
                            <i class="bi bi-person text-secondary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                                <div class="small">
                                    <strong>{{ $seguimiento->usuario->nombre_completo }}</strong>
                                    <span class="badge bg-{{ $colorTipo }} ms-1">{{ $seguimiento->tipo_registro }}</span>
                                </div>
                                <small class="text-muted">{{ $seguimiento->fecha_registro?->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="pre-wrap-text small mb-0 mt-1">{{ $seguimiento->comentario }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Todavía no hay comentarios de seguimiento.</p>
                @endforelse
            </div>
        </div>

        @can('comment', $ticket)
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle text-primary me-1"></i> Agregar seguimiento</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.comment', $ticket) }}">
                        @csrf
                        <label for="comentario" class="form-label small fw-semibold">Comentario</label>
                        <textarea name="comentario" id="comentario" rows="3"
                                  class="form-control @error('comentario') is-invalid @enderror"
                                  maxlength="2000" placeholder="Agrega información, avances o consultas sobre la atención."
                                  required>{{ old('comentario') }}</textarea>
                        @error('comentario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-end mt-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-send me-1"></i> Publicar comentario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-1"></i> Historial de estados</h6>
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($ticket->historial as $evento)
                    <li class="list-group-item">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                @if ($evento->estadoAnterior)
                                    <span class="badge bg-{{ $evento->estadoAnterior->color_badge }}">{{ $evento->estadoAnterior->nombre }}</span>
                                    <i class="bi bi-arrow-right text-muted"></i>
                                @endif
                                <span class="badge bg-{{ $evento->estadoNuevo->color_badge }}">{{ $evento->estadoNuevo->nombre }}</span>
                            </div>
                            <small class="text-muted">{{ $evento->fecha_cambio?->format('d/m/Y H:i') }}</small>
                        </div>
                        <div class="small mt-1">{{ $evento->observacion }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            Responsable: {{ $evento->usuarioResponsable?->nombre_completo ?? 'Sistema' }}
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle text-primary me-1"></i> Detalles del ticket</h6>
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">Solicitante</dt>
                    <dd class="col-7 fw-semibold">{{ $ticket->solicitante->nombre_completo }}</dd>

                    <dt class="col-5 text-muted fw-normal">Técnico</dt>
                    <dd class="col-7">{{ $ticket->tecnicoAsignado?->nombre_completo ?? 'Sin asignar' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Supervisor</dt>
                    <dd class="col-7">{{ $ticket->supervisorAsignador?->nombre_completo ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Servicio</dt>
                    <dd class="col-7">{{ $ticket->servicio->nombre }}</dd>

                    <dt class="col-5 text-muted fw-normal">Categoría</dt>
                    <dd class="col-7">{{ $ticket->categoria->nombre }}</dd>

                    <dt class="col-5 text-muted fw-normal">Registro</dt>
                    <dd class="col-7">{{ $ticket->fecha_registro?->format('d/m/Y H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Asignación</dt>
                    <dd class="col-7">{{ $ticket->fecha_asignacion?->format('d/m/Y H:i') ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Resolución</dt>
                    <dd class="col-7 mb-0">{{ $ticket->fecha_resolucion?->format('d/m/Y H:i') ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        @can('assign', $ticket)
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-check text-primary me-1"></i> Asignación y prioridad</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="id_tecnico_asignado" class="form-label small fw-semibold">Técnico responsable</label>
                            <select name="id_tecnico_asignado" id="id_tecnico_asignado"
                                    class="form-select form-select-sm @error('id_tecnico_asignado') is-invalid @enderror" required>
                                <option value="">Seleccione un técnico</option>
                                @foreach ($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id_usuario }}"
                                        @selected((string) old('id_tecnico_asignado', $ticket->id_tecnico_asignado) === (string) $tecnico->id_usuario)>
                                        {{ $tecnico->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tecnico_asignado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_prioridad_asignacion" class="form-label small fw-semibold">Prioridad</label>
                            <select name="id_prioridad" id="id_prioridad_asignacion"
                                    class="form-select form-select-sm @error('id_prioridad') is-invalid @enderror" required>
                                @foreach ($prioridades as $prioridad)
                                    <option value="{{ $prioridad->id_prioridad }}"
                                        @selected((string) old('id_prioridad', $ticket->id_prioridad) === (string) $prioridad->id_prioridad)>
                                        {{ $prioridad->nombre }} — SLA {{ $prioridad->sla_horas }} h
                                    </option>
                                @endforeach
                            </select>
                            @error('id_prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observacion_asignacion" class="form-label small fw-semibold">Observación</label>
                            <textarea name="observacion" id="observacion_asignacion" rows="2"
                                      class="form-control form-control-sm" maxlength="255"
                                      placeholder="Motivo o indicación para el técnico">{{ old('observacion') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-person-check me-1"></i> Guardar asignación
                        </button>
                    </form>
                </div>
            </div>
        @endcan

        @if ($estadosDisponibles->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-repeat text-primary me-1"></i> Actualizar estado</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="id_estado" class="form-label small fw-semibold">Nuevo estado</label>
                            <select name="id_estado" id="id_estado"
                                    class="form-select form-select-sm @error('id_estado') is-invalid @enderror" required>
                                <option value="">Seleccione el estado</option>
                                @foreach ($estadosDisponibles as $estado)
                                    <option value="{{ $estado->id_estado }}" @selected((string) old('id_estado') === (string) $estado->id_estado)>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if (! $usuario->hasRole('Solicitante'))
                            <div class="mb-3">
                                <label for="diagnostico_tecnico" class="form-label small fw-semibold">Diagnóstico técnico</label>
                                <textarea name="diagnostico_tecnico" id="diagnostico_tecnico" rows="3"
                                          class="form-control form-control-sm @error('diagnostico_tecnico') is-invalid @enderror"
                                          maxlength="5000" placeholder="Causa identificada de la incidencia">{{ old('diagnostico_tecnico', $ticket->diagnostico_tecnico) }}</textarea>
                                @error('diagnostico_tecnico')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="solucion_aplicada" class="form-label small fw-semibold">Solución aplicada</label>
                                <textarea name="solucion_aplicada" id="solucion_aplicada" rows="3"
                                          class="form-control form-control-sm @error('solucion_aplicada') is-invalid @enderror"
                                          maxlength="5000" placeholder="Obligatoria para marcar el ticket como Resuelto">{{ old('solucion_aplicada', $ticket->solucion_aplicada) }}</textarea>
                                @error('solucion_aplicada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <p class="small text-muted">
                                Al cerrar el ticket confirmas que la solución aplicada resolvió tu incidencia.
                            </p>
                        @endif

                        <div class="mb-3">
                            <label for="observacion_estado" class="form-label small fw-semibold">Observación del cambio</label>
                            <textarea name="observacion" id="observacion_estado" rows="2"
                                      class="form-control form-control-sm" maxlength="255"
                                      placeholder="Opcional">{{ old('observacion') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-check2-circle me-1"></i> Confirmar cambio
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-paperclip text-primary me-1"></i> Evidencias</h6>
            </div>
            <div class="card-body">
                @forelse ($ticket->evidencias as $evidencia)
                    @php($icono = match ($evidencia->extension) {
                        'jpg', 'jpeg', 'png' => 'bi-file-earmark-image',
                        'pdf' => 'bi-file-earmark-pdf',
                        'docx' => 'bi-file-earmark-word',
                        default => 'bi-file-earmark',
                    })
                    <div class="d-flex justify-content-between align-items-center {{ $loop->last ? '' : 'mb-2 pb-2 border-bottom' }}">
                        <div class="small text-truncate me-2">
                            <i class="bi {{ $icono }} text-secondary me-1"></i>
                            {{ $evidencia->nombre_original }}
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $evidencia->tamano_kb }} KB</div>
                        </div>
                        <a href="{{ route('tickets.evidence.download', [$ticket, $evidencia]) }}"
                           class="btn btn-sm btn-outline-secondary" title="Descargar evidencia">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No se adjuntaron evidencias.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
