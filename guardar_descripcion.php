<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-error.log');

require_once 'includes/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if (!isset($_POST['id_presupuesto']) || !isset($_POST['descripcion'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

$id = (int) $_POST['id_presupuesto'];
$descripcion = trim($_POST['descripcion']);

// Usamos $conn que está definido en includes/db.php
if (!isset($conn)) {
    echo json_encode(['ok' => false, 'error' => 'Conexión no disponible']);
    exit;
}

file_put_contents('/tmp/debug.txt', "Preparando consulta para id: $id\n", FILE_APPEND);

$stmt = $conn->prepare("UPDATE presupuestos SET descripcion = ? WHERE id_presupuesto = ?");
if (!$stmt) {
    echo json_encode(['ok' => false, 'error' => 'Error en prepare: ' . $conn->error]);
    exit;
}

$stmt->bind_param('si', $descripcion, $id);

if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'error' => 'Error al ejecutar: ' . $stmt->error]);
    exit;
}

echo json_encode(['ok' => true]);
exit;
