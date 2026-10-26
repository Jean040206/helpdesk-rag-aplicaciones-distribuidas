<?php
require_once __DIR__ . '/includes/header.php';

$filtroEstado    = intval($_GET['estado'] ?? 0);
$filtroPrioridad = intval($_GET['prioridad'] ?? 0);
$filtroServicio  = intval($_GET['servicio'] ?? 0);
$busqueda        = trim($_GET['q'] ?? '');

$condiciones = ["1=1"];
$params = [];

if ($filtroEstado > 0) {
    $condiciones[] = "t.id_estado = ?";
    $params[] = $filtroEstado;
}
if ($filtroPrioridad > 0) {
    $condiciones[] = "t.id_prioridad = ?";
    $params[] = $filtroPrioridad;
}
if ($filtroServicio > 0) {
    $condiciones[] = "t.id_servicio = ?";
    $params[] = $filtroServicio;
}
if ($busqueda !== '') {
    $condiciones[] = "(t.codigo_ticket LIKE ? OR t.asunto LIKE ? OR t.descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sqlWhere = implode(" AND ", $condiciones);
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.nombre_completo AS solicitante,
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
    WHERE $sqlWhere
    ORDER BY t.id_ticket DESC
");
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$estados     = $pdo->query("SELECT * FROM estados")->fetchAll();
$prioridades = $pdo->query("SELECT * FROM prioridades ORDER BY nivel_orden")->fetchAll();
$servicios   = $pdo->query("SELECT * FROM servicios")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Bandeja General de Tickets</h3>
        <p class="text-muted small mb-0">Listado, búsqueda y filtrado de incidencias tecnológicas registradas</p>
    </div>
    <a href="nuevo_ticket.php" class="btn btn-primary fw-semibold">
        <i class="bi bi-plus-lg"></i> Nuevo Ticket
    </a>
</div>

<!-- Filtros -->
<div class="card p-3 mb-4">
    <form method="GET" action="tickets.php" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Buscar por Código o Texto</label>
            <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" class="form-control form-control-sm" placeholder="Ej. TKT-2026 o académico...">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1">Servicio Afectado</label>
            <select name="servicio" class="form-select form-select-sm">
                <option value="0">-- Todos los servicios --</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= $s['id_servicio'] ?>" <?= $filtroServicio == $s['id_servicio'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Estado</label>
            <select name="estado" class="form-select form-select-sm">
                <option value="0">-- Todos --</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e['id_estado'] ?>" <?= $filtroEstado == $e['id_estado'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1">Prioridad</label>
            <select name="prioridad" class="form-select form-select-sm">
                <option value="0">-- Todas --</option>
                <?php foreach ($prioridades as $p): ?>
                    <option value="<?= $p['id_prioridad'] ?>" <?= $filtroPrioridad == $p['id_prioridad'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-dark w-100"><i class="bi bi-funnel-fill"></i> Filtrar</button>
            <a href="tickets.php" class="btn btn-sm btn-outline-secondary">Limpiar</a>
        </div>
    </form>
</div>

<!-- Listado -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Código / Fecha</th>
                    <th>Servicio y Categoría</th>
                    <th>Asunto y Descripción</th>
                    <th>Solicitante</th>
                    <th>Técnico Asignado</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron tickets con los filtros seleccionados.</td></tr>
                <?php endif; ?>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>
                        <div class="fw-bold text-primary"><?= htmlspecialchars($t['codigo_ticket']) ?></div>
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($t['fecha_registro'])) ?></small>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?= htmlspecialchars($t['servicio']) ?></span>
                        <div class="small text-muted mt-1"><?= htmlspecialchars($t['categoria']) ?></div>
                    </td>
                    <td style="max-width: 280px;">
                        <div class="fw-semibold small"><?= htmlspecialchars($t['asunto']) ?></div>
                        <div class="text-muted small text-truncate"><?= htmlspecialchars($t['descripcion']) ?></div>
                    </td>
                    <td class="small"><?= htmlspecialchars($t['solicitante']) ?></td>
                    <td class="small">
                        <?php if ($t['tecnico']): ?>
                            <span class="text-dark fw-semibold"><i class="bi bi-person-check-fill text-success"></i> <?= htmlspecialchars($t['tecnico']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Sin asignar</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-<?= htmlspecialchars($t['color_prio']) ?>"><?= htmlspecialchars($t['prioridad']) ?></span></td>
                    <td><span class="badge bg-<?= htmlspecialchars($t['color_est']) ?>"><?= htmlspecialchars($t['estado']) ?></span></td>
                    <td>
                        <a href="detalle_ticket.php?id=<?= $t['id_ticket'] ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye-fill"></i> Ver
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>