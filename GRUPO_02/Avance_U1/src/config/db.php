<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host    = '127.0.0.1';
$db      = 'helpdesk_rag_db';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("<div style='font-family:sans-serif;padding:24px;background:#fef2f2;border:1px solid #ef4444;color:#991b1b;border-radius:8px;margin:20px;'>
            <h3>Error de Conexión a la Base de Datos</h3>
            <p>No se pudo conectar a <b>helpdesk_rag_db</b>: " . htmlspecialchars($e->getMessage()) . "</p>
            <p>Verifica que el servicio MySQL esté activo y hayas importado el script SQL del proyecto.</p>
         </div>");
}

if (!isset($_SESSION['usuario'])) {
    $stmtDefault = $pdo->query("
        SELECT u.*, r.nombre AS rol_nombre 
        FROM usuarios u 
        INNER JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.id_usuario = 3 LIMIT 1
    ");
    $_SESSION['usuario'] = $stmtDefault->fetch();
}

function registrarAuditoria(PDO $pdo, string $modulo, string $accion, string $descripcion): void {
    $id_usuario = $_SESSION['usuario']['id_usuario'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $pdo->prepare("INSERT INTO auditoria (id_usuario, modulo, accion, descripcion_evento, ip_origen) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_usuario, $modulo, $accion, $descripcion, $ip]);
}
?>