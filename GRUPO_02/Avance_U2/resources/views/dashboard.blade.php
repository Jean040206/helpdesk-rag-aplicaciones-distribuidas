@extends('layouts.app')

@php($colorRol = match ($usuario->rol->nombre) {
    'Administrador' => 'danger',
    'Supervisor' => 'warning',
    'Tecnico' => 'info',
    default => 'success',
})
@php($alcance = match (true) {
    $usuario->hasRole('Solicitante') => 'Vista de las incidencias que has registrado en la plataforma.',
    $usuario->hasRole('Tecnico') => 'Vista de los tickets asignados a tu especialidad de soporte.',
    $usuario->hasRole('Supervisor') => 'Vista general para revisión y asignación de incidencias del equipo.',
    default => 'Vista general de la plataforma y control de accesos.',
})

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Dashboard de Soporte TI</h3>
        <p class="text-muted small mb-0">{{ $alcance }}</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-{{ $colorRol }} fs-6">{{ $usuario->rol->nombre }}</span>
        @if (Route::has('tickets.create'))
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Ticket
            </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3 border-start border-primary border-4 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">TOTAL TICKETS</div>
                    <h2 class="fw-bold mb-0">{{ $resumen['total'] }}</h2>
                </div>
                <i class="bi bi-inboxes-fill text-primary fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3 border-start border-danger border-4 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">ABIERTOS</div>
                    <h2 class="fw-bold text-danger mb-0">{{ $resumen['abiertos'] }}</h2>
                </div>
                <i class="bi bi-exclamation-octagon-fill text-danger fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3 border-start border-warning border-4 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">EN ATENCIÓN</div>
                    <h2 class="fw-bold text-warning mb-0">{{ $resumen['en_atencion'] }}</h2>
                </div>
                <i class="bi bi-tools text-warning fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card p-3 border-start border-success border-4 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">RESUELTOS / CERRADOS</div>
                    <h2 class="fw-bold text-success mb-0">{{ $resumen['resueltos'] }}</h2>
                </div>
                <i class="bi bi-check2-circle text-success fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="{{ $mostrarPanelLateral ? 'col-lg-8' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history text-primary me-1"></i> Últimas incidencias registradas
                </h6>
                @if (Route::has('tickets.index'))
                    <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-primary">Ver bandeja completa</a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Código</th>
                            <th>Servicio y asunto</th>
                            <th>Solicitante</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ultimosTickets as $ticket)
                            <tr>
                                <td class="fw-bold text-primary small">{{ $ticket->codigo_ticket }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $ticket->servicio->nombre }}</span>
                                    <div class="fw-semibold small mt-1">{{ $ticket->asunto }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $ticket->categoria->nombre }}
                                        @if ($ticket->tecnicoAsignado)
                                            • Técnico: {{ $ticket->tecnicoAsignado->nombre_completo }}
                                        @endif
                                    </div>
                                </td>
                                <td class="small">{{ $ticket->solicitante->nombre_completo }}</td>
                                <td>
                                    <span class="badge bg-{{ $ticket->prioridad->color_badge }}">{{ $ticket->prioridad->nombre }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $ticket->estado->color_badge }}">{{ $ticket->estado->nombre }}</span>
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $ticket->fecha_registro?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                        Abrir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                    No hay tickets registrados para tu perfil.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($mostrarPanelLateral)
        <div class="col-lg-4">
            @if ($totalArticulos !== null)
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-journal-check text-primary me-1"></i> Base de conocimiento</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span class="text-muted">Artículos activos</span>
                            <strong class="fs-5">{{ $totalArticulos }}</strong>
                        </div>
                        <p class="small text-muted mb-0">
                            Documentos y procedimientos disponibles para la recuperación de conocimiento del asistente.
                        </p>
                        @can('viewAny', App\Models\BaseConocimiento::class)
                            <a href="{{ route('kb.index') }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                                <i class="bi bi-journal-code me-1"></i> Consultar base de conocimiento
                            </a>
                        @endcan
                    </div>
                </div>
            @endif

            @if ($ragEvaluadas !== null)
                <div class="card rag-box p-3 mb-4">
                    <h6 class="fw-bold text-primary"><i class="bi bi-robot me-1"></i> Asistente RAG</h6>
                    <p class="small text-muted mb-2">
                        Seguimiento de las recomendaciones evaluadas por el equipo técnico.
                    </p>
                    <div class="d-flex justify-content-between small bg-white p-2 rounded border mb-2">
                        <span>Recomendaciones evaluadas</span>
                        <strong>{{ $ragEvaluadas }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small bg-white p-2 rounded border">
                        <span>Recomendaciones aceptadas</span>
                        <strong class="text-success">{{ $ragAceptadas }}</strong>
                    </div>
                </div>
            @endif

            @if ($usuario->hasRole('Administrador'))
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill text-primary me-1"></i> Administración</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span class="text-muted">Usuarios registrados</span>
                            <strong class="fs-5">{{ $totalUsuarios }}</strong>
                        </div>
                        <a href="{{ route('admin.index') }}" class="btn btn-sm btn-outline-primary w-100">
                            Revisar usuarios y roles
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<div class="card mt-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="small text-muted">
            <i class="bi bi-shield-check text-primary me-1"></i>
            Sesión autenticada como <strong>{{ $usuario->email }}</strong> con rol <strong>{{ $usuario->rol->nombre }}</strong>.
        </div>
        <div class="small text-muted">
            Último ingreso registrado en auditoría • {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</div>
@endsection
