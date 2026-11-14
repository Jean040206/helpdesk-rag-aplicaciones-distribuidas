<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($tituloPagina = strip_tags(html_entity_decode(\Illuminate\Support\Facades\View::yieldContent('title', 'Panel'), ENT_QUOTES | ENT_HTML5, 'UTF-8')))
    <title>{{ \Illuminate\Support\Str::limit($tituloPagina, 120, '') }} | HelpDesk RAG - Grupo 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
</head>
<body>
@php($usuario = auth()->user())
@php($colorRol = match ($usuario->rol->nombre) {
    'Administrador' => 'danger',
    'Supervisor' => 'warning',
    'Tecnico' => 'info',
    default => 'success',
})
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
            <a href="{{ route('dashboard') }}" class="d-block mb-4 pb-2 border-bottom border-secondary text-decoration-none">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo HelpDesk RAG" class="sidebar-logo">
            </a>

            <div class="mb-3">
                <small class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Navegación</small>
                <ul class="nav flex-column mt-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Inicio / Dashboard
                        </a>
                    </li>

                    @if (Route::has('tickets.create'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tickets.create') ? 'active' : '' }}" href="{{ route('tickets.create') }}">
                                <i class="bi bi-plus-circle-fill"></i> Nuevo Ticket
                            </a>
                        </li>
                    @endif

                    @if (Route::has('tickets.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tickets.*') && ! request()->routeIs('tickets.create') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                                <i class="bi bi-ticket-detailed-fill"></i> Tickets
                            </a>
                        </li>
                    @endif

                    @can('viewAny', App\Models\BaseConocimiento::class)
                        @if (Route::has('kb.index'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('kb.*') ? 'active' : '' }}" href="{{ route('kb.index') }}">
                                    <i class="bi bi-journal-code"></i> Base Conocimiento
                                </a>
                            </li>
                        @endif
                    @endcan

                    @can('viewAny', App\Models\EvaluacionRag::class)
                        @if (Route::has('rag.index'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('rag.*') ? 'active' : '' }}" href="{{ route('rag.index') }}">
                                    <i class="bi bi-robot"></i> Evaluaciones RAG
                                </a>
                            </li>
                        @endif
                    @endcan

                    @if (Route::has('admin.index') && $usuario->hasRole('Administrador'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.index') }}">
                                <i class="bi bi-people-fill"></i> Usuarios y Roles
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="sidebar-user pt-3 border-top border-secondary">
                <small class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Usuario activo</small>
                <div class="mt-2 p-2 rounded bg-dark bg-opacity-50">
                    <div class="fw-semibold small text-white">{{ $usuario->nombre_completo }}</div>
                    <span class="badge bg-{{ $colorRol }} role-user-badge mt-1">{{ $usuario->rol->nombre }}</span>
                    <div class="small text-white-50 mt-1">{{ $usuario->codigo_institucional }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-0 main-content">
            <div class="topbar px-4 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="text-muted small">
                        <i class="bi bi-hdd-network text-primary me-1"></i>
                        Plataforma web para gestión inteligente de tickets y soporte TI
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $colorRol }}">{{ $usuario->rol->nombre }}</span>
                    <span class="small text-muted d-none d-sm-inline">{{ $usuario->nombre_completo }}</span>
                    <span class="small text-muted border-start ps-2">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="p-4">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
