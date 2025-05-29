<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Ruta correcta a tu conexión
require_once(__DIR__ . '../includes/db.php'); // Cambiá esto si la ruta es diferente

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- DEBUG LOG ---
    file_put_contents(__DIR__ . '/log_debug.txt', "POST:\n" . print_r($_POST, true), FILE_APPEND);

    // --- CREAR CLIENTE SI ES NUEVO ---
    if (isset($_POST['crear_cliente']) && $_POST['crear_cliente'] === 'true') {
        $nombre = $_POST['nuevo_nombre'] ?? '';
        $email = $_POST['nuevo_email'] ?? '';
        $telefono = $_POST['nuevo_telefono'] ?? '';
        $direccion = $_POST['nuevo_direccion'] ?? '';

        if (!$nombre) {
            echo "error: nombre vacío";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $telefono, $direccion);
        if (!$stmt->execute()) {
            echo "error: no se pudo crear cliente";
            exit;
        }
        $id_cliente = $conn->insert_id;
        $stmt->close();
    } else {
        $id_cliente = $_POST['id_cliente'] ?? null;
        if (!$id_cliente || $id_cliente === 'nuevo') {
            echo "error: cliente no definido";
            exit;
        }
    }

    // --- CREAR PRESUPUESTO ---
    $fecha = $_POST['fecha_creacion'] ?? date('Y-m-d');
    $recargo = floatval($_POST['recargo_final'] ?? 0);

    $stmt = $conn->prepare("INSERT INTO presupuestos (id_cliente, fecha_creacion, recargo_final) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $id_cliente, $fecha, $recargo);
    if (!$stmt->execute()) {
        echo "error: no se pudo crear presupuesto";
        exit;
    }
    $id_presupuesto = $conn->insert_id;
    $stmt->close();

    // --- AGREGAR ÍTEMS ---
    $ids = $_POST['id_stock'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $precios = $_POST['precio_unitario'] ?? [];
    $subtotales = $_POST['subtotal'] ?? [];

    if (count($ids) === 0) {
        echo "error: presupuesto sin productos";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO presupuesto_items (id_presupuesto, id_stock, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($ids); $i++) {
        $id_stock = intval($ids[$i]);
        $cantidad = intval($cantidades[$i]);
        $precio = floatval($precios[$i]);
        $subtotal = floatval($subtotales[$i]);

        $stmt->bind_param("iiidd", $id_presupuesto, $id_stock, $cantidad, $precio, $subtotal);
        $stmt->execute();
    }
    $stmt->close();

    echo "ok";
    exit;
}

// Si llega hasta acá sin ser POST
echo "error: sin POST";
