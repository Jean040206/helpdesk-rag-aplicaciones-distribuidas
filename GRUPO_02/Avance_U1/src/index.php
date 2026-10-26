<?php
require_once __DIR__ . '/includes/header.php';

// Indicadores principales desde MySQL
$kpis = $pdo->query("
    SELECT 
        COUNT(*) AS total_tickets,
        SUM(CASE WHEN id_estado = 1 THEN 1 ELSE 0 END) AS abiertos,
        SUM(CASE WHEN id_estado IN (2, 3) THEN 1 ELSE 0 END) AS en_atencion,
        SUM(CASE WHEN id_estado IN (4, 5) THEN 1 ELSE 0 END) AS resueltos
    FROM tickets
")->fetch();

$totalKB = $pdo->query("SELECT COUNT(*) FROM base_conocimiento WHERE activo = 1")->fetchColumn();
$evalRAG = $pdo->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN decision_tecnico = 'Aceptada' THEN 1 ELSE 0 END) AS aceptadas
    FROM asistente_rag_evaluaciones
")->fetch();

// Distribución por Servicio Afectado
$porServicio = $pdo->query("
    SELECT s.nombre, COUNT(t.id_ticket) AS total
    FROM servicios s
    LEFT JOIN tickets t ON s.id_servicio = t.id_servicio
    GROUP BY s.id_servicio, s.nombre
    ORDER BY total DESC
")->fetchAll();

// Últimos tickets registrados
$ultimosTickets = $pdo->query("
    SELECT t.id_ticket, t.codigo_ticket, t.asunto, t.fecha_registro,
           u.nombre_completo AS solicitante, s.nombre AS servicio,
           p.nombre AS prioridad, p.color_badge AS color_prio,
           e.nombre AS estado, e.color_badge AS color_est
    FROM tickets t
    INNER JOIN usuarios u ON t.id_solicitante = u.id_usuario
    INNER JOIN servicios s ON t.id_servicio = s.id_servicio
    INNER JOIN prioridades p ON t.id_prioridad = p.id_prioridad
    INNER JOIN estados e ON t.id_estado = e.id_estado
    ORDER BY t.id_ticket DESC LIMIT 5
")->fetchAll();

// Últimos registros de auditoría
$ultimosLogs = $pdo->query("
    SELECT a.modulo, a.accion, a.descripcion_evento, a.fecha_evento, u.nombre_completo
    FROM auditoria a
    LEFT JOIN usuarios u ON a.id_usuario = u.id_usuario
    ORDER BY a.id_auditoria DESC LIMIT 5
")->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Dashboard de Soporte TI y Asistencia RAG</h3>
        <p class="text-muted small mb-0">Monitoreo en tiempo real de tickets, servicios afectados, base de conocimiento y auditoría</p>
    </div>
    <div class="d-flex gap-2 mt-2 mt-md-0">
        <a href="nuevo_ticket.php" class="btn btn-primary fw-semibold">
            <i class="bi bi-plus-lg"></i> Nuevo Ticket
        </a>
        <a href="base_conocimiento.php" class="btn btn-outline-dark">
            <i class="bi bi-journal-code"></i> Base de Conocimiento
        </a>
    </div>
</div>

<!-- Tarjetas KPI -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card kpi-card p-3 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">TOTAL TICKETS</div>
                    <h2 class="fw-bold mb-0"><?= intval($kpis['total_tickets']) ?></h2>
                </div>
                <i class="bi bi-inboxes-fill text-primary fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card p-3 border-start border-danger border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">ABIERTOS</div>
                    <h2 class="fw-bold text-danger mb-0"><?= intval($kpis['abiertos']) ?></h2>
                </div>
                <i class="bi bi-exclamation-octagon-fill text-danger fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card p-3 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">EN ATENCIÓN</div>
                    <h2 class="fw-bold text-warning mb-0"><?= intval($kpis['en_atencion']) ?></h2>
                </div>
                <i class="bi bi-tools text-warning fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card kpi-card p-3 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">DOCUMENTOS RAG</div>
                    <h2 class="fw-bold text-success mb-0"><?= intval($totalKB) ?></h2>
                </div>
                <i class="bi bi-journal-check text-success fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Izquierda: Últimos Tickets y Auditoría -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-1"></i> Últimas Incidencias Registradas</h6>
                <a href="tickets.php" class="btn btn-sm btn-outline-primary">Ver Bandeja Completa</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Código</th>
                            <th>Servicio y Asunto</th>
                            <th>Solicitante</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosTickets as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary small"><?= htmlspecialchars($t['codigo_ticket']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($t['servicio']) ?></span>
                                <div class="fw-semibold small mt-1"><?= htmlspecialchars($t['asunto']) ?></div>
                            </td>
                            <td class="small"><?= htmlspecialchars($t['solicitante']) ?></td>
                            <td><span class="badge bg-<?= htmlspecialchars($t['color_prio']) ?>"><?= htmlspecialchars($t['prioridad']) ?></span></td>
                            <td><span class="badge bg-<?= htmlspecialchars($t['color_est']) ?>"><?= htmlspecialchars($t['estado']) ?></span></td>
                            <td>
                                <a href="detalle_ticket.php?id=<?= $t['id_ticket'] ?>" class="btn btn-sm btn-primary">Abrir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Registro de Auditoría Reciente -->
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check text-primary me-1"></i> Bitácora de Auditoría Reciente</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Módulo / Acción</th>
                            <th>Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimosLogs as $log): ?>
                        <tr>
                            <td class="text-muted"><?= date('d/m H:i', strtotime($log['fecha_evento'])) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($log['nombre_completo'] ?? 'Sistema') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($log['modulo']) ?> • <?= htmlspecialchars($log['accion']) ?></span></td>
                            <td><?= htmlspecialchars($log['descripcion_evento']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Distribución por Servicio y Métricas del Asistente RAG -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart-fill text-primary me-1"></i> Incidencias por Servicio TI</h6>
            </div>
            <div class="card-body">
                <?php foreach ($porServicio as $ps): ?>
                    <div class="d-flex justify-content-between small mb-1">
                        <span><?= htmlspecialchars($ps['nombre']) ?></span>
                        <span class="fw-bold"><?= intval($ps['total']) ?></span>
                    </div>
                    <div class="progress mb-3" style="height: 7px;">
                        <div class="progress-bar bg-primary" style="width: <?= max(10, intval($ps['total']) * 25) ?>%"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card rag-box p-3">
            <h6 class="fw-bold text-primary"><i class="bi bi-robot me-1"></i> Evaluación del Asistente RAG</h6>
            <p class="small text-muted mb-2">
                Seguimiento de recomendaciones propuestas a los técnicos desde la Base de Conocimiento:
            </p>
            <div class="d-flex justify-content-between small bg-white p-2 rounded border mb-2">
                <span>Recomendaciones Evaluadas:</span>
                <strong><?= intval($evalRAG['total']) ?></strong>
            </div>
            <div class="d-flex justify-content-between small bg-white p-2 rounded border">
                <span>Recomendaciones Aceptadas:</span>
                <strong class="text-success"><?= intval($evalRAG['aceptadas']) ?></strong>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>