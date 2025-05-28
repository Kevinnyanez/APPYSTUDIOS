<?php
session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_presupuesto = $_POST['id_presupuesto'];

    // Obtener datos del presupuesto
    $stmt = $conn->prepare("SELECT id_cliente, total_con_recargo FROM presupuestos WHERE id_presupuesto = ?");
    $stmt->bind_param("i", $id_presupuesto);
    $stmt->execute();
    $result = $stmt->get_result();
    $presupuesto = $result->fetch_assoc();

    if ($presupuesto) {
        // Evitar duplicación
        $check = $conn->prepare("SELECT * FROM ventas WHERE id_presupuesto = ?");
        $check->bind_param("i", $id_presupuesto);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO ventas (id_cliente, id_presupuesto, fecha_venta, monto_total) VALUES (?, ?, NOW(), ?)");
            $insert->bind_param("iid", $presupuesto['id_cliente'], $id_presupuesto, $presupuesto['total_con_recargo']);
            $insert->execute();
            echo "ok";
        } else {
            echo "existente";
        }
    } else {
        echo "error";
    }
}
?>
