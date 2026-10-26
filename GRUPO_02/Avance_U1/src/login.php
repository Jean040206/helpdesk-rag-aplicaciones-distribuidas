<?php
require_once __DIR__ . '/config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("
        SELECT u.*, r.nombre AS rol_nombre 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.email = ? AND u.activo = 1 LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && ($password === $user['password_hash'] || password_verify($password, $user['password_hash']))) {
        $_SESSION['usuario'] = $user;
        registrarAuditoria($pdo, 'Autenticación', 'LOGIN_EXITOSO', 'Ingreso al sistema: ' . $user['email']);
        header("Location: index.php");
        exit;
    } else {
        $error = 'Credenciales incorrectas. Verifique su correo institucional y contraseña.';
    }
}

$usuariosDemo = $pdo->query("
    SELECT u.email, u.nombre_completo, r.nombre AS rol 
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
    <title>Iniciar Sesión | HelpDesk RAG - Grupo 02</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body class="login-bg d-flex align-items-center">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg rounded-4 p-4">
                <div class="text-center mb-4">
                    <img src="assets/img/logo-helpdesk.svg" alt="Logo" width="54" height="54" class="mb-2">
                    <h3 class="fw-bold mb-0">HelpDesk RAG</h3>
                    <p class="text-muted small mb-0">Grupo 02 • Gestión Inteligente de Tickets y Soporte TI</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Correo Institucional</label>
                        <input type="email" name="email" id="emailInput" class="form-control" value="rsilva@uns.edu.pe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Contraseña</label>
                        <input type="password" name="password" class="form-control" value="123456" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar al Sistema
                    </button>
                </form>

                <hr class="my-4">
                <p class="small text-muted fw-bold mb-2">Cuentas de Demostración (Clic para cargar):</p>
                <div class="list-group small">
                    <?php foreach ($usuariosDemo as $ud): ?>
                        <button type="button"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                onclick="seleccionarCuentaDemo('<?= htmlspecialchars($ud['email']) ?>')">
                            <span><?= htmlspecialchars($ud['nombre_completo']) ?></span>
                            <span class="badge bg-secondary"><?= htmlspecialchars($ud['rol']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="small text-decoration-none">← Ir al Panel Principal</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>