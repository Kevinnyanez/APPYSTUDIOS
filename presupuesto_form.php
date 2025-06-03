<?php
// Al principio de presupuesto_form.php, antes de cualquier output:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';
// … resto de tu código …


$id_presupuesto = $_GET['id_presupuesto'] ?? null;

$presupuesto = null;
$items = [];

// Cargo clientes para dropdown
$clientes_result = $conn->query("SELECT id_cliente, nombre, telefono, email, direccion FROM clientes ORDER BY nombre");
$clientes = [];
while ($row = $clientes_result->fetch_assoc()) {
    $clientes[] = $row;
}

// Cargo stock para dropdown
$stock_result = $conn->query("SELECT id_stock, nombre, precio_unitario FROM stock ORDER BY nombre");
$stock_items = [];
while ($row = $stock_result->fetch_assoc()) {
    $stock_items[] = $row;
}

if ($id_presupuesto) {
    // Cargo presupuesto existente
    $stmt = $conn->prepare("SELECT * FROM presupuestos WHERE id_presupuesto = ?");
    $stmt->bind_param("i", $id_presupuesto);
    $stmt->execute();
    $result = $stmt->get_result();
    $presupuesto = $result->fetch_assoc();
    $stmt->close();

    if ($presupuesto) {
        // Cargo ítems del presupuesto
        $stmt = $conn->prepare("SELECT pi.*, s.nombre AS nombre_stock FROM presupuesto_items pi JOIN stock s ON pi.id_stock = s.id_stock WHERE id_presupuesto = ?");
        $stmt->bind_param("i", $id_presupuesto);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($item = $result->fetch_assoc()) {
            $items[] = $item;
        }
        $stmt->close();
    } else {
        // Si no existe el presupuesto, redirigir o mostrar error
        header("Location: presupuestos.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title><?= $presupuesto ? "Editar Presupuesto" : "Nuevo Presupuesto" ?></title>
  <style>
    /* Contenedor principal del formulario */
form#presupuestoForm {
  background-color: #fff;
  border: 1px solid #ccc;
  padding: 25px;
  border-radius: 10px;
  max-width: 1000px;
  margin: 20px auto;
  font-family: 'Segoe UI', sans-serif;
  box-shadow: 0 0 10px rgba(0,0,0,0.08);
  color: #222;
}

/* Encabezados */
h1, h3 {
  color: #222;
  margin-top: 0;
}

/* Labels */
form#presupuestoForm label {
  display: block;
  font-weight: bold;
  margin: 10px 0 5px;
}

/* Inputs y selects */
form#presupuestoForm input[type="text"],
form#presupuestoForm input[type="number"],
form#presupuestoForm input[type="date"],
form#presupuestoForm select,
form#presupuestoForm textarea {
  width: 100%;
  padding: 8px 10px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 15px;
}

/* Info del cliente */
.cliente-info {
  background-color: #f9f9f9;
  padding: 10px 15px;
  border-left: 4px solid #222;
  margin-bottom: 20px;
  font-size: 14px;
  color: #444;
}

/* Botón agregar item */
#btnAgregarItem {
  background-color: #222;
  color: white;
  border: none;
  padding: 8px 16px;
  margin-top: 10px;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.2s ease;
}
#btnAgregarItem:hover {
  background-color: #444;
}

/* Tabla de ítems */
#tablaItems {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
#tablaItems th,
#tablaItems td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
#tablaItems th {
  background-color: #222;
  color: white;
}
#tablaItems input {
  width: 80px;
}

/* Subtotal y total */
#totalPresupuesto {
  font-weight: bold;
  font-size: 16px;
}

/* Botón eliminar item */
.btn-eliminar-item {
  background-color: #b00020;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-eliminar-item:hover {
  background-color: #d00030;
}

/* Botones de acción */
form#presupuestoForm button[type="submit"],
form#presupuestoForm a {
  display: inline-block;
  margin-right: 15px;
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #222;
  color: white;
  text-decoration: none;
  border: none;
  border-radius: 5px;
  transition: 0.2s;
}
form#presupuestoForm a {
  background-color: #999;
}
form#presupuestoForm button[type="submit"]:hover {
  background-color: #444;
}
form#presupuestoForm a:hover {
  background-color: #777;
}

/* Responsive */
@media (max-width: 768px) {
  #tablaItems th, #tablaItems td {
    font-size: 13px;
    padding: 6px;
  }

  .cliente-info {
    font-size: 13px;
  }

  form#presupuestoForm input[type="number"],
  form#presupuestoForm input[type="text"],
  form#presupuestoForm input[type="date"],
  form#presupuestoForm select {
    font-size: 14px;
  }
}

  </style>
</head>
<body>

<h1><?= $presupuesto ? "Editar Presupuesto" : "Nuevo Presupuesto" ?></h1>

<form action="presupuesto_action.php" method="post" id="presupuestoForm">
  <?php if ($presupuesto): ?>
    <input type="hidden" name="id_presupuesto" value="<?= $presupuesto['id_presupuesto'] ?>">
  <?php endif; ?>

  <label>Cliente:</label><br>
  <select name="id_cliente" id="selectCliente" required>
    <option value="">Seleccione un cliente</option>
    <?php foreach ($clientes as $cliente): ?>
      <option value="<?= $cliente['id_cliente'] ?>" 
        data-telefono="<?= htmlspecialchars($cliente['telefono']) ?>" 
        data-email="<?= htmlspecialchars($cliente['email']) ?>" 
        data-direccion="<?= htmlspecialchars($cliente['direccion']) ?>"
        <?= ($presupuesto && $presupuesto['id_cliente'] == $cliente['id_cliente']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($cliente['nombre']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <div class="cliente-info" id="clienteInfo">
    <p><strong>Teléfono:</strong> <span id="cliTelefono"></span></p>
    <p><strong>Email:</strong> <span id="cliEmail"></span></p>
    <p><strong>Dirección:</strong> <span id="cliDireccion"></span></p>
  </div>

  <label>Fecha:</label><br>
  <input type="date" name="fecha_creacion" required value="<?= $presupuesto['fecha_creacion'] ?? date('Y-m-d') ?>"><br><br>

  <hr>

  <h3>Agregar ítems al presupuesto</h3>

  <label>Producto:</label>
  <select id="selectStock">
    <option value="">Seleccione un producto</option>
   <?php foreach ($stock_items as $item): ?>
  <option
    value="<?= $item['id_stock'] ?>"
    data-precio="<?= $item['precio_unitario'] ?>"
  >
    <?= htmlspecialchars(strip_tags($item['nombre']), ENT_QUOTES) ?> – $<?= number_format($item['precio_unitario'], 2) ?>
  </option>
<?php endforeach; ?>
  </select>
  <button type="button" id="btnAgregarItem">Agregar</button>

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
    <tbody>
      <?php foreach ($items as $item): ?>
      <tr class="item-row" data-id-stock="<?= $item['id_stock'] ?>">
        <td><?= htmlspecialchars($item['nombre_stock']) ?></td>
        <td><input type="number" name="items[<?= $item['id_item'] ?>][cantidad]" value="<?= $item['cantidad'] ?>" min="1" class="input-cantidad"></td>
        <td><input type="number" name="items[<?= $item['id_item'] ?>][precio_unitario]" value="<?= $item['precio_unitario'] ?>" min="0" step="0.01" class="input-precio"></td>
        <td class="td-subtotal"><?= number_format($item['subtotal'], 2) ?></td>
        <td><button type="button" class="btn-eliminar-item">Eliminar</button></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" style="text-align:right"><strong>Total:</strong></td>
        <td id="totalPresupuesto">$<?= number_format($presupuesto['total'] ?? 0, 2) ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <br>
  <button type="submit"><?= $presupuesto ? "Actualizar" : "Crear" ?></button>
  <a href="presupuestos.php">Cancelar</a>
  <hr>
<button type="button" id="btnMostrarDescripcion">Generar PDF con descripción</button>

<div id="contenedorDescripcion" style="display:none; margin-top:15px;">
  <label for="descripcionPdf">Descripción del presupuesto:</label>
  <textarea id="descripcionPdf" rows="4" style="width:100%;"></textarea>
  <button type="button" id="btnGenerarPDF">Descargar PDF</button>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const selectStock     = document.getElementById('selectStock');
  const btnAgregarItem  = document.getElementById('btnAgregarItem');
  const tablaItemsBody  = document.querySelector('#tablaItems tbody');
  const totalPresupuesto= document.getElementById('totalPresupuesto');

  if (!selectStock || !btnAgregarItem || !tablaItemsBody || !totalPresupuesto) {
    console.error('Faltan IDs en el formulario de presupuesto');
    return;
  }

  function calcularTotal() {
    let total = 0;
    tablaItemsBody.querySelectorAll('tr').forEach(row => {
      const sub = parseFloat(row.querySelector('input[name="subtotal[]"]').value) || 0;
      total += sub;
    });
    totalPresupuesto.textContent = '$' + total.toFixed(2);
  }

  btnAgregarItem.addEventListener('click', () => {
    const opt = selectStock.selectedOptions[0];
    if (!opt || !opt.value) return alert('Seleccione un producto');
    const idStock = opt.value;
    const nombre  = opt.text;
    const precio  = parseFloat(opt.dataset.precio) || 0;

    // Evitar duplicados
    if ([...tablaItemsBody.children].some(r => r.dataset.idStock === idStock)) {
      return alert('Ya agregaste este producto');
    }

    // Creamos la fila
    const row = document.createElement('tr');
    row.dataset.idStock = idStock;

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

    // Referencias
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
});

document.getElementById('btnMostrarDescripcion').addEventListener('click', () => {
  const contenedor = document.getElementById('contenedorDescripcion');
  contenedor.style.display = contenedor.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('btnGenerarPDF').addEventListener('click', () => {
  const descripcion = document.getElementById('descripcionPdf').value;
  const idPresupuesto = document.querySelector('input[name="id_presupuesto"]')?.value || '';

  if (!idPresupuesto) {
    alert('Primero debes guardar el presupuesto antes de generar el PDF.');
    return;
  }

  // Abrir una nueva ventana o redirigir con GET
  const url = `generar_pdf.php?id=${idPresupuesto}&descripcion=${encodeURIComponent(descripcion)}`;
  window.open(url, '_blank');
});

</script>

</body>
</html>
