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
    body {

        background-color: #cbd5e1;
    }
/* Igual que antes + un poco para el formulario */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #cbd5e1;
  color: #333;
  margin: 0;
  padding: 20px;
}

h1.Stock-esti {
  font-size: 28px;
  margin-bottom: 20px;
  color: #1f2d3d;
  border-bottom: 2px solid #0077b6;
  display: inline-block;
  padding-bottom: 5px;
}

.form-busqueda {
  background-color: #ffffff;
  padding: 15px;
  margin-bottom: 20px;
  border: 1px solid #dcdcdc;
  border-radius: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.form-busqueda .input-text,
.form-busqueda .input-select {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  flex: 1;
  min-width: 180px;
}

.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
}

.btn-buscar {
  background-color: #0077b6;
  color: white;
}

.btn-limpiar {
  background-color: #adb5bd;
  color: white;
}

.btn-agregar {
  display: inline-block;
  margin-bottom: 15px;
  background-color: #28a745;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  text-decoration: none;
}

.btn-volver {
  display: inline-block;
  margin-top: 20px;
  background-color: #6c757d;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  text-decoration: none;
}

table {
  width: 95%;
  margin: 0 auto;
  border-collapse: collapse;
  background-color: #2b2b2b;
  box-shadow: 0 4px 10px rgba(0,0,0,0.5);
  border-radius: 8px;
  overflow: hidden;
}

table th,
table td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #444;
}

table th {
  background-color: #333;
  color: #fff;
  font-weight: bold;
}

table tr:nth-child(even) {
  background-color: #262626;
}

table tr:hover {
  background-color: #383838;
}

@media (max-width: 768px) {
  table thead {
    display: none;
  }

  table tr {
    display: block;
    margin-bottom: 20px;
    background-color: #2b2b2b;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    overflow: hidden;
  }

  table td {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    border-bottom: 1px solid #333;
  }

  table td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #aaa;
  }

  table td:last-child {
    border-bottom: none;
  }
}


</style>
</head>
<body>

<div style="text-align: center;">
  <h1 class="Stock-esti">Stock de Materiales y Muebles</h1>
</div>

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
