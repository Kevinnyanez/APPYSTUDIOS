<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_POST['id_presupuesto']) || !isset($_POST['descripcion'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

$id = (int) $_POST['id_presupuesto'];
$descripcion = trim($_POST['descripcion']);

try {
    $stmt = $pdo->prepare("UPDATE presupuestos SET descripcion = :descripcion WHERE id_presupuesto = :id");
    $stmt->execute([
        ':descripcion' => $descripcion,
        ':id' => $id
    ]);

    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>