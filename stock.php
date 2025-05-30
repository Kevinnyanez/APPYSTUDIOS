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
                    <a href="stock_form.php?id=<?= $row['id_stock'] ?>" class="btn-editar-stock">Editar</a> |
                    <a href="stock_action.php?action=delete&id=<?= $row['id_stock'] ?>" onclick="return confirm('¿Seguro querés eliminar este item?');">Eliminar</a>
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
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
  background-color: #222;
  border-radius: 8px;
  overflow: hidden;
}
th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
th, td { background-color: #222; color: white; }
tr:nth-child(even) { background-color: #eee; }
h1 { text-align: center; margin-top: 20px; }
.btn-volver, .btn-agregar { display: inline-block; padding: 8px 12px; background: #222; color: white; text-decoration: none; border-radius: 4px; margin: 10px; }
form { width: 90%; margin: 10px auto; text-align: center; }
input[type="text"], select { padding: 6px; margin: 0 10px 10px 0; }

.form-busqueda {
    max-width: 600px;
    margin: 20px auto;
    padding: 16px;
    background-color: #222;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.form-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
}

.input-text,
.input-select {
    padding: 10px 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
    max-width: 250px;
}

.btn {
    padding: 10px 16px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    text-align: center;
    display: inline-block;
}

.btn-buscar {
    background-color: #007BFF;
    color: white;
}

.btn-buscar:hover {
    background-color: #0056b3;
}

.btn-limpiar {
    background-color: #6c757d;
    color: white;
}

.btn-limpiar:hover {
    background-color: #5a6268;
}


.Stock-esti {
  font-size: 2rem;
  margin-bottom: 2rem;
  color: #222;
  font-family: 'Segoe UI', sans-serif;
  font-weight: 700;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #007acc;
  padding-bottom: 0.5rem;
  display: inline-block;
  margin-left: auto;
  margin-right: auto;
}



</style>
</head>
<body>

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

        <button type="submit" class="btn btn-buscar">Buscar</button>
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
                    <a href="stock_form.php?id=<?= $row['id_stock'] ?>" class="btn-editar-stock">Editar</a> |
                    <a href="stock_action.php?action=delete&id=<?= $row['id_stock'] ?>" onclick="return confirm('¿Seguro querés eliminar este item?');">Eliminar</a>
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
