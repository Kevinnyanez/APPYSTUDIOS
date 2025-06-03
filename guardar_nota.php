<?php
require_once 'includes/db.php';
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Obtiene los datos JSON enviados
$data = json_decode(file_get_contents('php://input'), true);

// Verifica que los datos estén presentes
if (!isset($data['id_presupuesto']) || !isset($data['notas'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$id_presupuesto = $data['id_presupuesto'];
$notas = $data['notas'];

// Prepara la consulta
$stmt = $conn->prepare("UPDATE presupuestos SET notas = ? WHERE id_presupuesto = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error en la preparación']);
    exit;
}

$stmt->bind_param("si", $notas, $id_presupuesto);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al ejecutar']);
}
?>
