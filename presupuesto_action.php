<?php
require_once 'includes/db.php'; // conexión centralizada

// Capturar datos básicos del presupuesto
$id_presupuesto = $_POST['id_presupuestos'] ?? null;
$id_cliente     = (int)$_POST['id_cliente'];
$total          = (float)$_POST['total'] ?? 0;
$fecha_creacion = date('Y-m-d');
$estado         = 'activo';

// 1. Insertar presupuesto (solo insert por ahora)
$stmt = $conn->prepare("INSERT INTO presupuestos (id_cliente, fecha_creacion, total, estado) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isds", $id_cliente, $fecha_creacion, $total, $estado);
$stmt->execute();
$id_presupuesto = $stmt->insert_id;
$stmt->close();

// 2. Insertar los ítems del presupuesto
$id_stock_array        = $_POST['id_stock'];
$cantidad_array        = $_POST['cantidad'];
$precio_unitario_array = $_POST['precio_unitario'];
$subtotal_array        = $_POST['subtotal'];

$stmt_item = $conn->prepare("INSERT INTO presupuesto_items (id_presupuesto, id_stock, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

for ($i = 0; $i < count($id_stock_array); $i++) {
    $id_stock        = (int)$id_stock_array[$i];
    $cantidad        = (int)$cantidad_array[$i];
    $precio_unitario = (float)$precio_unitario_array[$i];
    $subtotal        = (float)$subtotal_array[$i];

    $stmt_item->bind_param("iiidd", $id_presupuesto, $id_stock, $cantidad, $precio_unitario, $subtotal);
    $stmt_item->execute();
}

$stmt_item->close();
$conn->close();

// Redireccionar al listado de presupuestos
header("Location: presupuestos.php?ok=1");
exit;
?>
