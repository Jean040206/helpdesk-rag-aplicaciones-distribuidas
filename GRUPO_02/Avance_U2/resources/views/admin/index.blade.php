@extends('layouts.app')

@section('title', 'Usuarios y Roles')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-0">Usuarios y Roles</h3>
        <p class="text-muted small mb-0">Consulta de cuentas habilitadas y nivel de acceso institucional.</p>
    </div>
    <span class="badge bg-danger fs-6"><i class="bi bi-shield-lock me-1"></i> Acceso exclusivo de Administración</span>
</div>

<div class="row g-3 mb-4">
    @foreach ($roles as $rol)
        @php($color = match ($rol->nombre) {
            'Administrador' => 'danger',
            'Supervisor' => 'warning',
            'Tecnico' => 'info',
            default => 'success',
        })
        <div class="col-sm-6 col-xl-3">
            <div class="card kpi-card p-3 border-start border-{{ $color }} border-4 h-100">
                <div class="text-muted small fw-semibold text-uppercase">{{ $rol->nombre }}</div>
                <div class="d-flex justify-content-between align-items-end">
                    <h2 class="fw-bold mb-0">{{ $rol->usuarios_count }}</h2>
                    <i class="bi bi-person-badge text-{{ $color }} fs-2 opacity-50"></i>
                </div>
                <div class="text-muted mt-1" style="font-size: 0.75rem;">{{ $rol->descripcion }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill text-primary me-1"></i> Cuentas registradas</h6>
        <span class="badge bg-light text-dark border">{{ $usuarios->count() }} usuarios</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Código</th>
                    <th>Nombre completo</th>
                    <th>Correo institucional</th>
                    <th>Dependencia / Oficina</th>
                    <th>Rol</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    @php($color = match ($usuario->rol->nombre) {
                        'Administrador' => 'danger',
                        'Supervisor' => 'warning',
                        'Tecnico' => 'info',
                        default => 'success',
                    })
                    <tr>
                        <td class="fw-semibold small">{{ $usuario->codigo_institucional }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $usuario->nombre_completo }}</div>
                            @if ($usuario->especialidad_ti)
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $usuario->especialidad_ti }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $usuario->email }}</td>
                        <td class="small">{{ $usuario->dependencia_oficina }}</td>
                        <td><span class="badge bg-{{ $color }}">{{ $usuario->rol->nombre }}</span></td>
                        <td>
                            @if ($usuario->activo)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Activo</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i>Inactivo</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Las cuentas se administran desde el módulo de seguridad. La desactivación de un usuario invalida su sesión en la siguiente petición.
    </div>
</div>
@endsection
