<?php
require_once 'includes/db.php'; // conexión centralizada

// Eliminar presupuesto
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Eliminar ítems primero
    $conn->query("DELETE FROM presupuesto_items WHERE id_presupuesto = $id");
    // Eliminar presupuesto
    $conn->query("DELETE FROM presupuestos WHERE id_presupuesto = $id");
    echo 'ok';
    exit;
}

// Cerrar presupuesto
if (isset($_GET['cerrar'])) {
    $id = (int)$_GET['cerrar'];
    $conn->query("UPDATE presupuestos SET estado = 'cerrado' WHERE id_presupuesto = $id");
    echo 'ok';
    exit;
}

// Actualizar presupuesto
if (isset($_POST['id_presupuesto'])) {
    $id_presupuesto = (int)$_POST['id_presupuesto'];
    $id_cliente = (int)$_POST['id_cliente'];
    $fecha_creacion = $_POST['fecha_creacion'] ?? date('Y-m-d');
    $id_stock_array = $_POST['id_stock'];
    $cantidad_array = $_POST['cantidad'];
    $precio_unitario_array = $_POST['precio_unitario'];
    $subtotal_array = $_POST['subtotal'];
    if (count($id_stock_array) === 0) {
        echo 'error:sin_items';
        exit;
    }
    $total = 0;
    foreach ($subtotal_array as $sub) {
        $total += (float)$sub;
    }
    $conn->begin_transaction();
    try {
        // Actualizar presupuesto
        $stmt = $conn->prepare("UPDATE presupuestos SET id_cliente=?, fecha_creacion=?, total=? WHERE id_presupuesto=?");
        $stmt->bind_param("isdi", $id_cliente, $fecha_creacion, $total, $id_presupuesto);
        $stmt->execute();
        $stmt->close();
        // Eliminar ítems viejos
        $conn->query("DELETE FROM presupuesto_items WHERE id_presupuesto = $id_presupuesto");
        // Insertar ítems nuevos
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
        echo 'ok';
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        echo 'error:transaccion';
        exit;
    }
}

// Obtener presupuesto y sus ítems (para editar, AJAX)
if (isset($_GET['get_presupuesto'])) {
    $id = (int)$_GET['get_presupuesto'];
    $pres = $conn->query("SELECT * FROM presupuestos WHERE id_presupuesto = $id")->fetch_assoc();
    $items = [];
    $res = $conn->query("SELECT pi.*, s.nombre AS nombre_stock FROM presupuesto_items pi JOIN stock s ON pi.id_stock = s.id_stock WHERE id_presupuesto = $id");
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    echo json_encode(['presupuesto' => $pres, 'items' => $items]);
    exit;
}

// Endpoint para obtener los ítems de un presupuesto (para el resumen desplegable)
if (isset($_GET['get_presupuesto'])) {
    $id = (int)$_GET['get_presupuesto'];
    $items = [];
    $sql = "SELECT pi.*, s.nombre AS nombre_stock FROM presupuesto_items pi JOIN stock s ON pi.id_stock = s.id_stock WHERE pi.id_presupuesto = $id";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode(['items' => $items]);
    exit;
}

// Crear cliente si se solicita
if (isset($_POST['crear_cliente'])) {
    $nombre = trim($_POST['nuevo_nombre'] ?? '');
    $email = trim($_POST['nuevo_email'] ?? '');
    $telefono = trim($_POST['nuevo_telefono'] ?? '');
    $direccion = trim($_POST['nuevo_direccion'] ?? '');
    if (!$nombre) {
        echo 'error:Nombre requerido';
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $nombre, $email, $telefono, $direccion);
    $stmt->execute();
    $id_cliente = $stmt->insert_id;
    $stmt->close();
    echo 'cliente_id:' . $id_cliente;
    exit;
}

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
