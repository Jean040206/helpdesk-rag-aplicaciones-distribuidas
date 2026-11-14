@php($articulo = $articulo ?? null)
@php($esEdicion = ! is_null($articulo))

<form method="POST"
      action="{{ $esEdicion ? route('kb.update', $articulo) : route('kb.store') }}">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text text-primary me-1"></i> Contenido del artículo</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-semibold small">Título</label>
                        <input type="text" name="titulo" id="titulo"
                               class="form-control @error('titulo') is-invalid @enderror"
                               value="{{ old('titulo', $articulo?->titulo) }}"
                               maxlength="180" placeholder="Resumen claro del procedimiento o solución" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sintomas_problema" class="form-label fw-semibold small">Síntomas o problema</label>
                        <textarea name="sintomas_problema" id="sintomas_problema" rows="5"
                                  class="form-control @error('sintomas_problema') is-invalid @enderror"
                                  maxlength="5000" placeholder="Describa cómo se manifiesta la incidencia y en qué contexto ocurre."
                                  required>{{ old('sintomas_problema', $articulo?->sintomas_problema) }}</textarea>
                        <div class="form-text">Mínimo 20 caracteres.</div>
                        @error('sintomas_problema')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="pasos_solucion" class="form-label fw-semibold small">Pasos de solución</label>
                        <textarea name="pasos_solucion" id="pasos_solucion" rows="8"
                                  class="form-control @error('pasos_solucion') is-invalid @enderror"
                                  maxlength="10000" placeholder="Enumere los pasos verificables para diagnosticar y resolver la incidencia."
                                  required>{{ old('pasos_solucion', $articulo?->pasos_solucion) }}</textarea>
                        <div class="form-text">Mínimo 20 caracteres. Use una lista numerada para facilitar su aplicación.</div>
                        @error('pasos_solucion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="palabras_clave" class="form-label fw-semibold small">Palabras clave</label>
                        <input type="text" name="palabras_clave" id="palabras_clave"
                               class="form-control @error('palabras_clave') is-invalid @enderror"
                               value="{{ old('palabras_clave', $articulo?->palabras_clave) }}"
                               maxlength="200" placeholder="Ejemplo: correo institucional bloqueo contrasena">
                        <div class="form-text">Separe los términos con espacios. Se usan para la búsqueda FULLTEXT y el futuro asistente RAG.</div>
                        @error('palabras_clave')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-tags text-primary me-1"></i> Clasificación</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="id_servicio" class="form-label fw-semibold small">Servicio</label>
                        <select name="id_servicio" id="id_servicio"
                                class="form-select @error('id_servicio') is-invalid @enderror"
                                data-service-select required>
                            <option value="">Seleccione un servicio</option>
                            @foreach ($servicios as $servicio)
                                <option value="{{ $servicio->id_servicio }}"
                                    @selected((string) old('id_servicio', $articulo?->id_servicio) === (string) $servicio->id_servicio)>
                                    {{ $servicio->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_servicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="id_categoria" class="form-label fw-semibold small">Categoría</label>
                        <select name="id_categoria" id="id_categoria"
                                class="form-select @error('id_categoria') is-invalid @enderror"
                                data-category-select required>
                            <option value="">Seleccione un servicio primero</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria }}"
                                        data-service="{{ $categoria->id_servicio_default }}"
                                    @selected((string) old('id_categoria', $articulo?->id_categoria) === (string) $categoria->id_categoria)>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tipo_documento" class="form-label fw-semibold small">Tipo de documento</label>
                        <select name="tipo_documento" id="tipo_documento"
                                class="form-select @error('tipo_documento') is-invalid @enderror" required>
                            <option value="">Seleccione el tipo</option>
                            @foreach ($tiposDocumento as $valor => $etiqueta)
                                <option value="{{ $valor }}"
                                    @selected(old('tipo_documento', $articulo?->tipo_documento) === $valor)>
                                    {{ $etiqueta }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_documento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Código del artículo</label>
                        <div class="form-control bg-light">
                            @if ($esEdicion)
                                {{ $articulo->codigo_articulo }}
                            @else
                                <span class="text-muted">Se generará automáticamente al guardar</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input type="hidden" name="activo" value="0">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                               @checked((bool) old('activo', $articulo?->activo ?? true))>
                        <label class="form-check-label small" for="activo">
                            Artículo activo y disponible para consulta
                        </label>
                    </div>
                    <div class="form-text">Los artículos inactivos quedan ocultos para los técnicos.</div>
                </div>
            </div>

            <div class="card rag-box p-3">
                <h6 class="fw-bold text-primary"><i class="bi bi-lightbulb me-1"></i> Buenas prácticas</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Describa el problema con las palabras que usaría un usuario.</li>
                    <li>Incluya pasos verificables y reproducibles.</li>
                    <li>Registre palabras clave para mejorar la recuperación RAG.</li>
                    <li>Desactive el artículo cuando quede obsoleto.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ $esEdicion ? route('kb.show', $articulo) : route('kb.index') }}" class="btn btn-outline-secondary">
            Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> {{ $esEdicion ? 'Guardar cambios' : 'Crear artículo' }}
        </button>
    </div>
</form>
