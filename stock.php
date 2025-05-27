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

.form-busqueda {
    max-width: 600px;
    margin: 20px auto;
    padding: 16px;
    background-color: #222;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.form-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.input-text,
.input-select {
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
    max-width: 250px;
}

.btn {
    padding: 10px 16px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    text-align: center;
    display: inline-block;
}

.btn-buscar {
    background-color: #007BFF;
    color: white;
}

.btn-buscar:hover {
    background-color: #0056b3;
}

.btn-limpiar {
    background-color: #6c757d;
    color: white;
}

.btn-limpiar:hover {
    background-color: #5a6268;
}

.Stock-esti{
  text-align: center;
  font-size: 2rem;
  margin-bottom: 2rem;
  color: #007acc;
  font-family: 'Segoe UI', sans-serif;
  font-weight: 700;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #007acc;
  padding-bottom: 0.5rem;
  display: inline-block;
}


</style>
</head>
<body>

<h1 class="Stock-esti">Stock de Materiales y Muebles</h1>

<form method="GET" action="stock.php" class="form-busqueda">
    <div class="form-group">
        <input type="text" name="search" placeholder="Buscar por nombre" value="<?=htmlspecialchars($search)?>" class="input-text" />
        
        <select name="tipo" class="input-select">
            <option value="">-- Filtrar por tipo --</option>
            <?php foreach($tipos as $t): ?>
                <option value="<?=htmlspecialchars($t)?>" <?= $filter_tipo === $t ? 'selected' : '' ?>><?=htmlspecialchars($t)?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-buscar">Buscar</button>
        <a href="stock.php" class="btn btn-limpiar">Limpiar</a>
    </div>
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
