
<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'includes/db.php';
require_once 'dompdf-3.1.0/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
 // Este archivo debe definir $conn (MySQLi)

if (isset($_GET['descargar_pdf'])) {
    $id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        die("ID inválido para descargar PDF");
    }

    // Obtener presupuesto y cliente
    $sql = "SELECT p.*, c.nombre AS nombre_cliente, c.email AS email_cliente
            FROM presupuestos p
            JOIN clientes c ON p.id_cliente = c.id_cliente
            WHERE p.id_presupuesto = $id";
    $result = $conn->query($sql);
    $presupuesto = $result->fetch_assoc();

    if (!$presupuesto) {
        die("No se encontró el presupuesto con ID $id");
    }

    // Obtener ítems del presupuesto
   $sql_items = "SELECT pi.*, s.nombre AS nombre_producto
              FROM presupuesto_items pi
              JOIN stock s ON pi.id_stock = s.id_stock
              WHERE pi.id_presupuesto = $id";

    $result_items = $conn->query($sql_items);
    $items = [];
    while ($row = $result_items->fetch_assoc()) {
        $items[] = $row;
    }

    // Formatear fecha
    $fecha_formateada = date("d/m/Y", strtotime($presupuesto['fecha_creacion']));

    // Crear HTML
    $html = '
        <style>
            body {
    font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
    color: #222;
    margin: 20px;
    font-size: 12pt;
}

h1 {
    color: #004080;
    border-bottom: 3px solid #004080;
    padding-bottom: 8px;
    font-weight: 700;
    font-size: 24pt;
    margin-bottom: 15px;
}

h2 {
    color: #004080;
    font-weight: 600;
    font-size: 16pt;
    margin-top: 30px;
    margin-bottom: 10px;
    border-bottom: 1px solid #ccc;
    padding-bottom: 4px;
}

p {
    margin: 5px 0;
    line-height: 1.4;
}

strong {
    color: #004080;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 11pt;
}

th, td {
    border: 1px solid #ccc;
    padding: 8px 12px;
    text-align: left;
}

th {
    background-color: #e0e7f1;
    color: #004080;
    font-weight: 600;
}

tbody tr:nth-child(even) {
    background-color: #f9fafc;
}

tfoot tr {
    font-weight: 700;
    background-color: #d0d8e8;
}

.footer {
    margin-top: 40px;
    font-size: 9pt;
    color: #555;
    border-top: 1px solid #ccc;
    padding-top: 10px;
    text-align: center;
    font-style: italic;
}

.pdf-footer {
    margin-top: 50px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    font-family: Segoe UI, sans-serif;
    font-size: 10pt;
    color: #444;
    line-height: 1.5;
    text-align: center;
}

.pdf-footer h4 {
    font-size: 11pt;
    color: #333;
    margin-bottom: 5px;
    font-weight: 600;
}

.pdf-footer p {
    margin: 3px 0;
}

.pdf-footer .contacto {
    margin-top: 10px;
    font-size: 9pt;
    color: #666;
}

.pdf-footer .nota {
    margin-top: 15px;
    font-style: italic;
    color: #777;
    border-left: 3px solid #ccc;
    padding-left: 10px;
    font-size: 9.5pt;
}

.encabezado {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #004080;
}

.encabezado h1 {
    font-size: 22pt;
    font-weight: 700;
    color: #004080;
    margin-bottom: 10px;
}

.encabezado p {
    font-size: 11pt;
    line-height: 1.6;
    color: #222;
    margin: 3px 0;
}

        </style>
    <div class="encabezado">
        <h1>Presupuesto</h1>
        <p><strong>Cliente:</strong> ' . htmlspecialchars($presupuesto['nombre_cliente']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($presupuesto['email_cliente']) . '</p>
        <p><strong>Fecha:</strong> ' . $fecha_formateada . '</p>
        <p><strong>Total:</strong> $' . number_format($presupuesto['total_con_recargo'], 2, ',', '.') . '</p>
    </div>
        <h2>Ítems</h2>
        <table>
            <thead>
                <tr>
                    <th>Materiales</th>
                    <th>Cantidad</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>';

    $total_subtotal = 0;
    foreach ($items as $item) {
        $subtotal = $item['cantidad'] * $item['subtotal'];
        $total_subtotal += $subtotal;

        $html .= '
            <tr>
                <td>' . htmlspecialchars($item['nombre_producto']) . '</td>
                <td>' . $item['cantidad'] . '</td>
            </tr>';
    }

    // Agregamos fila de total final
    $html .= '
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="3" style="text-align: right;">Total Ítems:</td>
                <td>$' . number_format($presupuesto['total_con_recargo'], 2, ',', '.') . '</td>
            </tr>
        </tbody>
    </table>
    <div class="pdf-footer">
    <h4>¡Gracias por consultarnos!</h4>
    <p>Esperamos con ansias trabajar con vos.</p>
    
    <div class="contacto">
        <p><strong>IG:</strong> @fd.sonandobajito</p>
        <p><strong>Teléfono:</strong> +54 9 11 1234-5678</p>
    </div>
    
    <div class="nota">
        <strong>Nota:</strong> Estamos a tu disposición para cualquier modificación o sugerencia. Gracias por tu tiempo.
    </div>
</div>';

    // Generar PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("presupuesto_{$id}.pdf", ["Attachment" => true]);
    exit;
}






// Cargar presupuestos con el nombre del clientee
$sql = "SELECT p.*, c.nombre AS nombre_cliente
        FROM presupuestos p
        JOIN clientes c ON p.id_cliente = c.id_cliente
        ORDER BY p.fecha_creacion DESC";
$result = $conn->query($sql);

$presupuestos = [];
while ($row = $result->fetch_assoc()) {
    $presupuestos[] = $row;
}

// Cargar clientes y stock para el modal
$clientes_result = $conn->query("SELECT id_cliente, nombre, telefono, email, direccion FROM clientes ORDER BY nombre");
$clientes = [];
while ($row = $clientes_result->fetch_assoc()) {
    $clientes[] = $row;
}
$stock_result = $conn->query("SELECT id_stock, nombre, precio_unitario FROM stock ORDER BY nombre");
$stock_items = [];
while ($row = $stock_result->fetch_assoc()) {
    $stock_items[] = $row;
}
  // ajusta la ruta si hace falta


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Presupuestos</title>

  <style>
body {
  background-color: #cbd5e1;
  color: #eee;
  font-family: 'Segoe UI', sans-serif;
}

.titulo-presupuestos {
  text-align: center;
  margin: 20px 0;
  color: #00bcd4;
}

.btn-nuevo {
  display: inline-block;
  margin: 10px 35px;
  padding: 10px 20px;
  background-color: #00bcd4;
  color: #fff;
  text-decoration: none;
  border-radius: 6px;
  transition: background 0.3s;
}

.btn-nuevo:hover {
  background-color: #0097a7;
}

.tabla-presupuestos {
  width: 95%;
  margin: 0 auto;
  border-collapse: collapse;
  background-color: #2b2b2b;
  box-shadow: 0 4px 10px rgba(0,0,0,0.5);
  border-radius: 8px;
  overflow: hidden;
}

.tabla-presupuestos th,
.tabla-presupuestos td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #444;
}

.tabla-presupuestos th {
  background-color: #333;
  color: #fff;
  font-weight: bold;
}

.tabla-presupuestos tr:nth-child(even) {
  background-color: #262626;
}

.tabla-presupuestos tr:hover {
  background-color: #383838;
}

.sin-presupuestos {
  text-align: center;
  padding: 20px;
  color: #aaa;
  font-style: italic;
}

.btn-link {
  color: #00bcd4;
  text-decoration: none;
  margin-right: 8px;
  transition: color 0.2s ease;
}

.btn-link:hover {
  color: #03a9f4;
  text-decoration: underline;
}

.eliminar {
  color: #e57373;
}

.eliminar:hover {
  color: #ef5350;
}

.cerrar {
  color: #fbc02d;
}

.cerrar:hover {
  color: #fdd835;
}

/* ✅ Responsive */
@media (max-width: 768px) {
  .tabla-presupuestos thead {
    display: none;
  }

  .tabla-presupuestos tr {
    display: block;
    margin-bottom: 20px;
    background-color: #2b2b2b;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    overflow: hidden;
  }

  .tabla-presupuestos td {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    border-bottom: 1px solid #333;
  }

  .tabla-presupuestos td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #aaa;
  }

  .tabla-presupuestos td:last-child {
    border-bottom: none;
  }
}

/* Responsive básico */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }

            nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            nav a {
                margin: 8px 10px;
            }

            nav a.logout {
                margin-left: 0;
                width: 100%;
                text-align: center;
            }
        }
      nav {
            display: flex;
            align-items: center;
            background: #1f2937;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgb(0 0 0 / 0.1);
            margin-bottom: 30px;
        }

        nav a {
            color: #cbd5e1;
            text-decoration: none;
            margin-right: 25px;
            font-weight: 600;
            transition: color 0.3s ease;
            padding: 6px 8px;
            border-radius: 4px;
        }

        nav a:hover {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.15);
        }

        nav a.logout {
            margin-left: auto;
            background: #ef4444;
            color: white !important;
            padding: 8px 15px;
            font-weight: 700;
            transition: background 0.3s ease;
        }

        nav a.logout:hover {
            background: #b91c1c;
        }

.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0; width: 100%; height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4);
}
.modal-contenido {
  background-color: #fff;
  margin: 5% auto;
  padding: 20px;
  border-radius: 10px;
  width: 90%; max-width: 900px;
  position: relative;
  color: #222;
}
.cerrar-modal {
  color: #aaa;
  position: absolute;
  top: 10px; right: 20px;
  font-size: 32px;
  font-weight: bold;
  cursor: pointer;
}
.cerrar-modal:hover { color: #222; }

/* Wizard bar */
.wizard-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  background: #222;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
}
.wizard-step {
  flex: 1;
  text-align: center;
  padding: 12px 0;
  color: #cbd5e1;
  font-weight: 600;
  background: #222;
  border-right: 1px solid #333;
  transition: background 0.3s, color 0.3s;
}
.wizard-step:last-child { border-right: none; }
.wizard-step.activo, .wizard-step.active {
  background: #00bcd4;
  color: #fff;
}

/* Mejoras visuales para los pasos */
.paso-modal {
  padding: 18px 0 0 0;
  min-height: 320px;
}
.form-group {
  margin-bottom: 18px;
}
input, select, button {
  font-size: 1.1em;
}
input[type="text"], input[type="email"], input[type="number"], input[type="date"], select {
  width: 100%;
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid #bbb;
  margin-top: 4px;
  margin-bottom: 8px;
  background: #f8fafc;
  color: #222;
  transition: border 0.2s;
}
input:focus, select:focus {
  border: 1.5px solid #00bcd4;
  outline: none;
}
.btn-siguiente, .btn-anterior, .btn-confirmar, .btn-cancelar, .btn-agregar {
  background: #00bcd4;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 10px 22px;
  margin: 8px 6px 0 0;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-siguiente:hover, .btn-anterior:hover, .btn-confirmar:hover, .btn-cancelar:hover, .btn-agregar:hover {
  background: #0097a7;
}
.btn-cancelar {
  background: #e57373;
}
.btn-cancelar:hover {
  background: #ef5350;
}
.tabla-items-wrapper {
  overflow-x: auto;
  margin-bottom: 10px;
}
#tablaItems th, #tablaItems td {
  font-size: 1em;
  padding: 8px 10px;
}
#tablaItems th {
  background: #222;
  color: #fff;
}
#tablaItems tr:nth-child(even) {
  background: #f3f3f3;
}
#tablaItems tr:nth-child(odd) {
  background: #e0e7ef;
}
#tablaItems td {
  color: #222;
}
@media (max-width: 600px) {
  .modal-contenido { padding: 8px; }
  .wizard-bar { font-size: 0.95em; }
  .paso-modal { padding: 8px 0 0 0; }
  #tablaItems th, #tablaItems td { font-size: 0.95em; }
}

.sugerencias-lista {
  position: absolute;
  z-index: 10;
  left: 0; right: 0;
  max-height: 180px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #00bcd4;
  border-radius: 0 0 6px 6px;
  display: none;
  margin: 0;
  padding: 0;
  list-style: none;
}
.sugerencias-lista li {
  color: #222;
  background: #fff;
  padding: 10px 14px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
  font-size: 1em;
}
.sugerencias-lista li:last-child { border-bottom: none; }
.sugerencias-lista li:hover { background: #e0f7fa; }

.modal-paso {
  max-width: 420px;
  width: 98%;
  margin: 0 auto;
  padding: 18px 10px 0 10px;
}
@media (max-width: 600px) {
  .modal-paso { max-width: 99vw; padding: 8px 2vw 0 2vw; }
  .tabla-items-wrapper { overflow-x: auto; }
  #tablaItems th, #tablaItems td { font-size: 0.95em; }
}
.form-group label, .form-group input, .form-group select, .form-group button {
  font-size: 1em;
}
input, select, button {
  font-size: 1em;
  box-sizing: border-box;
}
input[type="text"], input[type="email"], input[type="number"], input[type="date"], select {
  width: 100%;
  padding: 8px 10px;
  border-radius: 6px;
  border: 1px solid #bbb;
  margin-top: 4px;
  margin-bottom: 8px;
  background: #f8fafc;
  color: #222;
  transition: border 0.2s;
}
input:focus, select:focus {
  border: 1.5px solid #00bcd4;
  outline: none;
}
.btn-siguiente, .btn-anterior, .btn-confirmar, .btn-cancelar, .btn-agregar {
  background: #00bcd4;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 8px 18px;
  margin: 8px 6px 0 0;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 1em;
}
.btn-siguiente:hover, .btn-anterior:hover, .btn-confirmar:hover, .btn-cancelar:hover, .btn-agregar:hover {
  background: #0097a7;
}
.btn-cancelar {
  background: #e57373;
}
.btn-cancelar:hover {
  background: #ef5350;
}
.tabla-items-wrapper {
  overflow-x: auto;
  margin-bottom: 10px;
}
#tablaItems th, #tablaItems td {
  font-size: 1em;
  padding: 8px 10px;
}
#tablaItems th {
  background: #222;
  color: #fff;
}
#tablaItems tr:nth-child(even) {
  background: #f3f3f3;
}
#tablaItems tr:nth-child(odd) {
  background: #e0e7ef;
}
#tablaItems td {
  color: #222;
}
</style>

</head>
<body>

<nav>
        <a href="stock.php">Ver Stock</a>
        <a href="clientes.php">clientes</a>
        <a href="ventas.php">Ventas</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="recomendaciones.php">Recomendaciones</a>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </nav>

<h1 class="titulo-presupuestos">Presupuestos</h1>

<a href="#" class="btn-nuevo" id="abrirModalPresupuesto">+ Nuevo Presupuesto</a>

<!-- Modal Paso 1: Cliente -->
<div id="modalCliente" class="modal">
  <div class="modal-contenido modal-paso">
    <span class="cerrar-modal" id="cerrarModalCliente">&times;</span>
    <h2>Nuevo Presupuesto - Paso 1</h2>
    <form id="formCliente">
      <div class="form-group">
        <label>Cliente:</label>
        <select name="id_cliente" id="selectCliente" required>
          <option value="">Seleccione un cliente</option>
          <option value="nuevo">+ Nuevo cliente</option>
          <?php foreach ($clientes as $cliente): ?>
            <option value="<?= $cliente['id_cliente'] ?>"
              data-telefono="<?= htmlspecialchars($cliente['telefono']) ?>"
              data-email="<?= htmlspecialchars($cliente['email']) ?>"
              data-direccion="<?= htmlspecialchars($cliente['direccion']) ?>">
              <?= htmlspecialchars($cliente['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="cliente-info" id="clienteInfo" style="display:none;">
        <p><strong>Teléfono:</strong> <span id="cliTelefono"></span></p>
        <p><strong>Email:</strong> <span id="cliEmail"></span></p>
        <p><strong>Dirección:</strong> <span id="cliDireccion"></span></p>
      </div>
      <div id="nuevoClienteFields" style="display:none; margin-bottom:10px;">
        <label>Nombre:</label>
        <input type="text" id="nuevoNombre" name="nuevo_nombre" placeholder="Nombre del cliente">
        <label>Email:</label>
        <input type="email" id="nuevoEmail" name="nuevo_email" placeholder="Email">
        <label>Teléfono:</label>
        <input type="text" id="nuevoTelefono" name="nuevo_telefono" placeholder="Teléfono">
        <label>Dirección:</label>
        <input type="text" id="nuevoDireccion" name="nuevo_direccion" placeholder="Dirección">
      </div>
      <div class="navegacion">
        <button type="button" id="cancelarModalCliente" class="btn-cancelar">Cancelar</button>
        <button type="button" id="siguienteCliente" class="btn-siguiente">Siguiente</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Paso 2: Productos -->
<div id="modalProductos" class="modal">
  <div class="modal-contenido modal-paso">
    <span class="cerrar-modal" id="cerrarModalProductos">&times;</span>
    <h2>Nuevo Presupuesto - Paso 2</h2>
    <form id="formProductos">
      <div class="form-group">
        <label>Fecha:</label>
        <input type="date" name="fecha_creacion" id="fecha_creacion" required value="<?= date('Y-m-d') ?>">
      </div>
      <div class="form-group">
        <label>Producto:</label>
        <div style="margin-bottom:10px; position:relative;">
          <input type="text" id="inputBuscarProducto" placeholder="Buscar producto..." autocomplete="off">
          <input type="hidden" id="idProductoSeleccionado">
          <input type="hidden" id="precioProductoSeleccionado">
          <ul id="sugerenciasProductos" class="sugerencias-lista"></ul>
        </div>
        <button type="button" id="btnAgregarItem" class="btn-agregar">Agregar</button>
      </div>
      <div class="form-group">
        <label>Recargo por producto (%): <input type="number" id="recargoProducto" value="2.5" min="0" step="0.1" style="width:70px;"> </label>
      </div>
      <div class="tabla-items-wrapper">
        <table id="tablaItems">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Precio Unitario</th>
              <th>Subtotal</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-align:right"><strong>Total:</strong></td>
              <td id="totalPresupuesto">$0.00</td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" style="text-align:right"><strong>Recargo al total (%):</strong></td>
              <td colspan="2"><input type="number" id="recargoTotal" value="10" min="0" step="0.1" style="width:70px;"></td>
            </tr>
            <tr>
              <td colspan="3" style="text-align:right"><strong>Total con recargo:</strong></td>
              <td id="totalConRecargo">$0.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="navegacion">
        <button type="button" id="anteriorProductos" class="btn-anterior">Anterior</button>
        <button type="button" id="siguienteProductos" class="btn-siguiente">Siguiente</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Paso 3: Resumen -->
<div id="modalResumen" class="modal">
  <div class="modal-contenido modal-paso">
    <span class="cerrar-modal" id="cerrarModalResumen">&times;</span>
    <h2>Nuevo Presupuesto - Paso 3</h2>
    <form id="formResumen">
      <div class="resumen-presupuesto">
        <h4>Datos del Cliente</h4>
        <div id="resumenCliente"></div>
        <h4>Productos</h4>
        <div id="resumenProductos"></div>
        <h4>Totales</h4>
        <div id="resumenTotales"></div>
      </div>
      <div class="navegacion">
        <button type="button" id="anteriorResumen" class="btn-anterior">Anterior</button>
        <button type="submit" class="btn-confirmar">Confirmar Presupuesto</button>
      </div>
    </form>
  </div>
</div>

<table class="tabla-presupuestos">
  <thead>
    <tr>
      <th>ID</th>
      <th>Cliente</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Total Con Recargo</th>
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($presupuestos)): ?>
      <tr><td colspan="7" class="sin-presupuestos">No hay presupuestos registrados.</td></tr>
    <?php else: ?>
      <?php foreach ($presupuestos as $p): ?>
        <tr>
          <td><?= $p['id_presupuesto'] ?></td>
          <td><?= htmlspecialchars($p['nombre_cliente']) ?></td>
          <td>
          <?php
          $fecha = new DateTime($p['fecha_creacion']);
          $formatter = new IntlDateFormatter(
              'es_ES', 
              IntlDateFormatter::LONG, 
              IntlDateFormatter::NONE,
             null,
             null,
             "d 'de' MMMM 'de' yyyy"
          );
          echo $formatter->format($fecha);
          ?>
          </td>
          <td>$<?= number_format($p['total'], 2) ?></td>
          <td>$<?= number_format($p['total_con_recargo'], 2) ?></td>
          <td><?= ucfirst($p['estado']) ?></td>
          <td>
            <a href="presupuesto_form.php?id_presupuesto=<?= $p['id_presupuesto'] ?>" class="btn-link editar">Ver / Editar</a>
            <a href="#" class="btn-link btn-ver-items" data-id="<?= $p['id_presupuesto'] ?>">Ver ítems</a>
            <?php if ($p['estado'] === 'abierto'): ?>
              | <a href="presupuesto_action.php?cerrar=<?= $p['id_presupuesto'] ?>" onclick="return confirm('¿Cerrar presupuesto?')" class="btn-link cerrar">Cerrar</a>
            <?php endif; ?>
            | <a href="presupuesto_action.php?delete=<?= $p['id_presupuesto'] ?>" onclick="return confirm('¿Eliminar presupuesto?')" class="btn-link eliminar">Eliminar</a>
              | <a href="presupuestos.php?descargar_pdf=1&id=<?= $p['id_presupuesto'] ?>" target="_blank" class="btn-link descargar-pdf">Descargar PDF</a>

          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<script>
window.productosPresupuesto = [
  <?php foreach ($stock_items as $item): ?>
    { id: "<?= $item['id_stock'] ?>", nombre: "<?= htmlspecialchars(strip_tags($item['nombre']), ENT_QUOTES) ?>", precio: "<?= $item['precio_unitario'] ?>" },
  <?php endforeach; ?>
];
</script>
<script src="presupuestos.js"></script>
<?php include 'footer.php'; ?>

</body>
</html>
