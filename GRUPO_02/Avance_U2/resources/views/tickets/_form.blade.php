@php($ticket = $ticket ?? null)
@php($esEdicion = ! is_null($ticket))
@php($prioridadSeleccionada = old('id_prioridad', $ticket?->id_prioridad ?? $prioridadDefault))

<form method="POST"
      action="{{ $esEdicion ? route('tickets.update', $ticket) : route('tickets.store') }}"
      enctype="multipart/form-data">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-ticket-detailed text-primary me-1"></i> Datos de la incidencia</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="id_servicio" class="form-label fw-semibold small">Servicio afectado</label>
                            <select name="id_servicio" id="id_servicio"
                                    class="form-select @error('id_servicio') is-invalid @enderror"
                                    data-service-select required>
                                <option value="">Seleccione un servicio</option>
                                @foreach ($servicios as $servicio)
                                    <option value="{{ $servicio->id_servicio }}"
                                        @selected((string) old('id_servicio', $ticket?->id_servicio) === (string) $servicio->id_servicio)>
                                        {{ $servicio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_servicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="id_categoria" class="form-label fw-semibold small">Categoría</label>
                            <select name="id_categoria" id="id_categoria"
                                    class="form-select @error('id_categoria') is-invalid @enderror"
                                    data-category-select required>
                                <option value="">Seleccione un servicio primero</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id_categoria }}"
                                            data-service="{{ $categoria->id_servicio_default }}"
                                        @selected((string) old('id_categoria', $ticket?->id_categoria) === (string) $categoria->id_categoria)>
                                        {{ $categoria->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_categoria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="id_prioridad" class="form-label fw-semibold small">Prioridad</label>
                            <select name="id_prioridad" id="id_prioridad"
                                    class="form-select @error('id_prioridad') is-invalid @enderror" required>
                                <option value="">Seleccione la prioridad</option>
                                @foreach ($prioridades as $prioridad)
                                    <option value="{{ $prioridad->id_prioridad }}"
                                        @selected((string) $prioridadSeleccionada === (string) $prioridad->id_prioridad)>
                                        {{ $prioridad->nombre }} — SLA {{ $prioridad->sla_horas }} h
                                    </option>
                                @endforeach
                            </select>
                            @error('id_prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Estado</label>
                            <div class="form-control bg-light">
                                @if ($esEdicion)
                                    <span class="badge bg-{{ $ticket->estado->color_badge }}">{{ $ticket->estado->nombre }}</span>
                                    <span class="small text-muted ms-2">El estado se actualiza desde el detalle del ticket.</span>
                                @else
                                    <span class="badge bg-danger">Abierto</span>
                                    <span class="small text-muted ms-2">Estado inicial al registrar la incidencia.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="asunto" class="form-label fw-semibold small">Asunto</label>
                        <input type="text" name="asunto" id="asunto"
                               class="form-control @error('asunto') is-invalid @enderror"
                               value="{{ old('asunto', $ticket?->asunto) }}"
                               maxlength="180" placeholder="Resumen breve de la incidencia" required>
                        @error('asunto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <label for="descripcion" class="form-label fw-semibold small">Descripción de la incidencia</label>
                        <textarea name="descripcion" id="descripcion" rows="7"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  maxlength="5000" placeholder="Detalle qué ocurre, desde cuándo, en qué equipo o sistema y qué acciones ya intentó."
                                  required>{{ old('descripcion', $ticket?->descripcion) }}</textarea>
                        <div class="form-text">Mínimo 20 caracteres. No incluya contraseñas ni datos sensibles.</div>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-paperclip text-primary me-1"></i> Evidencias</h6>
                </div>
                <div class="card-body">
                    <label for="evidencias" class="form-label fw-semibold small">Adjuntar archivos</label>
                    <input type="file" name="evidencias[]" id="evidencias" multiple
                           class="form-control @error('evidencias') is-invalid @enderror @error('evidencias.*') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.pdf,.docx">
                    <div class="form-text">
                        Hasta 5 archivos. Formatos: JPG, PNG, PDF o DOCX. Máximo 2 MB por archivo.
                    </div>
                    @error('evidencias')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('evidencias.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if ($esEdicion && $ticket->evidencias->isNotEmpty())
                        <hr>
                        <p class="small fw-semibold mb-2">Evidencias registradas:</p>
                        <ul class="list-unstyled small mb-0">
                            @foreach ($ticket->evidencias as $evidencia)
                                <li class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-truncate me-2">
                                        <i class="bi bi-file-earmark text-secondary me-1"></i>{{ $evidencia->nombre_original }}
                                    </span>
                                    <a href="{{ route('tickets.evidence.download', [$ticket, $evidencia]) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card rag-box p-3">
                <h6 class="fw-bold text-primary"><i class="bi bi-lightbulb me-1"></i> Recomendaciones</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Describa el mensaje de error exacto si lo hay.</li>
                    <li>Indique el sistema, equipo o servicio afectado.</li>
                    <li>Adjunte capturas o documentos que ayuden al diagnóstico.</li>
                    <li>No adjunte archivos con contraseñas ni información reservada.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ $esEdicion ? route('tickets.show', $ticket) : route('tickets.index') }}" class="btn btn-outline-secondary">
            Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Registrar ticket' }}
        </button>
    </div>
</form>
