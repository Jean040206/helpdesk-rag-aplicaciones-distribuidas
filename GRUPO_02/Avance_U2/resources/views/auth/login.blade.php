<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | HelpDesk RAG - Grupo 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-6 col-md-8">
            <div class="card login-card shadow-lg p-4">
                <div class="text-center mb-4">
                    <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo HelpDesk RAG" class="auth-logo">
                    <h3 class="fw-bold mb-0 mt-2">HelpDesk RAG</h3>
                    <p class="text-muted small mb-0">Grupo 02 • Gestión Inteligente de Tickets y Soporte TI</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success py-2 small" role="alert">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">Correo Institucional</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="usuario@uns.edu.pe"
                                   autocomplete="username"
                                   maxlength="120"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold small">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Ingrese su contraseña"
                                   autocomplete="current-password"
                                   maxlength="255"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" data-toggle-password aria-label="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="remember">Recordarme</label>
                        </div>
                        <span class="small text-muted"><i class="bi bi-shield-lock me-1"></i>Acceso restringido</span>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar al Sistema
                    </button>
                </form>

                @if ($usuariosDemo->isNotEmpty())
                    <hr class="my-4">
                    <p class="small text-muted fw-bold mb-2">Cuentas de demostración (clic para completar el formulario):</p>
                    <div class="list-group small">
                        @foreach ($usuariosDemo as $demo)
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    data-demo-email="{{ $demo->email }}"
                                    data-demo-password="123456">
                                <span class="text-truncate me-2">{{ $demo->nombre_completo }}</span>
                                <span class="badge bg-secondary">{{ $demo->rol->nombre }}</span>
                            </button>
                        @endforeach
                    </div>
                    <p class="small text-muted mt-2 mb-0">Contraseña de demostración: <code>123456</code></p>
                @endif
            </div>

            <p class="text-center text-white-50 small mt-3 mb-0">
                © {{ now()->year }} Grupo 02 • Universidad Nacional del Santa
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
