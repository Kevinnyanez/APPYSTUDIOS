<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id > 0) {
    $stmt = $conn->prepare("DELETE FROM stock WHERE id_stock = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $precio_unitario = (float)($_POST['precio_unitario'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $stmt = $conn->prepare("INSERT INTO stock (nombre, descripcion, cantidad, precio_unitario, tipo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssids", $nombre, $descripcion, $cantidad, $precio_unitario, $tipo);
    $stmt->execute();
    $stmt->close();
    echo 'ok';
    exit();
}

if ($action === 'edit' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $precio_unitario = (float)($_POST['precio_unitario'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $stmt = $conn->prepare("UPDATE stock SET nombre=?, descripcion=?, cantidad=?, precio_unitario=?, tipo=? WHERE id_stock=?");
    $stmt->bind_param("ssidsi", $nombre, $descripcion, $cantidad, $precio_unitario, $tipo, $id);
    $stmt->execute();
    $stmt->close();
    echo 'ok';
    exit();
}

header("Location: stock.php");
exit();
