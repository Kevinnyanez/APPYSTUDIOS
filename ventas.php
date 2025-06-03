<?php

require_once 'includes/db.php';
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}
// Obtener mes y año desde el formulario si existen
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Consulta para ventas por mes
$sql = "SELECT p.id_presupuesto, p.fecha_creacion, p.total_con_recargo, c.nombre, p.notas
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
$stmt = $conn->prepare("UPDATE presupuestos SET notas = ? WHERE id_presupuesto = ?");
$stmt->bind_param("si", $notas, $id_presupuesto);


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ventas Confirmadas</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #cbd5e1;
      color: #222;
      margin: 0;
      padding: 20px;
    }
    h1 {
      color: #00bcd4;
      margin-bottom: 24px;
      text-align: center;
      font-size: 2rem;
    }
    .filtros {
      margin: 0 auto 28px auto;
      max-width: 700px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      padding: 18px 24px 10px 24px;
      display: flex;
      flex-wrap: wrap;
      gap: 18px;
      align-items: center;
      justify-content: center;
    }
    .filtros label {
      font-weight: 600;
      color: #0077b6;
      margin-right: 6px;
    }
    .filtros select, .filtros button {
      padding: 7px 12px;
      border-radius: 6px;
      border: 1px solid #bbb;
      font-size: 1rem;
      margin-right: 10px;
    }
    .filtros button {
      background: #00bcd4;
      color: #fff;
      border: none;
      font-weight: 600;
      transition: background 0.2s;
      cursor: pointer;
    }
    .filtros button:hover {
      background: #0097a7;
    }
    table {
      width: 98%;
      margin: 0 auto 24px auto;
      border-collapse: separate;
      border-spacing: 0;
      background-color: #23272f;
      box-shadow: 0 4px 18px rgba(0,0,0,0.10);
      border-radius: 12px;
      overflow: hidden;
    }
    th, td {
      padding: 14px 18px;
      text-align: left;
      font-size: 1rem;
    }
    th {
      background-color: #1a1d23;
      color: #fff;
      font-weight: 700;
      font-size: 1.08rem;
      letter-spacing: 0.5px;
    }
    tr {
      transition: background 0.18s;
    }
    tr:nth-child(even) {
      background-color: #23272f;
    }
    tr:nth-child(odd) {
      background-color: #2d323c;
    }
    tr:hover {
      background-color: #0077b6 !important;
      color: #fff;
    }
    td, th {
      border-bottom: 1px solid #353a45;
    }
    td {
      color: #f1f1f1;
      font-size: 1rem;
    }
    .total {
      font-weight: bold;
      color: #22c55e;
    }
    .total-acumulado {
      margin: 0 auto 0 auto;
      font-weight: bold;
      background: #fff;
      padding: 18px 30px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      display: block;
      max-width: 700px;
      color: #0077b6;
      font-size: 1.15rem;
      text-align: center;
    }
    @media (max-width: 900px) {
      table, .filtros, .total-acumulado { width: 99%; padding: 10px; }
      th, td { padding: 8px 6px; font-size: 0.98rem; }
      h1 { font-size: 1.3rem; }
    }
  </style>
</head>
<body>
<?php include 'header.php'; ?>
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
          <td>
        <textarea
          data-id="<?= $row['id_presupuesto'] ?>"
          class="nota-textarea"
          rows="3"
          style="width: 100%; resize: vertical;"
        ><?= htmlspecialchars($row['notas']) ?></textarea>
      </td>
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

<?php include 'footer.php'; ?>
</body>
<script>
  document.querySelectorAll('.nota-textarea').forEach(textarea => {
    textarea.addEventListener('blur', function() {
      const idPresupuesto = this.dataset.id;
      const notas = this.value;

      fetch('guardar_nota.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_presupuesto: idPresupuesto, notas: notas })
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          console.log('Nota guardada');
        } else {
          alert('Error al guardar la nota.');
        }
      })
      .catch(() => alert('Error de conexión.'));
    });
  });
</script>

</html>
