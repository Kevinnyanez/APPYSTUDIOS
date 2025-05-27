<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? null;
$modo = $id ? 'Editar' : 'Agregar';

$nombre = '';
$descripcion = '';
$cantidad = '';
$precio_unitario = '';
$tipo = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM stock WHERE id_stock = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $nombre = $fila['nombre'];
        $descripcion = $fila['descripcion'];
        $cantidad = $fila['cantidad'];
        $precio_unitario = $fila['precio_unitario'];
        $tipo = $fila['tipo'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $cantidad = (int)$_POST['cantidad'];
    $precio_unitario = (float)$_POST['precio_unitario'];
    $tipo = $_POST['tipo'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE stock SET nombre=?, descripcion=?, cantidad=?, precio_unitario=?, tipo=? WHERE id_stock=?");
        $stmt->bind_param("ssidsi", $nombre, $descripcion, $cantidad, $precio_unitario, $tipo, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO stock (nombre, descripcion, cantidad, precio_unitario, tipo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssids", $nombre, $descripcion, $cantidad, $precio_unitario, $tipo);
    }

    $stmt->execute();
    header("Location: stock.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $modo ?> producto</title>
</head>
<body>

<h1><?= $modo ?> producto</h1>

<form method="post">
    <label for="nombre">Nombre *</label>
    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" id="descripcion"><?= htmlspecialchars($descripcion) ?></textarea>

    <label for="cantidad">Cantidad *</label>
    <input type="number" name="cantidad" id="cantidad" value="<?= htmlspecialchars($cantidad) ?>" required>

    <label for="precio_unitario">Precio Unitario *</label>
    <input type="number" name="precio_unitario" id="precio_unitario" step="0.01" min="0" value="<?= htmlspecialchars($precio_unitario) ?>" required>

    <label for="tipo">Tipo *</label>
    <select name="tipo" id="tipo" required>
        <option value="">-- Seleccionar --</option>
        <option value="Material" <?= $tipo === 'Material' ? 'selected' : '' ?>>Material</option>
        <option value="Mueble" <?= $tipo === 'Mueble' ? 'selected' : '' ?>>Mueble</option>
    </select>

    <button type="submit">Guardar</button>
</form>

<a href="stock.php">← Volver al stock</a>

</body>
</html>
