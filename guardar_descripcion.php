<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (!isset($_POST['id_presupuesto']) || !isset($_POST['descripcion'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

$id = (int) $_POST['id_presupuesto'];
$descripcion = trim($_POST['descripcion']);

// Asegurate de tener $mysqli en includes/db.php
if (!isset($mysqli)) {
    echo json_encode(['ok' => false, 'error' => 'Conexión no disponible']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE presupuestos SET descripcion = ? WHERE id_presupuesto = ?");
if ($stmt) {
    $stmt->bind_param('si', $descripcion, $id);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Error al ejecutar: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['ok' => false, 'error' => 'Error en prepare: ' . $mysqli->error]);
}
