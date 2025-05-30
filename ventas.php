<?php
include 'includes/db.php';

// Obtener mes y año desde el formulario si existen
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Consulta para ventas por mes
$sql = "SELECT p.id_presupuesto, p.fecha_creacion, p.total_con_recargo, c.nombre
        FROM presupuestos p
        JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.estado = 'cerrado' AND MONTH(p.fecha_creacion) = ? AND YEAR(p.fecha_creacion) = ?
        ORDER BY p.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $mes, $anio);
$stmt->execute();
$result = $stmt->get_result();

// Consulta para total acumulado
// Consulta para total acumulado
$totalAcumulado = 0;
$sqlTotal = "SELECT SUM(total_con_recargo) AS total FROM presupuestos WHERE estado = 'cerrado'";
$resTotal = $conn->query($sqlTotal);
if ($row = $resTotal->fetch_assoc()) {
    $totalAcumulado = $row['total'];
}
// Consulta para total acumulado del mes filtrado
$totalAcumulado = 0;
$sqlTotal = "SELECT SUM(total_con_recargo) AS total 
             FROM presupuestos 
             WHERE estado = 'cerrado' 
             AND MONTH(fecha_creacion) = ? 
             AND YEAR(fecha_creacion) = ?";
$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("ii", $mes, $anio);
$stmtTotal->execute();
$resTotal = $stmtTotal->get_result();
if ($row = $resTotal->fetch_assoc()) {
    $totalAcumulado = $row['total'];
}


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ventas Confirmadas</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    h1 { color: #2c3e50; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ccc; }
    th { background-color: #ecf0f1; }
    .total { font-weight: bold; color: green; }
    .filtros { margin-top: 10px; margin-bottom: 20px; }
    .total-acumulado { margin-top: 20px; font-weight: bold; background: #f5f5f5; padding: 10px; border: 1px solid #ccc; display: inline-block; }
  </style>
</head>
<body>

<h1>🧾 Ventas Confirmadas</h1>

<div class="filtros">
  <form method="GET" action="">
    <label for="mes">Mes:</label>
    <select name="mes" id="mes">
      <?php for ($i = 1; $i <= 12; $i++): ?>
        <option value="<?= $i ?>" <?= ($i == $mes) ? 'selected' : '' ?>>
          <?= str_pad($i, 2, "0", STR_PAD_LEFT) ?>
        </option>
      <?php endfor; ?>
    </select>

    <label for="anio">Año:</label>
    <select name="anio" id="anio">
      <?php
        $anioActual = date('Y');
        for ($y = $anioActual; $y >= $anioActual - 5; $y--): ?>
        <option value="<?= $y ?>" <?= ($y == $anio) ? 'selected' : '' ?>>
          <?= $y ?>
        </option>
      <?php endfor; ?>
    </select>

    <button type="submit">🔍 Filtrar</button>
  </form>
</div>

<table>
  <thead>
    <tr>
      <th># Presupuesto</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total Pagado</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td>#<?= $row['id_presupuesto'] ?></td>
          <td><?= htmlspecialchars($row['nombre']) ?></td>
          <td><?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?></td>
          <td class="total">$<?= number_format($row['total_con_recargo'], 2, ',', '.') ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="4">No hay ventas registradas en este período.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<div class="total-acumulado">
  💰 Total acumulado de todas las ventas: <strong>$<?= number_format($totalAcumulado, 2, ',', '.') ?></strong>
</div>

</body>
</html>
