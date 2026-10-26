<?php
require_once __DIR__ . '/includes/header.php';

$id_ticket = intval($_GET['id'] ?? 0);
if ($id_ticket <= 0) {
    echo "<div class='alert alert-warning'>Seleccione un ticket válido desde la <a href='tickets.php'>bandeja de tickets</a>.</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$alerta = '';

// 1. Asignación de Técnico por el Supervisor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_tecnico'])) {
    $id_tecnico = intval($_POST['id_tecnico']);
    $estAnt = $pdo->prepare("SELECT id_estado FROM tickets WHERE id_ticket = ?");
    $estAnt->execute([$id_ticket]);
    $estadoAnterior = $estAnt->fetchColumn();

    $nuevoEstado = ($estadoAnterior == 1) ? 2 : $estadoAnterior;

    $stmtUpd = $pdo->prepare("
        UPDATE tickets 
        SET id_tecnico_asignado = ?, id_supervisor_asignador = ?, id_estado = ?, fecha_asignacion = NOW()
        WHERE id_ticket = ?
    ");
    $stmtUpd->execute([$id_tecnico, $usuarioActual['id_usuario'], $nuevoEstado, $id_ticket]);

    $stmtHist = $pdo->prepare("
        INSERT INTO ticket_historial (id_ticket, id_estado_anterior, id_estado_nuevo, id_usuario_responsable, observacion)
        VALUES (?, ?, ?, ?, 'Supervisor asignó técnico responsable al ticket')
    ");
    $stmtHist->execute([$id_ticket, $estadoAnterior, $nuevoEstado, $usuarioActual['id_usuario']]);

    registrarAuditoria($pdo, 'Supervisión', 'ASIGNAR_TICKET', "Ticket ID #$id_ticket asignado al técnico ID #$id_tecnico");
    $alerta = 'Técnico asignado correctamente.';
}

// 2. Actualización Técnica: Estado, Diagnóstico, Solución y Comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_atencion'])) {
    $nuevo_estado = intval($_POST['id_estado']);
    $diagnostico  = trim($_POST['diagnostico_tecnico'] ?? '');
    $solucion     = trim($_POST['solucion_aplicada'] ?? '');
    $comentario   = trim($_POST['comentario_seguimiento'] ?? '');

    $estAnt = $pdo->prepare("SELECT id_estado FROM tickets WHERE id_ticket = ?");
    $estAnt->execute([$id_ticket]);
    $estadoAnterior = $estAnt->fetchColumn();

    $fechaResolucionSql = in_array($nuevo_estado, [4, 5]) ? ", fecha_resolucion = NOW()" : "";
    $stmtUpd = $pdo->prepare("
        UPDATE tickets 
        SET id_estado = ?, diagnostico_tecnico = ?, solucion_aplicada = ? $fechaResolucionSql
        WHERE id_ticket = ?
    ");
    $stmtUpd->execute([$nuevo_estado, $diagnostico, $solucion, $id_ticket]);

    if ($estadoAnterior != $nuevo_estado) {
        $stmtHist = $pdo->prepare("
            INSERT INTO ticket_historial (id_ticket, id_estado_anterior, id_estado_nuevo, id_usuario_responsable, observacion)
            VALUES (?, ?, ?, ?, 'Actualización de estado por el técnico')
        ");
        $stmtHist->execute([$id_ticket, $estadoAnterior, $nuevo_estado, $usuarioActual['id_usuario']]);
    }

    if ($comentario !== '') {
        $stmtCom = $pdo->prepare("
            INSERT INTO ticket_seguimiento (id_ticket, id_usuario, comentario, tipo_registro)
            VALUES (?, ?, ?, 'Comentario')
        ");
        $stmtCom->execute([$id_ticket, $usuarioActual['id_usuario'], $comentario]);
    }

    registrarAuditoria($pdo, 'Seguimiento', 'ACTUALIZAR_TICKET', "Actualización técnica en Ticket ID #$id_ticket");
    $alerta = 'Diagnóstico, estado y seguimiento actualizados en MySQL.';
}

// 3. Decisión del Técnico sobre la Recomendación del Asistente RAG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_decision_rag'])) {
    $id_articulo = intval($_POST['id_articulo_recuperado'] ?? 0) ?: null;
    $propuesta   = trim($_POST['solucion_recomendada'] ?? '');
    $decision    = $_POST['decision_tecnico'] ?? 'Aceptada';
    $cat_sug     = trim($_POST['categoria_sugerida'] ?? '');
    $prio_sug    = trim($_POST['prioridad_sugerida'] ?? '');
    $serv_sug    = trim($_POST['servicio_sugerido'] ?? '');

    $stmtRag = $pdo->prepare("
        INSERT INTO asistente_rag_evaluaciones 
        (id_ticket, id_articulo_recuperado, id_tecnico_evaluador, metodo_ia, modelo_utilizado, categoria_sugerida, prioridad_sugerida, servicio_sugerido, solucion_recomendada, decision_tecnico, tiempo_procesamiento_ms)
        VALUES (?, ?, ?, 'Recuperacion_KB', 'FullText_KB_Matcher_v1', ?, ?, ?, ?, ?, 38)
    ");
    $stmtRag->execute([$id_ticket, $id_articulo, $usuarioActual['id_usuario'], $cat_sug, $prio_sug, $serv_sug, $propuesta, $decision]);

    if ($decision === 'Aceptada') {
        $stmtCopy = $pdo->prepare("UPDATE tickets SET solucion_aplicada = ? WHERE id_ticket = ?");
        $stmtCopy->execute([$propuesta, $id_ticket]);
    }

    registrarAuditoria($pdo, 'Asistente_RAG', 'DECISION_TECNICO', "Recomendación marcada como $decision en Ticket ID #$id_ticket");
    $alerta = "Decisión del técnico ($decision) registrada correctamente.";
}

// Consultar información completa del ticket
$stmtTicket = $pdo->prepare("
    SELECT t.*, 
           u.nombre_completo AS solicitante, u.dependencia_oficina,
           tec.nombre_completo AS tecnico,
           s.nombre AS servicio,
           c.nombre AS categoria,
           p.nombre AS prioridad, p.color_badge AS color_prio,
           e.nombre AS estado, e.color_badge AS color_est
    FROM tickets t
    INNER JOIN usuarios u ON t.id_solicitante = u.id_usuario
    LEFT JOIN usuarios tec ON t.id_tecnico_asignado = tec.id_usuario
    INNER JOIN servicios s ON t.id_servicio = s.id_servicio
    INNER JOIN categorias c ON t.id_categoria = c.id_categoria
    INNER JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    INNER JOIN estados e ON t.id_estado = e.id_estado
    WHERE t.id_ticket = ?
");
$stmtTicket->execute([$id_ticket]);
$ticket = $stmtTicket->fetch();

if (!$ticket) {
    echo "<div class='alert alert-danger'>El ticket solicitado no existe. <a href='tickets.php'>Volver a la bandeja</a>.</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$evidencias = $pdo->prepare("SELECT * FROM ticket_evidencias WHERE id_ticket = ?");
$evidencias->execute([$id_ticket]);
$listaEvidencias = $evidencias->fetchAll();

$seguimientos = $pdo->prepare("
    SELECT ts.*, u.nombre_completo, r.nombre AS rol
    FROM ticket_seguimiento ts
    INNER JOIN usuarios u ON ts.id_usuario = u.id_usuario
    INNER JOIN roles r ON u.id_rol = r.id_rol
    WHERE ts.id_ticket = ? ORDER BY ts.id_seguimiento DESC
");
$seguimientos->execute([$id_ticket]);
$listaSeguimiento = $seguimientos->fetchAll();

$historial = $pdo->prepare("
    SELECT th.*, ea.nombre AS est_ant, en.nombre AS est_nue, u.nombre_completo
    FROM ticket_historial th
    LEFT JOIN estados ea ON th.id_estado_anterior = ea.id_estado
    INNER JOIN estados en ON th.id_estado_nuevo = en.id_estado
    INNER JOIN usuarios u ON th.id_usuario_responsable = u.id_usuario
    WHERE th.id_ticket = ? ORDER BY th.id_historial DESC
");
$historial->execute([$id_ticket]);
$listaHistorial = $historial->fetchAll();

$stmtKB = $pdo->prepare("
    SELECT * FROM base_conocimiento 
    WHERE id_servicio = ? OR id_categoria = ?
    ORDER BY (id_servicio = ?) DESC, (id_categoria = ?) DESC LIMIT 2
");
$stmtKB->execute([$ticket['id_servicio'], $ticket['id_categoria'], $ticket['id_servicio'], $ticket['id_categoria']]);
$articulosRecuperados = $stmtKB->fetchAll();

$evaluacionesPrevias = $pdo->prepare("
    SELECT * FROM asistente_rag_evaluaciones WHERE id_ticket = ? ORDER BY id_evaluacion_rag DESC
");
$evaluacionesPrevias->execute([$id_ticket]);
$listaEvaluaciones = $evaluacionesPrevias->fetchAll();

$tecnicos = $pdo->query("SELECT id_usuario, nombre_completo, especialidad_ti FROM usuarios WHERE id_rol = 3 AND activo = 1")->fetchAll();
$estados  = $pdo->query("SELECT * FROM estados")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <span class="badge bg-<?= htmlspecialchars($ticket['color_est']) ?> mb-1"><?= htmlspecialchars($ticket['estado']) ?></span>
        <span class="badge bg-<?= htmlspecialchars($ticket['color_prio']) ?> mb-1">Prioridad <?= htmlspecialchars($ticket['prioridad']) ?></span>
        <h3 class="fw-bold mb-0"><?= htmlspecialchars($ticket['codigo_ticket']) ?>: <?= htmlspecialchars($ticket['asunto']) ?></h3>
    </div>
    <a href="tickets.php" class="btn btn-outline-secondary btn-sm">← Volver a Tickets</a>
</div>

<?php if ($alerta): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($alerta) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Columna Izquierda: Datos del Ticket, Diagnóstico e Historial -->
    <div class="col-lg-7">
        <div class="card p-4 mb-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Información del Ticket</h6>
            <div class="row small mb-3">
                <div class="col-sm-6"><strong>Solicitante:</strong> <?= htmlspecialchars($ticket['solicitante']) ?></div>
                <div class="col-sm-6"><strong>Dependencia:</strong> <?= htmlspecialchars($ticket['dependencia_oficina']) ?></div>
                <div class="col-sm-6 mt-1"><strong>Servicio Afectado:</strong> <?= htmlspecialchars($ticket['servicio']) ?></div>
                <div class="col-sm-6 mt-1"><strong>Categoría:</strong> <?= htmlspecialchars($ticket['categoria']) ?></div>
            </div>
            <div class="p-3 bg-light rounded border mb-3">
                <div class="small fw-bold text-muted mb-1">Descripción del problema:</div>
                <div><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></div>
            </div>

            <?php if (!empty($listaEvidencias)): ?>
                <div class="small fw-bold mb-1">Evidencias Adjuntas:</div>
                <?php foreach ($listaEvidencias as $ev): ?>
                    <a href="<?= htmlspecialchars($ev['ruta_archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                        <i class="bi bi-paperclip"></i> <?= htmlspecialchars($ev['nombre_original']) ?> (<?= $ev['tamano_kb'] ?> KB)
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card p-4 mb-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Diagnóstico, Seguimiento y Solución del Técnico</h6>
            <form method="POST" action="detalle_ticket.php?id=<?= $id_ticket ?>">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Estado del Ticket</label>
                    <select name="id_estado" class="form-select">
                        <?php foreach ($estados as $e): ?>
                            <option value="<?= $e['id_estado'] ?>" <?= $e['id_estado'] == $ticket['id_estado'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nombre']) ?> — <?= htmlspecialchars($e['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Diagnóstico Técnico</label>
                    <textarea name="diagnostico_tecnico" rows="2" class="form-control" placeholder="Registre el diagnóstico técnico..."><?= htmlspecialchars($ticket['diagnostico_tecnico'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Solución Aplicada</label>
                    <textarea name="solucion_aplicada" rows="3" class="form-control" placeholder="Registre la solución final aplicada..."><?= htmlspecialchars($ticket['solucion_aplicada'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Agregar Comentario de Seguimiento</label>
                    <input type="text" name="comentario_seguimiento" class="form-control" placeholder="Ej. Se verificó el restablecimiento de acceso con el usuario...">
                </div>
                <button type="submit" name="actualizar_atencion" class="btn btn-success fw-bold">
                    <i class="bi bi-check2-square me-1"></i> Guardar Cambios del Ticket
                </button>
            </form>
        </div>

        <div class="card p-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Seguimiento e Historial de Estados</h6>
            <?php foreach ($listaSeguimiento as $seg): ?>
                <div class="border-start border-primary border-3 ps-3 mb-3 small">
                    <div class="fw-bold"><?= htmlspecialchars($seg['nombre_completo']) ?> <span class="badge bg-secondary"><?= htmlspecialchars($seg['rol']) ?></span></div>
                    <div class="text-dark my-1"><?= htmlspecialchars($seg['comentario']) ?></div>
                    <small class="text-muted"><?= $seg['fecha_registro'] ?></small>
                </div>
            <?php endforeach; ?>

            <h6 class="fw-bold small text-muted mt-2">Historial de Transiciones:</h6>
            <ul class="list-group list-group-flush small">
                <?php foreach ($listaHistorial as $h): ?>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span>
                            <strong><?= htmlspecialchars($h['est_ant'] ?? 'Inicio') ?> → <?= htmlspecialchars($h['est_nue']) ?></strong>:
                            <?= htmlspecialchars($h['observacion']) ?> (<?= htmlspecialchars($h['nombre_completo']) ?>)
                        </span>
                        <span class="text-muted"><?= date('d/m H:i', strtotime($h['fecha_cambio'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Columna Derecha: Asignación de Técnico y Asistente RAG -->
    <div class="col-lg-5">
        <div class="card p-4 mb-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person-gear text-primary me-1"></i> Asignación de Técnico (Supervisor)</h6>
            <form method="POST" action="detalle_ticket.php?id=<?= $id_ticket ?>">
                <div class="mb-2 small">
                    <strong>Responsable Actual:</strong> <?= htmlspecialchars($ticket['tecnico'] ?? 'Sin asignar') ?>
                </div>
                <select name="id_tecnico" class="form-select mb-3" required>
                    <option value="">-- Seleccionar técnico especialista --</option>
                    <?php foreach ($tecnicos as $tec): ?>
                        <option value="<?= $tec['id_usuario'] ?>" <?= $tec['id_usuario'] == $ticket['id_tecnico_asignado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tec['nombre_completo']) ?> (<?= htmlspecialchars($tec['especialidad_ti']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="asignar_tecnico" class="btn btn-dark btn-sm w-100 fw-semibold">
                    Asignar Técnico al Ticket
                </button>
            </form>
        </div>

        <div class="card rag-box p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-stars me-1"></i> Asistente RAG • Propuesta de Solución</h6>
                <a href="base_conocimiento.php" class="badge bg-primary text-decoration-none">Ver KB</a>
            </div>
            <p class="small text-muted">
                Procedimientos recuperados desde la Base de Conocimiento para apoyar al técnico. La decisión final queda registrada:
            </p>

            <?php foreach ($articulosRecuperados as $kb): ?>
                <div class="bg-white p-3 rounded border mb-3 small">
                    <div class="fw-bold text-primary"><?= htmlspecialchars($kb['codigo_articulo']) ?> • <?= htmlspecialchars($kb['tipo_documento']) ?></div>
                    <div class="fw-bold mt-1"><?= htmlspecialchars($kb['titulo']) ?></div>
                    <div class="text-muted mt-1 pre-wrap-text"><?= htmlspecialchars($kb['pasos_solucion']) ?></div>

                    <form method="POST" action="detalle_ticket.php?id=<?= $id_ticket ?>" class="mt-3 pt-2 border-top">
                        <input type="hidden" name="id_articulo_recuperado" value="<?= $kb['id_articulo'] ?>">
                        <input type="hidden" name="categoria_sugerida" value="<?= htmlspecialchars($ticket['categoria']) ?>">
                        <input type="hidden" name="prioridad_sugerida" value="<?= htmlspecialchars($ticket['prioridad']) ?>">
                        <input type="hidden" name="servicio_sugerido" value="<?= htmlspecialchars($ticket['servicio']) ?>">
                        <input type="hidden" name="solucion_recomendada" value="<?= htmlspecialchars($kb['pasos_solucion']) ?>">
                        <input type="hidden" name="decision_tecnico" value="Aceptada">

                        <label class="form-label fw-bold small mb-1">Evaluar recomendación:</label>
                        <div class="d-flex gap-2">
                            <button type="submit" name="registrar_decision_rag" value="1" onclick="fijarDecisionRag(this, 'Aceptada')" class="btn btn-sm btn-success flex-fill">Aceptar</button>
                            <button type="submit" name="registrar_decision_rag" value="1" onclick="fijarDecisionRag(this, 'Modificada')" class="btn btn-sm btn-warning flex-fill">Modificar</button>
                            <button type="submit" name="registrar_decision_rag" value="1" onclick="fijarDecisionRag(this, 'Descartada')" class="btn btn-sm btn-outline-danger flex-fill">Descartar</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($listaEvaluaciones)): ?>
                <div class="small fw-bold text-dark mt-2 mb-1">Decisiones registradas en este ticket:</div>
                <?php foreach ($listaEvaluaciones as $ev): ?>
                    <div class="bg-white p-2 rounded border mb-1 small d-flex justify-content-between">
                        <span>Resultado: <strong><?= htmlspecialchars($ev['decision_tecnico']) ?></strong> (<?= $ev['tiempo_procesamiento_ms'] ?> ms)</span>
                        <span class="text-muted"><?= date('d/m H:i', strtotime($ev['fecha_evento'])) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>