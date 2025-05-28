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
      <label>Cliente:</label>
      <select name="id_cliente" id="selectCliente" required>
        <option value="">Seleccione un cliente</option>
        <?php foreach ($clientes as $cliente): ?>
          <option value="<?= $cliente['id_cliente'] ?>"
            data-telefono="<?= htmlspecialchars($cliente['telefono']) ?>"
            data-email="<?= htmlspecialchars($cliente['email']) ?>"
            data-direccion="<?= htmlspecialchars($cliente['direccion']) ?>">
            <?= htmlspecialchars($cliente['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="cliente-info" id="clienteInfo">
        <p><strong>Teléfono:</strong> <span id="cliTelefono"></span></p>
        <p><strong>Email:</strong> <span id="cliEmail"></span></p>
        <p><strong>Dirección:</strong> <span id="cliDireccion"></span></p>
      </div>
      <label>Fecha:</label>
      <input type="date" name="fecha_creacion" required value="<?= date('Y-m-d') ?>"><br><br>
      <hr>
      <h3>Agregar ítems al presupuesto</h3>
      <label>Producto:</label>
      <select id="selectStock">
        <option value="">Seleccione un producto</option>
        <?php foreach ($stock_items as $item): ?>
          <option value="<?= $item['id_stock'] ?>" data-precio="<?= $item['precio_unitario'] ?>">
            <?= htmlspecialchars(strip_tags($item['nombre']), ENT_QUOTES) ?> – $<?= number_format($item['precio_unitario'], 2) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="button" id="btnAgregarItem">Agregar</button>
      <!-- Inputs de recargo -->
      <div style="margin: 10px 0;">
        <label>Recargo por producto (%): <input type="number" id="recargoProducto" value="2.5" min="0" step="0.1" style="width:70px;"> </label>
      </div>
      <table id="tablaItems" style="margin-top: 20px;">
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
      <br>
      <button type="submit">Crear</button>
      <button type="button" id="cancelarModalPresupuesto">Cancelar</button>
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
document.addEventListener('DOMContentLoaded', () => {
  // Modal abrir/cerrar
  document.getElementById('abrirModalPresupuesto').onclick = function(e) {
    e.preventDefault();
    limpiarModalPresupuesto();
    document.getElementById('modalPresupuesto').style.display = 'block';
  };
  document.getElementById('cerrarModalPresupuesto').onclick = function() {
    document.getElementById('modalPresupuesto').style.display = 'none';
  };
  document.getElementById('cancelarModalPresupuesto').onclick = function() {
    document.getElementById('modalPresupuesto').style.display = 'none';
  };
  window.onclick = function(event) {
    const modal = document.getElementById('modalPresupuesto');
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  };

  // Limpiar modal (para crear nuevo)
  function limpiarModalPresupuesto() {
    document.getElementById('formPresupuestoModal').reset();
    document.querySelector('#formPresupuestoModal input[name="id_presupuesto"]')?.remove();
    document.querySelector('#tablaItems tbody').innerHTML = '';
    document.getElementById('totalPresupuesto').textContent = '$0.00';
  }

  // Cargar presupuesto en el modal para editar
  function cargarPresupuestoEnModal(id) {
    fetch('presupuesto_action.php?get_presupuesto=' + id)
      .then(res => res.json())
      .then(data => {
        limpiarModalPresupuesto();
        const f = document.getElementById('formPresupuestoModal');
        // id_presupuesto hidden
        let idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_presupuesto';
        idInput.value = data.presupuesto.id_presupuesto;
        f.appendChild(idInput);
        // Cliente
        f.id_cliente.value = data.presupuesto.id_cliente;
        // Fecha
        f.fecha_creacion.value = data.presupuesto.fecha_creacion.substr(0,10);
        // Ítems
        const tbody = document.querySelector('#tablaItems tbody');
        tbody.innerHTML = '';
        data.items.forEach(item => {
          const row = document.createElement('tr');
          row.dataset.idStock = item.id_stock;
          row.innerHTML = `
            <td>
              ${item.nombre_stock}
              <input type="hidden" name="id_stock[]" value="${item.id_stock}">
            </td>
            <td><input type="number" name="cantidad[]" value="${item.cantidad}" min="1" class="input-cantidad"></td>
            <td><input type="number" name="precio_unitario[]" value="${parseFloat(item.precio_unitario).toFixed(2)}" readonly></td>
            <td class="td-subtotal">
              <span class="subtotal-text">${parseFloat(item.subtotal).toFixed(2)}</span>
              <input type="hidden" name="subtotal[]" value="${parseFloat(item.subtotal).toFixed(2)}">
            </td>
            <td><button type="button" class="btn-eliminar-item">Eliminar</button></td>
          `;
          tbody.appendChild(row);
          // Listeners para recalcular
          const qtyInput = row.querySelector('.input-cantidad');
          const precioInput = row.querySelector('input[name="precio_unitario[]"]');
          const textSub = row.querySelector('.subtotal-text');
          const hiddenSub = row.querySelector('input[name="subtotal[]"]');
          function recalc() {
            const qty = parseFloat(qtyInput.value) || 0;
            const pr  = parseFloat(precioInput.value) || 0;
            const st  = qty * pr;
            textSub.textContent = st.toFixed(2);
            hiddenSub.value = st.toFixed(2);
            calcularTotal();
          }
          qtyInput.addEventListener('input', recalc);
          row.querySelector('.btn-eliminar-item')
            .addEventListener('click', () => { row.remove(); calcularTotal(); });
        });
        calcularTotal();
        document.getElementById('modalPresupuesto').style.display = 'block';
      });
  }

  // Botones de editar
  document.querySelectorAll('.btn-link.editar').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.href.split('id_presupuesto=')[1];
      cargarPresupuestoEnModal(id);
    };
  });

  // Botones de eliminar
  document.querySelectorAll('.btn-link.eliminar').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      if (!confirm('¿Eliminar presupuesto?')) return;
      const id = this.href.split('delete=')[1];
      fetch('presupuesto_action.php?delete=' + id)
        .then(res => res.text())
        .then(resp => { location.reload(); });
    };
  });

  // Botones de cerrar
  document.querySelectorAll('.btn-link.cerrar').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      if (!confirm('¿Cerrar presupuesto?')) return;
      const id = this.href.split('cerrar=')[1];
      fetch('presupuesto_action.php?cerrar=' + id)
        .then(res => res.text())
        .then(resp => { location.reload(); });
    };
  });

  // JS para agregar ítems y calcular totales
  const selectStock     = document.getElementById('selectStock');
  const btnAgregarItem  = document.getElementById('btnAgregarItem');
  const tablaItemsBody  = document.querySelector('#tablaItems tbody');
  const totalPresupuesto= document.getElementById('totalPresupuesto');
  const recargoProductoInput = document.getElementById('recargoProducto');
  const recargoTotalInput = document.getElementById('recargoTotal');
  const totalConRecargo = document.getElementById('totalConRecargo');

  if (!selectStock || !btnAgregarItem || !tablaItemsBody || !totalPresupuesto || !recargoProductoInput || !recargoTotalInput || !totalConRecargo) return;

  function calcularTotal() {
    let total = 0;
    document.querySelectorAll('#tablaItems tbody tr').forEach(row => {
      const sub = parseFloat(row.querySelector('input[name="subtotal[]"]').value) || 0;
      total += sub;
    });
    document.getElementById('totalPresupuesto').textContent = '$' + total.toFixed(2);
    // Calcular recargo al total
    const recargoTotal = parseFloat(recargoTotalInput.value) || 0;
    const totalFinal = total * (1 + recargoTotal / 100);
    totalConRecargo.textContent = '$' + totalFinal.toFixed(2);
  }

  // Recalcular precios de productos al cambiar el recargo por producto
  recargoProductoInput.addEventListener('input', () => {
    document.querySelectorAll('#tablaItems tbody tr').forEach(row => {
      const precioBase = parseFloat(row.dataset.precioBase);
      const recargo = parseFloat(recargoProductoInput.value) || 0;
      const precioConRecargo = precioBase * (1 + recargo / 100);
      row.querySelector('input[name="precio_unitario[]"]').value = precioConRecargo.toFixed(2);
      // Recalcular subtotal
      const qty = parseFloat(row.querySelector('input[name="cantidad[]"]').value) || 0;
      const subtotal = qty * precioConRecargo;
      row.querySelector('.subtotal-text').textContent = subtotal.toFixed(2);
      row.querySelector('input[name="subtotal[]"]').value = subtotal.toFixed(2);
    });
    calcularTotal();
  });

  // Recalcular total con recargo al total
  recargoTotalInput.addEventListener('input', calcularTotal);

  // Modifica la función de agregar ítems para guardar el precio base y aplicar recargo
  btnAgregarItem.addEventListener('click', () => {
    const opt = selectStock.selectedOptions[0];
    if (!opt || !opt.value) return alert('Seleccione un producto');
    const idStock = opt.value;
    const nombre  = opt.text;
    const precioBase  = parseFloat(opt.dataset.precio) || 0;
    const recargo = parseFloat(recargoProductoInput.value) || 0;
    const precio = precioBase * (1 + recargo / 100);
    if ([...tablaItemsBody.children].some(r => r.dataset.idStock === idStock)) {
      return alert('Ya agregaste este producto');
    }
    const row = document.createElement('tr');
    row.dataset.idStock = idStock;
    row.dataset.precioBase = precioBase;
    row.innerHTML = `
      <td>
        ${nombre}
        <input type="hidden" name="id_stock[]" value="${idStock}">
      </td>
      <td><input type="number" name="cantidad[]" value="1" min="1" class="input-cantidad"></td>
      <td><input type="number" name="precio_unitario[]" value="${precio.toFixed(2)}" readonly></td>
      <td class="td-subtotal">
        <span class="subtotal-text">${precio.toFixed(2)}</span>
        <input type="hidden" name="subtotal[]" value="${precio.toFixed(2)}">
      </td>
      <td><button type="button" class="btn-eliminar-item">Eliminar</button></td>
    `;
    tablaItemsBody.appendChild(row);
    const qtyInput = row.querySelector('.input-cantidad');
    const precioInput = row.querySelector('input[name="precio_unitario[]"]');
    const textSub = row.querySelector('.subtotal-text');
    const hiddenSub = row.querySelector('input[name="subtotal[]"]');
    function recalc() {
      const qty = parseFloat(qtyInput.value) || 0;
      const pr  = parseFloat(precioInput.value) || 0;
      const st  = qty * pr;
      textSub.textContent = st.toFixed(2);
      hiddenSub.value = st.toFixed(2);
      calcularTotal();
    }
    qtyInput.addEventListener('input', recalc);
    row.querySelector('.btn-eliminar-item')
      .addEventListener('click', () => { row.remove(); calcularTotal(); });
    calcularTotal();
  });

  // AJAX para enviar el formulario
  document.getElementById('formPresupuestoModal').onsubmit = function(e) {
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);
    fetch('presupuesto_action.php', {
      method: 'POST',
      body: datos
    })
    .then(res => res.text())
    .then(resp => {
      document.getElementById('modalPresupuesto').style.display = 'none';
      location.reload();
    })
    .catch(err => {
      alert('Error al crear presupuesto');
    });
  };
});
</script>

</body>
</html>
