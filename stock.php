<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Variables de búsqueda
$search = trim($_GET['search'] ?? '');
$filter_tipo = trim($_GET['tipo'] ?? '');

// Preparar consulta base
$sql = "SELECT * FROM stock WHERE 1=1";

// Array para parámetros
$params = [];
$types = "";

// Si hay búsqueda por nombre
if ($search !== '') {
    $sql .= " AND nombre LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Si hay filtro por tipo
if ($filter_tipo !== '') {
    $sql .= " AND tipo = ?";
    $params[] = $filter_tipo;
    $types .= "s";
}

$sql .= " ORDER BY nombre";

$stmt = $conn->prepare($sql);

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener tipos distintos para filtro
$tipos_res = $conn->query("SELECT DISTINCT tipo FROM stock");
$tipos = [];
if ($tipos_res) {
    while($row = $tipos_res->fetch_assoc()) {
        $tipos[] = $row['tipo'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Stock - Buscar y Filtrar</title>
<style>
/* Igual que antes + un poco para el formulario */
table { border-collapse: collapse; width: 90%; margin: 20px auto; }
th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
th { background-color: #222; color: white; }
tr:nth-child(even) { background-color: #eee; }
h1 { text-align: center; margin-top: 20px; }
.btn-volver, .btn-agregar { display: inline-block; padding: 8px 12px; background: #222; color: white; text-decoration: none; border-radius: 4px; margin: 10px; }
form { width: 90%; margin: 10px auto; text-align: center; }
input[type="text"], select { padding: 6px; margin: 0 10px 10px 0; }
</style>
</head>
<body>

<h1>Stock de Materiales y Muebles</h1>

<form method="GET" action="stock.php">
    <input type="text" name="search" placeholder="Buscar por nombre" value="<?=htmlspecialchars($search)?>" />
    <select name="tipo">
        <option value="">-- Filtrar por tipo --</option>
        <?php foreach($tipos as $t): ?>
            <option value="<?=htmlspecialchars($t)?>" <?= $filter_tipo === $t ? 'selected' : '' ?>><?=htmlspecialchars($t)?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Buscar</button>
    <a href="stock.php" class="btn-volver">Limpiar</a>
</form>

<a href="stock_form.php" class="btn-agregar">Agregar nuevo</a>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Tipo</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($row['id_stock'])?></td>
                <td><?=htmlspecialchars($row['nombre'])?></td>
                <td><?=htmlspecialchars($row['descripcion'])?></td>
                <td><?=htmlspecialchars($row['cantidad'])?></td>
                <td>$<?=number_format($row['precio_unitario'], 2)?></td>
                <td><?=htmlspecialchars($row['tipo'])?></td>
                <td>
                    <a href="stock_form.php?id=<?= $row['id_stock'] ?>">Editar</a> |
                    <a href="stock_action.php?action=delete&id=<?= $row['id_stock'] ?>" onclick="return confirm('¿Seguro querés eliminar este item?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No se encontraron items.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<a href="dashboard.php" class="btn-volver">Volver al Dashboard</a>

</body>
</html>
