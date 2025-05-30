<?php
require_once 'includes/db.php';
session_start();

if (!isset($_POST['id_presupuesto'])) {
    echo "error";
    exit();
}

$id_presupuesto = intval($_POST['id_presupuesto']);

// Verificar si ya se confirmó la venta
$check = $conn->query("SELECT id_venta FROM ventas WHERE id_presupuesto = $id_presupuesto");
if ($check && $check->num_rows > 0) {
    echo "existente";
    exit();
}

// Obtener los datos del presupuesto
$presupuesto = $conn->query("SELECT * FROM presupuestos WHERE id_presupuesto = $id_presupuesto")->fetch_assoc();
if (!$presupuesto) {
    echo "error";
    exit();
}

$id_cliente = $presupuesto['id_cliente'];
$monto_total = $presupuesto['total_con_recargo']; // El monto final
$fecha = date('Y-m-d');

// Insertar en ventas
$conn->query("
    INSERT INTO ventas (id_cliente, id_presupuesto, monto_total, fecha_venta)
    VALUES ($id_cliente, $id_presupuesto, $monto_total, '$fecha')
");

// (opcional) Podés marcar el presupuesto como “cerrado” acá si no lo hacés en otro lado
$conn->query("UPDATE presupuestos SET estado = 'cerrado' WHERE id_presupuesto = $id_presupuesto");

echo "ok";
