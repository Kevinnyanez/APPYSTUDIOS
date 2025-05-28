<?php
require_once 'includes/db.php'; // conexión centralizada

// Validar datos mínimos
if (!isset($_POST['id_cliente'], $_POST['id_stock'], $_POST['cantidad'], $_POST['precio_unitario'], $_POST['subtotal'])) {
    header('Location: presupuestos.php?error=datos');
    exit;
}

$id_cliente = (int)$_POST['id_cliente'];
$id_stock_array        = $_POST['id_stock'];
$cantidad_array        = $_POST['cantidad'];
$precio_unitario_array = $_POST['precio_unitario'];
$subtotal_array        = $_POST['subtotal'];

// Validar que haya al menos un ítem
if (count($id_stock_array) === 0) {
    header('Location: presupuestos.php?error=sin_items');
    exit;
}

// Calcular el total sumando los subtotales
$total = 0;
foreach ($subtotal_array as $sub) {
    $total += (float)$sub;
}

$fecha_creacion = date('Y-m-d H:i:s');
$estado = 'abierto';

$conn->begin_transaction();
try {
    // Insertar presupuesto
    $stmt = $conn->prepare("INSERT INTO presupuestos (id_cliente, fecha_creacion, estado, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $id_cliente, $fecha_creacion, $estado, $total);
    $stmt->execute();
    $id_presupuesto = $stmt->insert_id;
    $stmt->close();

    // Insertar los ítems
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

    $conn->commit();
    $conn->close();
    header("Location: presupuestos.php?ok=1");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    header("Location: presupuestos.php?error=transaccion");
    exit;
}
?>
