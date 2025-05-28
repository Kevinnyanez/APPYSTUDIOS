<?php
include 'includes/db.php';
include 'header.php';

$mes_actual = date('m');
$anio_actual = date('Y');

// Ventas cerradas del mes
$sql_ventas = "SELECT p.*, c.nombre AS cliente 
               FROM presupuestos p
               JOIN clientes c ON p.id_cliente = c.id_cliente
               WHERE MONTH(p.fecha_creacion) = ? AND YEAR(p.fecha_creacion) = ? AND p.estado = 'cerrado'
               ORDER BY p.fecha_creacion DESC";

$stmt = $conn->prepare($sql_ventas);
$stmt->bind_param('ss', $mes_actual, $anio_actual);
$stmt->execute();
$result_ventas = $stmt->get_result();

// Presupuestos abiertos
$sql_abiertos = "SELECT p.*, c.nombre AS cliente 
                 FROM presupuestos p
                 JOIN clientes c ON p.id_cliente = c.id_cliente
                 WHERE p.estado = 'abierto'
                 ORDER BY p.fecha_creacion DESC";
$result_abiertos = $conn->query($sql_abiertos);

// Clientes con presupuestos
$sql_clientes_presupuestos = "SELECT c.*, COUNT(p.id_presupuestos) AS total_presupuestos
                              FROM clientes c
                              LEFT JOIN presupuestos p ON c.id_cliente = p.id_cliente
                              GROUP BY c.id_cliente
                              ORDER BY total_presupuestos DESC";
$result_clientes = $conn->query($sql_clientes_presupuestos);

// Totales para resumen
$total_ventas = 0;
$total_abiertos = $result_abiertos->num_rows;
$total_clientes = $result_clientes->num_rows;
foreach ($result_ventas as $venta) {
    $total_ventas += $venta['total'];
}
?>

<div class="container">
    <h1>Panel de Ventas del Mes</h1>

    <div class="resumen">
        <div class="card">Total Ventas: <strong>$<?= number_format($total_ventas, 2) ?></strong></div>
        <div class="card">Presupuestos Abiertos: <?= $total_abiertos ?></div>
        <div class="card">Clientes con presupuestos: <?= $total_clientes ?></div>
    </div>

    <h2>Ventas del Mes</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Total</th></tr>
        </thead>
        <tbody>
            <?php foreach ($result_ventas as $venta): ?>
                <tr>
                    <td><?= $venta['id_presupuestos'] ?></td>
                    <td><?= htmlspecialchars($venta['cliente']) ?></td>
                    <td><?= $venta['fecha_creacion'] ?></td>
                    <td>$<?= number_format($venta['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Presupuestos Abiertos</h2>
    <table>
        <thead><tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Acciones</th></tr></thead>
        <tbody>
            <?php while($row = $result_abiertos->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_presupuestos'] ?></td>
                    <td><?= htmlspecialchars($row['cliente']) ?></td>
                    <td><?= $row['fecha_creacion'] ?></td>
                    <td>$<?= number_format($row['total'], 2) ?></td>
                    <td><a href="presupuesto_form.php?id=<?= $row['id_presupuestos'] ?>">Ver / Editar</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Clientes con Presupuestos</h2>
    <table>
        <thead><tr><th>ID</th><th>Nombre</th><th>Total Presupuestos</th></tr></thead>
        <tbody>
            <?php while($row = $result_clientes->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_cliente'] ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= $row['total_presupuestos'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
