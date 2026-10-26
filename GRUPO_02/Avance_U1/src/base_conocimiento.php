<?php
require_once __DIR__ . '/includes/header.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_articulo'])) {
    $id_servicio  = intval($_POST['id_servicio']);
    $id_categoria = intval($_POST['id_categoria']);
    $tipo_doc     = $_POST['tipo_documento'];
    $titulo       = trim($_POST['titulo']);
    $sintomas     = trim($_POST['sintomas_problema']);
    $pasos        = trim($_POST['pasos_solucion']);
    $palabras     = trim($_POST['palabras_clave']);

    $total = $pdo->query("SELECT COUNT(*) FROM base_conocimiento")->fetchColumn() + 1;
    $codigo = sprintf("KB-2026-%03d", $total);

    $stmt = $pdo->prepare("
        INSERT INTO base_conocimiento (codigo_articulo, id_servicio, id_categoria, id_autor, tipo_documento, titulo, sintomas_problema, pasos_solucion, palabras_clave)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$codigo, $id_servicio, $id_categoria, $usuarioActual['id_usuario'], $tipo_doc, $titulo, $sintomas, $pasos, $palabras]);

    registrarAuditoria($pdo, 'Base_Conocimiento', 'CREAR_ARTICULO', "Se agregó el documento técnico $codigo ($titulo)");
    $mensaje = "Artículo técnico <strong>$codigo</strong> registrado en la Base de Conocimiento.";
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT b.*, s.nombre AS servicio, c.nombre AS categoria, u.nombre_completo AS autor
        FROM base_conocimiento b
        INNER JOIN servicios s ON b.id_servicio = s.id_servicio
        INNER JOIN categorias c ON b.id_categoria = c.id_categoria
        INNER JOIN usuarios u ON b.id_autor = u.id_usuario
        WHERE b.titulo LIKE ? OR b.sintomas_problema LIKE ? OR b.pasos_solucion LIKE ? OR b.palabras_clave LIKE ?
        ORDER BY b.id_articulo DESC
    ");
    $stmt->execute(["%$q%", "%$q%", "%$q%", "%$q%"]);
    $articulos = $stmt->fetchAll();
} else {
    $articulos = $pdo->query("
        SELECT b.*, s.nombre AS servicio, c.nombre AS categoria, u.nombre_completo AS autor
        FROM base_conocimiento b
        INNER JOIN servicios s ON b.id_servicio = s.id_servicio
        INNER JOIN categorias c ON b.id_categoria = c.id_categoria
        INNER JOIN usuarios u ON b.id_autor = u.id_usuario
        ORDER BY b.id_articulo DESC
    ")->fetchAll();
}

$servicios  = $pdo->query("SELECT * FROM servicios WHERE activo = 1")->fetchAll();
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Base de Conocimiento Técnica (Asistente RAG)</h3>
        <p class="text-muted small mb-0">Manuales, procedimientos, preguntas frecuentes y soluciones a incidencias anteriores</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">← Volver al Dashboard</a>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Buscador y Listado de Documentos -->
    <div class="col-lg-8">
        <div class="card p-3 mb-3">
            <form method="GET" action="base_conocimiento.php" class="d-flex gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Buscar por síntoma o palabra clave (ej. credenciales, correo, impresora)...">
                <button type="submit" class="btn btn-primary px-4">Buscar</button>
                <?php if ($q !== ''): ?>
                    <a href="base_conocimiento.php" class="btn btn-outline-secondary">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <?php foreach ($articulos as $art): ?>
            <div class="card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge bg-primary"><?= htmlspecialchars($art['codigo_articulo']) ?></span>
                        <span class="badge bg-info text-dark"><?= htmlspecialchars($art['tipo_documento']) ?></span>
                        <span class="badge bg-secondary"><?= htmlspecialchars($art['servicio']) ?></span>
                    </div>
                    <small class="text-muted">Autor: <?= htmlspecialchars($art['autor']) ?></small>
                </div>
                <h5 class="fw-bold mb-2"><?= htmlspecialchars($art['titulo']) ?></h5>
                <div class="small text-muted mb-2"><strong>Síntomas / Problema:</strong> <?= htmlspecialchars($art['sintomas_problema']) ?></div>
                <div class="p-3 bg-light rounded border small pre-wrap-text"><?= htmlspecialchars($art['pasos_solucion']) ?></div>
                <div class="mt-2 small text-secondary">
                    <i class="bi bi-tags-fill"></i> Palabras clave: <code><?= htmlspecialchars($art['palabras_clave']) ?></code>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulario para Registrar Nuevo Artículo -->
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">+ Agregar Documento Técnico</h6>
            <form method="POST" action="base_conocimiento.php">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Tipo de Documento</label>
                    <select name="tipo_documento" class="form-select form-select-sm" required>
                        <option value="Procedimiento">Procedimiento</option>
                        <option value="Manual">Manual Técnico</option>
                        <option value="FAQ">Pregunta Frecuente (FAQ)</option>
                        <option value="Solucion_Anterior">Solución a Incidencia Anterior</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Servicio Relacionado</label>
                    <select name="id_servicio" class="form-select form-select-sm" required>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= $s['id_servicio'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Categoría</label>
                    <select name="id_categoria" class="form-select form-select-sm" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Título del Documento</label>
                    <input type="text" name="titulo" class="form-control form-control-sm" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Síntomas del Problema</label>
                    <textarea name="sintomas_problema" rows="2" class="form-control form-control-sm" required></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Pasos de Solución</label>
                    <textarea name="pasos_solucion" rows="4" class="form-control form-control-sm" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Palabras Clave</label>
                    <input type="text" name="palabras_clave" class="form-control form-control-sm" placeholder="ej. correo bloqueo cuenta" required>
                </div>
                <button type="submit" name="guardar_articulo" class="btn btn-primary btn-sm w-100 fw-bold">
                    Guardar en Base de Conocimiento
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>