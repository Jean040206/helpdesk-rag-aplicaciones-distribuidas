<?php
require_once __DIR__ . '/../config/db.php';

// Cambio rápido de perfil en vivo para demostración
if (isset($_GET['cambiar_perfil'])) {
    $id_nuevo = intval($_GET['cambiar_perfil']);
    $stmtUser = $pdo->prepare("
        SELECT u.*, r.nombre AS rol_nombre 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.id_usuario = ? AND u.activo = 1
    ");
    $stmtUser->execute([$id_nuevo]);
    if ($nuevoUser = $stmtUser->fetch()) {
        $_SESSION['usuario'] = $nuevoUser;
        registrarAuditoria($pdo, 'Autenticación', 'CAMBIO_PERFIL', 'Sesión cambiada a: ' . $nuevoUser['nombre_completo']);
    }
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $redirect");
    exit;
}

$usuarioActual = $_SESSION['usuario'];
$paginaActual  = basename($_SERVER['PHP_SELF']);
$perfilesDemo  = $pdo->query("
    SELECT u.id_usuario, u.nombre_completo, r.nombre AS rol 
    FROM usuarios u 
    INNER JOIN roles r ON u.id_rol = r.id_rol 
    ORDER BY u.id_rol
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk RAG | Grupo 02 - Soporte TI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar de Navegación -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar p-3">
            <a href="index.php" class="d-flex align-items-center mb-4 pb-2 border-bottom border-secondary text-decoration-none text-white">
                <img src="assets/img/logo-helpdesk.svg" alt="Logo HelpDesk RAG" class="brand-logo me-2">
                <div>
                    <div class="fw-bold fs-6 lh-1">HelpDesk RAG</div>
                    <small class="text-info" style="font-size: 0.72rem;">GRUPO 02 • UNS</small>
                </div>
            </a>

            <div class="mb-3">
                <small class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Navegación</small>
                <ul class="nav flex-column mt-2">
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual === 'index.php' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-speedometer2"></i> Inicio / Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual === 'nuevo_ticket.php' ? 'active' : '' ?>" href="nuevo_ticket.php">
                            <i class="bi bi-plus-circle-fill"></i> Nuevo Ticket
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($paginaActual === 'tickets.php' || $paginaActual === 'detalle_ticket.php') ? 'active' : '' ?>" href="tickets.php">
                            <i class="bi bi-ticket-detailed-fill"></i> Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $paginaActual === 'base_conocimiento.php' ? 'active' : '' ?>" href="base_conocimiento.php">
                            <i class="bi bi-journal-code"></i> Base Conocimiento
                        </a>
                    </li>
                </ul>
            </div>

            <div class="mt-4 pt-3 border-top border-secondary">
                <small class="text-uppercase text-secondary fw-bold" style="font-size: 0.7rem;">Usuario Activo</small>
                <div class="mt-2 p-2 rounded bg-dark bg-opacity-50">
                    <div class="fw-semibold small text-white"><?= htmlspecialchars($usuarioActual['nombre_completo']) ?></div>
                    <span class="badge bg-info text-dark mt-1"><?= htmlspecialchars($usuarioActual['rol_nombre']) ?></span>
                </div>
                <a href="login.php" class="btn btn-outline-light btn-sm w-100 mt-2">
                    <i class="bi bi-box-arrow-right"></i> Cambiar Cuenta / Login
                </a>
            </div>
        </nav>

        <!-- Contenedor Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-0">
            <div class="topbar px-4 py-2 d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <span class="text-muted small">
                        <i class="bi bi-hdd-network text-primary me-1"></i>
                        Plataforma web para gestión inteligente de tickets y soporte TI
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Rol activo:</span>
                    <form method="GET" class="d-inline">
                        <?php if (isset($_GET['id'])): ?>
                            <input type="hidden" name="id" value="<?= intval($_GET['id']) ?>">
                        <?php endif; ?>
                        <select name="cambiar_perfil" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php foreach ($perfilesDemo as $pf): ?>
                                <option value="<?= $pf['id_usuario'] ?>" <?= $pf['id_usuario'] == $usuarioActual['id_usuario'] ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($pf['rol']) ?>] <?= htmlspecialchars($pf['nombre_completo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="p-4"></div>