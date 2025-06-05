<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_presupuesto']) || !isset($data['descripcion'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$id_presupuesto = $data['id_presupuesto'];
$descripcion = $data['descripcion'];

$stmt = $conn->prepare("UPDATE presupuestos SET descripcion = ? WHERE id_presupuesto = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error en la preparación']);
    exit;
}

$id_presupuesto = intval($data['id_presupuesto']); // Asegura tipo entero

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar']);
}
?>
