<?php
include 'includes/db.php'; // cargamos $conn

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM clientes WHERE id_cliente = $id") === false) {
        error_log("Error al borrar cliente ID $id: " . $conn->error);
        header("Location: clientes.php?error=foranea");
        exit;
    }
    header("Location: clientes.php?success=deleted");
    exit;
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
