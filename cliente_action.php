<?php
include 'includes/db.php'; // cargamos $conn
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $conn->query("DELETE FROM clientes WHERE id_cliente = $id");
        header("Location: clientes.php?success=deleted");
        exit;
    } catch (mysqli_sql_exception $e) {
        error_log("Error al borrar cliente ID $id: " . $e->getMessage());
        header("Location: clientes.php?error=foranea");
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
    $nombre = $conn->real_escape_string(trim($_POST['nombre']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $telefono = $conn->real_escape_string(trim($_POST['telefono']));
    $direccion = $conn->real_escape_string(trim($_POST['direccion']));

    if (!$nombre) {
        die("El nombre es obligatorio.");
    }

    if ($id > 0) {
        // Actualizar
        $conn->query("UPDATE clientes SET nombre='$nombre', email='$email', telefono='$telefono', direccion='$direccion' WHERE id_cliente = $id");
    } else {
        // Insertar nuevo cliente, fecha_registro automático con default CURRENT_DATE en tabla
        $conn->query("INSERT INTO clientes (nombre, email, telefono, direccion) VALUES ('$nombre', '$email', '$telefono', '$direccion')");
    }

    header("Location: clientes.php");
    exit;
}

header("Location: clientes.php");
