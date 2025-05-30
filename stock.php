<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

// Variables de búsqueda
$search = trim($_GET['search'] ?? '');
$filter_tipo = trim($_GET['tipo'] ?? '');

// Preparar consulta base
$sql = "SELECT * FROM stock WHERE 1=1";

// Array para parámetros
$params = [];
$types = "";

// Si hay búsqueda por nombre
if ($search !== '') {
    $sql .= " AND nombre LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Si hay filtro por tipo
if ($filter_tipo !== '') {
    $sql .= " AND tipo = ?";
    $params[] = $filter_tipo;
    $types .= "s";
}

$sql .= " ORDER BY nombre";

$stmt = $conn->prepare($sql);

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener tipos distintos para filtro
$tipos_res = $conn->query("SELECT DISTINCT tipo FROM stock");
$tipos = [];
if ($tipos_res) {
    while($row = $tipos_res->fetch_assoc()) {
        $tipos[] = $row['tipo'];
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_start();
    ?>
    <thead>
        <tr>
            <th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Tipo</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (
            $result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($row['id_stock'])?></td>
                <td><?=htmlspecialchars($row['nombre'])?></td>
                <td><?=htmlspecialchars($row['descripcion'])?></td>
                <td><?=htmlspecialchars($row['cantidad'])?></td>
                <td>$<?=number_format($row['precio_unitario'], 2)?></td>
                <td><?=htmlspecialchars($row['tipo'])?></td>
                <td>
                    <a href="stock_form.php?id=<?= $row['id_stock'] ?>" class="btn-editar-stock">Editar</a>
                    <a href="stock_action.php?action=delete&id=<?= $row['id_stock'] ?>" class="btn-eliminar-stock" onclick="return confirm('¿Seguro querés eliminar este item?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No se encontraron items.</td></tr>
        <?php endif; ?>
    </tbody>
    <?php
    echo ob_get_clean();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Stock - Buscar y Filtrar</title>
<style>
    body {
        background-color: #cbd5e1;
    }
/* Igual que antes + un poco para el formulario */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #cbd5e1;
  color: #333;
  margin: 0;
  padding: 20px;
}

h1.Stock-esti {
  font-size: 28px;
  margin-bottom: 20px;
  color: #1f2d3d;
  border-bottom: 2px solid #0077b6;
  display: inline-block;
  padding-bottom: 5px;
}

.form-busqueda {
  background-color: #ffffff;
  padding: 15px;
  margin-bottom: 20px;
  border: 1px solid #dcdcdc;
  border-radius: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
}

.form-busqueda .input-text,
.form-busqueda .input-select {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  flex: 1;
  min-width: 180px;
}

.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
}

.btn-buscar {
  background-color: #0077b6;
  color: white;
}

.btn-limpiar {
  background-color: #adb5bd;
  color: white;
}

.btn-agregar {
  display: inline-block;
  margin-bottom: 15px;
  background-color: #28a745;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  text-decoration: none;
}

.btn-volver {
  display: inline-block;
  margin-top: 20px;
  background-color: #6c757d;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  text-decoration: none;
}

/* MODAL MEJORADO */
#modalProducto {
  display: none;
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.45);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}
#modalProducto .modal-content {
  background: #fff;
  color: #222;
  border-radius: 18px;
  max-width: 420px;
  width: 95%;
  margin: auto;
  padding: 2.2rem 2rem 1.5rem 2rem;
  position: relative;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
  animation: modalShow 0.25s;
}
@keyframes modalShow { from { transform: translateY(-40px); opacity:0; } to { transform: none; opacity:1; } }
#modalProducto h2 {
  margin-top: 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #0077b6;
  margin-bottom: 1.2rem;
}
#cerrarModalProducto {
  position: absolute;
  top: 12px; right: 18px;
  font-size: 2rem;
  cursor: pointer;
  color: #888;
  transition: color 0.2s;
}
#cerrarModalProducto:hover { color: #0077b6; }
#formProducto {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}
#formProducto label {
  font-weight: 600;
  color: #333;
  margin-bottom: 2px;
}
#formProducto input, #formProducto textarea, #formProducto select {
  padding: 9px 12px;
  border: 1px solid #b0b0b0;
  border-radius: 7px;
  font-size: 1rem;
  background: #f7fafd;
  color: #222;
  margin-bottom: 2px;
  transition: border-color 0.2s;
}
#formProducto input:focus, #formProducto textarea:focus, #formProducto select:focus {
  border-color: #0077b6;
  outline: none;
}
#btnGuardarProducto {
  margin-top: 1.2rem;
  padding: 12px 0;
  background-color: #0077b6;
  color: white;
  font-weight: 700;
  border: none;
  border-radius: 7px;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background 0.2s;
}
#btnGuardarProducto:hover {
  background: #023e8a;
}

/* TABLA MEJORADA */
table {
  width: 98%;
  margin: 0 auto;
  border-collapse: separate;
  border-spacing: 0;
  background-color: #23272f;
  box-shadow: 0 4px 18px rgba(0,0,0,0.18);
  border-radius: 12px;
  overflow: hidden;
}
table th, table td {
  padding: 14px 18px;
  text-align: left;
  font-size: 1rem;
}
table th {
  background-color: #1a1d23;
  color: #fff;
  font-weight: 700;
  font-size: 1.08rem;
  letter-spacing: 0.5px;
}
table tr {
  transition: background 0.18s;
}
table tr:nth-child(even) {
  background-color: #23272f;
}
table tr:nth-child(odd) {
  background-color: #2d323c;
}
table tr:hover {
  background-color: #0077b6 !important;
  color: #fff;
}
table td, table th {
  border-bottom: 1px solid #353a45;
}
table td {
  color: #f1f1f1;
  font-size: 1rem;
}
/* Botones de acciones */
.btn-editar-stock, .btn-eliminar-stock {
  display: inline-block;
  padding: 7px 14px;
  border-radius: 6px;
  font-size: 0.98rem;
  font-weight: 600;
  text-decoration: none;
  margin-right: 6px;
  transition: background 0.18s, color 0.18s;
}
.btn-editar-stock {
  background: #38bdf8;
  color: #1a1d23;
  border: none;
}
.btn-editar-stock:hover {
  background: #0ea5e9;
  color: #fff;
}
.btn-eliminar-stock {
  background: #ef4444;
  color: #fff;
  border: none;
}
.btn-eliminar-stock:hover {
  background: #b91c1c;
}

@media (max-width: 768px) {
  table th, table td {
    padding: 10px 6px;
    font-size: 0.98rem;
  }
  #modalProducto .modal-content {
    padding: 1.2rem 0.5rem 1rem 0.5rem;
  }
}

/* NAVBAR ESTILO MODERNO */
nav {
  background: #232b36;
  border-radius: 12px;
  padding: 22px 36px 18px 36px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 36px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.10);
}
nav a {
  color: #fff;
  text-decoration: none;
  font-size: 1.18rem;
  font-weight: 500;
  margin-right: 18px;
  transition: color 0.18s, background 0.18s, box-shadow 0.18s;
  padding: 6px 12px;
  border-radius: 6px;
}
nav a:hover {
  background: #0077b6;
  color: #fff;
  box-shadow: 0 2px 8px rgba(0,119,182,0.10);
}
nav .logout {
  margin-left: auto;
  background: #ef4444;
  color: #fff;
  font-weight: 700;
  border-radius: 8px;
  padding: 8px 22px;
  font-size: 1.1rem;
  box-shadow: 0 2px 8px rgba(239,68,68,0.10);
  transition: background 0.18s, color 0.18s;
}
nav .logout:hover {
  background: #b91c1c;
  color: #fff;
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

<div style="text-align: center;">
  <h1 class="Stock-esti">Stock de Materiales y Muebles</h1>
</div>

<form method="GET" action="stock.php" class="form-busqueda">
    <div class="form-group">
        <input type="text" name="search" placeholder="Buscar por nombre" value="<?=htmlspecialchars($search)?>" class="input-text" />
        <select name="tipo" class="input-select">
            <option value="">-- Filtrar por tipo --</option>
            <?php foreach($tipos as $t): ?>
                <option value="<?=htmlspecialchars($t)?>" <?= $filter_tipo === $t ? 'selected' : '' ?>><?=htmlspecialchars($t)?></option>
            <?php endforeach; ?>
        </select>
        <a href="stock.php" class="btn btn-limpiar">Limpiar</a>
    </div>
</form>


<a href="#" class="btn-agregar" id="btnAbrirModalAgregar">Agregar nuevo</a>

<!-- Modal para Alta/Edición de Producto -->
<div id="modalProducto" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div class="modal-content" style="background:#fff; color:#222; border-radius:12px; max-width:500px; width:95%; margin:auto; padding:2rem; position:relative;">
    <span id="cerrarModalProducto" style="position:absolute; top:10px; right:18px; font-size:2rem; cursor:pointer;">&times;</span>
    <h2 id="tituloModalProducto">Agregar producto</h2>
    <form id="formProducto">
      <input type="hidden" name="id" id="idProducto">
      <label for="nombre">Nombre *</label>
      <input type="text" name="nombre" id="nombreProducto" required>
      <label for="descripcion">Descripción</label>
      <textarea name="descripcion" id="descripcionProducto"></textarea>
      <label for="cantidad">Cantidad *</label>
      <input type="number" name="cantidad" id="cantidadProducto" required>
      <label for="precio_unitario">Precio Unitario *</label>
      <input type="number" name="precio_unitario" id="precioProducto" step="0.01" min="0" required>
      <label for="tipo">Tipo *</label>
      <select name="tipo" id="tipoProducto" required>
        <option value="">-- Seleccionar --</option>
        <option value="Material">Material</option>
        <option value="Mueble">Mueble</option>
      </select>
      <button type="submit" id="btnGuardarProducto" style="margin-top:1.5rem; padding:12px 24px; background-color:#007acc; color:white; font-weight:600; border:none; border-radius:8px; cursor:pointer;">Guardar</button>
    </form>
  </div>
</div>

<table id="tablaStock">
    <thead>
        <tr>
            <th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Tipo</th><th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($row['id_stock'])?></td>
                <td><?=htmlspecialchars($row['nombre'])?></td>
                <td><?=htmlspecialchars($row['descripcion'])?></td>
                <td><?=htmlspecialchars($row['cantidad'])?></td>
                <td>$<?=number_format($row['precio_unitario'], 2)?></td>
                <td><?=htmlspecialchars($row['tipo'])?></td>
                <td>
                    <a href="stock_form.php?id=<?= $row['id_stock'] ?>" class="btn-editar-stock">Editar</a>
                    <a href="stock_action.php?action=delete&id=<?= $row['id_stock'] ?>" class="btn-eliminar-stock" onclick="return confirm('¿Seguro querés eliminar este item?');">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No se encontraron items.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<a href="dashboard.php" class="btn-volver">Volver al Dashboard</a>

<script>
// --- Modal y AJAX para Alta/Edición ---
const modal = document.getElementById('modalProducto');
const btnAbrir = document.getElementById('btnAbrirModalAgregar');
const cerrar = document.getElementById('cerrarModalProducto');
const form = document.getElementById('formProducto');
const tituloModal = document.getElementById('tituloModalProducto');
const btnGuardar = document.getElementById('btnGuardarProducto');
const idInput = document.getElementById('idProducto');
const nombreInput = document.getElementById('nombreProducto');
const descInput = document.getElementById('descripcionProducto');
const cantInput = document.getElementById('cantidadProducto');
const precioInput = document.getElementById('precioProducto');
const tipoInput = document.getElementById('tipoProducto');

function abrirModal(modo, datos = null) {
  tituloModal.textContent = modo === 'editar' ? 'Editar producto' : 'Agregar producto';
  form.reset();
  idInput.value = '';
  if (modo === 'editar' && datos) {
    idInput.value = datos.id_stock;
    nombreInput.value = datos.nombre;
    descInput.value = datos.descripcion;
    cantInput.value = datos.cantidad;
    precioInput.value = datos.precio_unitario;
    tipoInput.value = datos.tipo;
  }
  modal.style.display = 'flex';
}

btnAbrir.onclick = function(e) {
  e.preventDefault();
  abrirModal('agregar');
};
cerrar.onclick = function() { modal.style.display = 'none'; };
window.onclick = function(e) { if (e.target === modal) modal.style.display = 'none'; };

// --- Enviar formulario por AJAX ---
form.onsubmit = function(e) {
  e.preventDefault();
  btnGuardar.disabled = true;
  const datos = new FormData(form);
  let url = 'stock_action.php';
  let action = idInput.value ? 'edit' : 'add';
  if (action === 'edit') url += '?action=edit&id=' + encodeURIComponent(idInput.value);
  else url += '?action=add';
  fetch(url, {
    method: 'POST',
    body: datos
  })
  .then(r => r.text())
  .then(resp => {
    btnGuardar.disabled = false;
    modal.style.display = 'none';
    cargarTabla();
  })
  .catch(() => { btnGuardar.disabled = false; alert('Error al guardar'); });
};

// --- Cargar tabla por AJAX ---
function cargarTabla() {
  fetch('stock.php?ajax=1')
    .then(r => r.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const nuevaTabla = doc.getElementById('tablaStock');
      document.getElementById('tablaStock').innerHTML = nuevaTabla.innerHTML;
      agregarListenersEdicion();
    });
}

// --- Listener para editar ---
function agregarListenersEdicion() {
  document.querySelectorAll('.btn-editar-stock').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const fila = this.closest('tr');
      abrirModal('editar', {
        id_stock: fila.children[0].textContent.trim(),
        nombre: fila.children[1].textContent.trim(),
        descripcion: fila.children[2].textContent.trim(),
        cantidad: fila.children[3].textContent.trim(),
        precio_unitario: fila.children[4].textContent.replace('$','').trim(),
        tipo: fila.children[5].textContent.trim()
      });
    };
  });
}
agregarListenersEdicion();

// --- Buscador dinámico ---
const inputBuscar = document.querySelector('input[name="search"]');
const selectTipo = document.querySelector('select[name="tipo"]');
inputBuscar.addEventListener('input', filtrarTabla);
selectTipo.addEventListener('change', filtrarTabla);
function filtrarTabla() {
  const texto = inputBuscar.value.toLowerCase();
  const tipo = selectTipo.value;
  document.querySelectorAll('#tablaStock tbody tr').forEach(tr => {
    const nombre = tr.children[1].textContent.toLowerCase();
    const tipoProd = tr.children[5].textContent;
    let visible = nombre.includes(texto);
    if (tipo && tipoProd !== tipo) visible = false;
    tr.style.display = visible ? '' : 'none';
  });
}
</script>

</body>
</html>
