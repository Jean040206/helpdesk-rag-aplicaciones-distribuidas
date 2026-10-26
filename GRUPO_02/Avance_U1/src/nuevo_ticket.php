<?php
require_once __DIR__ . '/includes/header.php';

$mensaje_exito = '';
$mensaje_error = '';
$id_creado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket'])) {
    $id_solicitante = intval($_POST['id_solicitante'] ?? 0);
    $id_servicio    = intval($_POST['id_servicio'] ?? 0);
    $id_categoria   = intval($_POST['id_categoria'] ?? 0);
    $id_prioridad   = intval($_POST['id_prioridad'] ?? 0);
    $asunto         = trim($_POST['asunto'] ?? '');
    $descripcion    = trim($_POST['descripcion'] ?? '');

    if ($id_solicitante <= 0 || $id_servicio <= 0 || $id_categoria <= 0 || $id_prioridad <= 0 || empty($asunto) || empty($descripcion)) {
        $mensaje_error = 'Por favor, complete todos los campos obligatorios marcados con (*).';
    } else {
        $pdo->beginTransaction();
        try {
            $total = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn() + 1;
            $codigo = sprintf("TKT-2026-%04d", $total);

            $stmt = $pdo->prepare("
                INSERT INTO tickets (codigo_ticket, id_solicitante, id_servicio, id_categoria, id_prioridad, id_estado, asunto, descripcion)
                VALUES (?, ?, ?, ?, ?, 1, ?, ?)
            ");
            $stmt->execute([$codigo, $id_solicitante, $id_servicio, $id_categoria, $id_prioridad, $asunto, $descripcion]);
            $id_creado = $pdo->lastInsertId();

            // Procesar archivo de evidencia adjunto si existe
            if (!empty($_FILES['evidencia']['name']) && $_FILES['evidencia']['error'] === UPLOAD_ERR_OK) {
                $dir = __DIR__ . '/uploads/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $nombreOrig = basename($_FILES['evidencia']['name']);
                $ext = strtolower(pathinfo($nombreOrig, PATHINFO_EXTENSION));
                $permitidos = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'txt'];

                if (in_array($ext, $permitidos)) {
                    $nombreSeguro = $codigo . '_' . time() . '.' . $ext;
                    $rutaDestino = 'uploads/' . $nombreSeguro;
                    if (move_uploaded_file($_FILES['evidencia']['tmp_name'], __DIR__ . '/' . $rutaDestino)) {
                        $tamanoKb = intval($_FILES['evidencia']['size'] / 1024);
                        $stmtEv = $pdo->prepare("
                            INSERT INTO ticket_evidencias (id_ticket, nombre_original, ruta_archivo, extension, tamano_kb)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmtEv->execute([$id_creado, $nombreOrig, $rutaDestino, $ext, $tamanoKb]);
                    }
                }
            }

            // Crear registro en el historial de estados
            $stmtHist = $pdo->prepare("
                INSERT INTO ticket_historial (id_ticket, id_estado_anterior, id_estado_nuevo, id_usuario_responsable, observacion)
                VALUES (?, NULL, 1, ?, 'Ticket registrado con estado inicial Abierto')
            ");
            $stmtHist->execute([$id_creado, $id_solicitante]);

            registrarAuditoria($pdo, 'Tickets', 'CREAR_TICKET', "Se registró el ticket $codigo ($asunto)");
            $pdo->commit();

            $mensaje_exito = "Ticket <strong>$codigo</strong> registrado exitosamente en la base de datos.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje_error = "Error al guardar el ticket: " . $e->getMessage();
        }
    }
}

$solicitantes = $pdo->query("SELECT id_usuario, nombre_completo, dependencia_oficina FROM usuarios ORDER BY nombre_completo")->fetchAll();
$servicios    = $pdo->query("SELECT id_servicio, nombre FROM servicios WHERE activo = 1")->fetchAll();
$categorias   = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$prioridades  = $pdo->query("SELECT id_prioridad, nombre, sla_horas FROM prioridades ORDER BY nivel_orden")->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold mb-0">Registrar Nuevo Ticket de Soporte</h3>
                <p class="text-muted small mb-0">Seleccione el servicio afectado, describa el problema y adjunte evidencias si es necesario</p>
            </div>
            <a href="tickets.php" class="btn btn-outline-secondary btn-sm">Ir a Bandeja de Tickets</a>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success d-flex justify-content-between align-items-center">
                <span><i class="bi bi-check-circle-fill me-2"></i><?= $mensaje_exito ?></span>
                <a href="detalle_ticket.php?id=<?= $id_creado ?>" class="btn btn-sm btn-success fw-bold">Ver Ticket Creado →</a>
            </div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="POST" action="nuevo_ticket.php" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Usuario Solicitante *</label>
                        <select name="id_solicitante" class="form-select" required>
                            <?php foreach ($solicitantes as $u): ?>
                                <option value="<?= $u['id_usuario'] ?>" <?= $u['id_usuario'] == $usuarioActual['id_usuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['nombre_completo']) ?> (<?= htmlspecialchars($u['dependencia_oficina']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Servicio Tecnológico Afectado *</label>
                        <select name="id_servicio" id="selectServicio" class="form-select" required>
                            <option value="">-- Seleccione servicio --</option>
                            <?php foreach ($servicios as $s): ?>
                                <option value="<?= $s['id_servicio'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Categoría de Incidencia *</label>
                        <select name="id_categoria" id="selectCategoria" class="form-select" required>
                            <option value="">-- Seleccione categoría --</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Prioridad *</label>
                        <select name="id_prioridad" id="selectPrioridad" class="form-select" required>
                            <?php foreach ($prioridades as $p): ?>
                                <option value="<?= $p['id_prioridad'] ?>" <?= $p['id_prioridad'] == 2 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?> (SLA: <?= $p['sla_horas'] ?>h)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Asunto o Resumen *</label>
                        <input type="text" name="asunto" id="inputAsunto" class="form-control"
                               placeholder="Ej. No puedo acceder al sistema académico" required>
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Descripción del Problema *</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="cargarEjemploGuia()">
                                <i class="bi bi-magic"></i> Cargar caso de ejemplo Grupo 02
                            </button>
                        </div>
                        <textarea name="descripcion" id="inputDescripcion" rows="4" class="form-control"
                                  placeholder="Describa el mensaje de error o el problema presentado..." required></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Adjuntar Evidencia (Imagen o Documento - Opcional)</label>
                        <input type="file" name="evidencia" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.docx,.txt">
                        <div class="form-text">Formatos permitidos: JPG, PNG, PDF, DOCX, TXT.</div>
                    </div>

                    <div class="col-12 pt-2">
                        <button type="submit" name="crear_ticket" class="btn btn-primary btn-lg w-100 fw-bold">
                            <i class="bi bi-save2-fill me-1"></i> Registrar Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>