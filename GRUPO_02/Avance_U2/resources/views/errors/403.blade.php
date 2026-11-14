<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado | HelpDesk RAG - Grupo 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card login-card shadow-lg p-4 text-center">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo HelpDesk RAG" class="auth-logo mx-auto mb-2">
                <div class="display-5 text-danger mb-2"><i class="bi bi-shield-lock-fill"></i></div>
                <h3 class="fw-bold mb-1">Acceso denegado</h3>
                <p class="text-muted mb-4">
                    Tu rol actual no tiene permisos para ingresar a esta sección. El intento fue registrado en la bitácora de auditoría.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="bi bi-speedometer2 me-1"></i> Volver al dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                        </a>
                    @endauth
                </div>
                <p class="small text-muted mt-4 mb-0">Código de error: 403 · Permisos insuficientes</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
