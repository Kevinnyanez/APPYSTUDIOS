<?php
include 'includes/db.php';

// Cargar presupuestos con el nombre del cliente
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

<!-- Modal para crear presupuesto -->
<div id="modalPresupuesto" class="modal">
  <div class="modal-contenido">
    <span class="cerrar-modal" id="cerrarModalPresupuesto">&times;</span>
    <h2>Nuevo Presupuesto</h2>
    <form id="formPresupuestoModal">
      <!-- Paso 1: Cliente -->
      <div id="paso1" class="paso activo">
        <h3>Paso 1: Seleccionar Cliente</h3>
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
          <button type="button" id="cancelarModalPresupuesto">Cancelar</button>
          <button type="button" id="siguientePaso1">Siguiente</button>
        </div>
      </div>

      <!-- Paso 2: Productos -->
      <div id="paso2" class="paso">
        <h3>Paso 2: Agregar Productos</h3>
        <label>Fecha:</label>
        <input type="date" name="fecha_creacion" required value="<?= date('Y-m-d') ?>">
        <label>Producto:</label>
        <div style="margin-bottom:10px; position:relative;">
          <input type="text" id="inputBuscarProducto" placeholder="Buscar producto..." autocomplete="off">
          <input type="hidden" id="idProductoSeleccionado">
          <input type="hidden" id="precioProductoSeleccionado">
          <ul id="sugerenciasProductos" style="position:absolute;z-index:10;left:0;right:0;max-height:180px;overflow-y:auto;background:#222;border:1px solid #444;border-radius:0 0 6px 6px;display:none;margin:0;padding:0;list-style:none;"></ul>
        </div>
        <button type="button" id="btnAgregarItem">Agregar</button>
        <div style="margin: 10px 0;">
          <label>Recargo por producto (%): <input type="number" id="recargoProducto" value="2.5" min="0" step="0.1" style="width:70px;"> </label>
        </div>
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
        <div class="navegacion">
          <button type="button" id="anteriorPaso2">Anterior</button>
          <button type="button" id="siguientePaso2">Siguiente</button>
        </div>
      </div>

      <!-- Paso 3: Resumen -->
      <div id="paso3" class="paso">
        <h3>Paso 3: Resumen del Presupuesto</h3>
        <div class="resumen-presupuesto">
          <h4>Datos del Cliente</h4>
          <div id="resumenCliente"></div>
          <h4>Productos</h4>
          <div id="resumenProductos"></div>
          <h4>Totales</h4>
          <div id="resumenTotales"></div>
        </div>
        <div class="navegacion">
          <button type="button" id="anteriorPaso3">Anterior</button>
          <button type="submit">Confirmar Presupuesto</button>
        </div>
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
      <th>Estado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($presupuestos)): ?>
      <tr><td colspan="6" class="sin-presupuestos">No hay presupuestos registrados.</td></tr>
    <?php else: ?>
      <?php foreach ($presupuestos as $p): ?>
        <tr>
          <td><?= $p['id_presupuesto'] ?></td>
          <td><?= htmlspecialchars($p['nombre_cliente']) ?></td>
          <td><?= $p['fecha_creacion'] ?></td>
          <td>$<?= number_format($p['total'], 2) ?></td>
          <td><?= ucfirst($p['estado']) ?></td>
          <td>
            <a href="presupuesto_form.php?id_presupuesto=<?= $p['id_presupuesto'] ?>" class="btn-link editar">Ver / Editar</a>
            <a href="#" class="btn-link btn-ver-items" data-id="<?= $p['id_presupuesto'] ?>">Ver ítems</a>
            <?php if ($p['estado'] === 'abierto'): ?>
              | <a href="presupuesto_action.php?cerrar=<?= $p['id_presupuesto'] ?>" onclick="return confirm('¿Cerrar presupuesto?')" class="btn-link cerrar">Cerrar</a>
            <?php endif; ?>
            | <a href="presupuesto_action.php?delete=<?= $p['id_presupuesto'] ?>" onclick="return confirm('¿Eliminar presupuesto?')" class="btn-link eliminar">Eliminar</a>
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
<script src="test.js"></script>
</body>
</html>
