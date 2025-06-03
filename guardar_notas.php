<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id_presupuesto = $data['id_presupuesto'] ?? null;
$notas = $data['nota'] ?? '';

if (!$id_presupuesto) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit();
}

$sql = "UPDATE presupuestos SET notas = ? WHERE id_presupuesto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nota, $id_presupuesto);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar']);
}
