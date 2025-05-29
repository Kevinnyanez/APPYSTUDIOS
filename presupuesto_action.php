<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../includes/db.php'); // ✅ asegurate que este archivo existe

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CREAR CLIENTE SI CORRESPONDE ---
    if (isset($_POST['crear_cliente']) && $_POST['crear_cliente'] === 'true') {
        $nuevo_nombre    = $_POST['nuevo_nombre'] ?? '';
        $nuevo_email     = $_POST['nuevo_email'] ?? '';
        $nuevo_telefono  = $_POST['nuevo_telefono'] ?? '';
        $nuevo_direccion = $_POST['nuevo_direccion'] ?? '';

        if (empty($nuevo_nombre)) {
            echo 'error: nombre vacío';
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nuevo_nombre, $nuevo_email, $nuevo_telefono, $nuevo_direccion);

        if (!$stmt->execute()) {
            echo 'error: no se pudo crear el cliente';
            exit;
        }

        $id_cliente = $conn->insert_id;
        $stmt->close();
    } else {
        $id_cliente = $_POST['id_cliente'] ?? null;
        if (!$id_cliente || $id_cliente === 'nuevo') {
            echo 'error: cliente no definido';
            exit;
        }
    }

    // --- CREAR PRESUPUESTO ---
    $fecha_creacion = $_POST['fecha_creacion'] ?? date('Y-m-d');
    $recargo_final = floatval($_POST['recargo_final'] ?? 0);

    $stmt = $conn->prepare("INSERT INTO presupuestos (id_cliente, fecha_creacion, recargo_final) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $id_cliente, $fecha_creacion, $recargo_final);

    if (!$stmt->execute()) {
        echo 'error: no se pudo crear el presupuesto';
        exit;
    }

    $id_presupuesto = $conn->insert_id;
    $stmt->close();

    // --- AGREGAR ÍTEMS ---
    $ids     = $_POST['id_stock'] ?? [];
    $cant    = $_POST['cantidad'] ?? [];
    $precios = $_POST['precio_unitario'] ?? [];
    $subs    = $_POST['subtotal'] ?? [];

    if (count($ids) === 0) {
        echo 'error: presupuesto sin productos';
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO presupuesto_items (id_presupuesto, id_stock, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

    for ($i = 0; $i < count($ids); $i++) {
        $id_stock       = intval($ids[$i]);
        $cantidad       = intval($cant[$i]);
        $precio_unit    = floatval($precios[$i]);
        $subtotal       = floatval($subs[$i]);

        $stmt->bind_param("iiidd", $id_presupuesto, $id_stock, $cantidad, $precio_unit, $subtotal);
        $stmt->execute();
    }

    $stmt->close();

    echo 'ok';
    exit;
};
