document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM Content Loaded - Inicializando eventos del modal');
  
  // --- PASOS DEL MODAL ---
  function mostrarPaso(n) {
    console.log('Intentando mostrar paso:', n);
    document.querySelectorAll('.paso').forEach(p => p.classList.remove('activo'));
    const paso = document.getElementById('paso' + n);
    if (paso) {
      paso.classList.add('activo');
      console.log('Paso', n, 'mostrado correctamente');
    } else {
      console.warn('No se encontró el paso', n);
    }
    // Wizard bar visual
    document.querySelectorAll('.wizard-step').forEach((w, i) => {
      if (i === n - 1) w.classList.add('activo');
      else w.classList.remove('activo');
    });
  }

  // --- Modal abrir/cerrar ---
  const btnAbrir = document.getElementById('abrirModalPresupuesto');
  if (btnAbrir) {
    console.log('Botón abrir modal encontrado');
    btnAbrir.onclick = function(e) {
      console.log('Click en botón abrir modal');
      e.preventDefault();
      limpiarModalPresupuesto();
      mostrarPaso(1);
      document.getElementById('modalPresupuesto').style.display = 'block';
    };
  } else {
    console.error('No se encontró el botón abrir modal');
  }

  // --- Botón Siguiente Paso 1 ---
  const btnSiguiente1 = document.getElementById('siguientePaso1');
  if (btnSiguiente1) {
    console.log('Botón siguiente paso 1 encontrado');
    btnSiguiente1.onclick = function() {
      console.log('Click en botón siguiente paso 1');
      const selectCliente = document.getElementById('selectCliente');
      if (selectCliente.value === 'nuevo') {
        if (!document.getElementById('nuevoNombre').value.trim()) {
          mostrarErrorInput(document.getElementById('nuevoNombre'), 'Ingrese el nombre del nuevo cliente');
          return;
        }
      } else if (!selectCliente.value) {
        mostrarErrorInput(selectCliente, 'Seleccione un cliente o cree uno nuevo');
        return;
      }
      mostrarPaso(2);
    };
  } else {
    console.error('No se encontró el botón siguientePaso1');
  }

  // --- Botón Anterior/Siguiente Paso 2 y Paso 3 ---
  const btnAnterior2 = document.getElementById('anteriorPaso2');
  if (btnAnterior2) btnAnterior2.onclick = function() { mostrarPaso(1); };
  const btnSiguiente2 = document.getElementById('siguientePaso2');
  if (btnSiguiente2) btnSiguiente2.onclick = function() {
    if (document.querySelectorAll('#tablaItems tbody tr').length === 0) {
      alert('Agregue al menos un producto');
      return;
    }
    // Resumen
    const cliente = document.getElementById('selectCliente').selectedOptions[0];
    let clienteInfo = '';
    if (cliente.value === 'nuevo') {
      clienteInfo = `
        <p><strong>Nombre:</strong> ${document.getElementById('nuevoNombre').value}</p>
        <p><strong>Email:</strong> ${document.getElementById('nuevoEmail').value}</p>
        <p><strong>Teléfono:</strong> ${document.getElementById('nuevoTelefono').value}</p>
        <p><strong>Dirección:</strong> ${document.getElementById('nuevoDireccion').value}</p>
      `;
    } else {
      clienteInfo = `
        <p><strong>Nombre:</strong> ${cliente.text}</p>
        <p><strong>Teléfono:</strong> ${cliente.getAttribute('data-telefono') || ''}</p>
        <p><strong>Email:</strong> ${cliente.getAttribute('data-email') || ''}</p>
        <p><strong>Dirección:</strong> ${cliente.getAttribute('data-direccion') || ''}</p>
      `;
    }
    document.getElementById('resumenCliente').innerHTML = clienteInfo;
    let productosInfo = '<ul>';
    document.querySelectorAll('#tablaItems tbody tr').forEach(row => {
      productosInfo += `
        <li>${row.cells[0].textContent.trim()} - Cantidad: ${row.querySelector('input[name="cantidad[]"]').value} - 
        Precio: $${row.querySelector('input[name="precio_unitario[]"]').value} - 
        Subtotal: $${row.querySelector('.subtotal-text').textContent}</li>
      `;
    });
    productosInfo += '</ul>';
    document.getElementById('resumenProductos').innerHTML = productosInfo;
    document.getElementById('resumenTotales').innerHTML = `
      <p><strong>Subtotal:</strong> ${document.getElementById('totalPresupuesto').textContent}</p>
      <p><strong>Recargo total:</strong> ${document.getElementById('recargoTotal').value}%</p>
      <p><strong>Total final:</strong> ${document.getElementById('totalConRecargo').textContent}</p>
    `;
    mostrarPaso(3);
  };
  const btnAnterior3 = document.getElementById('anteriorPaso3');
  if (btnAnterior3) btnAnterior3.onclick = function() { mostrarPaso(2); };

  // Mostrar/ocultar campos de nuevo cliente
  const selectCliente = document.getElementById('selectCliente');
  const clienteInfo = document.getElementById('clienteInfo');
  const nuevoClienteFields = document.getElementById('nuevoClienteFields');
  const cliTelefono = document.getElementById('cliTelefono');
  const cliEmail = document.getElementById('cliEmail');
  const cliDireccion = document.getElementById('cliDireccion');
  selectCliente.addEventListener('change', function() {
    if (this.value === 'nuevo') {
      clienteInfo.style.display = 'none';
      nuevoClienteFields.style.display = 'block';
    } else if (this.value) {
      const opt = this.selectedOptions[0];
      cliTelefono.textContent = opt.getAttribute('data-telefono') || '';
      cliEmail.textContent = opt.getAttribute('data-email') || '';
      cliDireccion.textContent = opt.getAttribute('data-direccion') || '';
      clienteInfo.style.display = 'block';
      nuevoClienteFields.style.display = 'none';
    } else {
      clienteInfo.style.display = 'none';
      nuevoClienteFields.style.display = 'none';
    }
  });

  // --- Autocompletado de productos ---
  if (window.productosPresupuesto) {
    const productos = window.productosPresupuesto;
    const inputBuscar = document.getElementById('inputBuscarProducto');
    const sugerencias = document.getElementById('sugerenciasProductos');
    const idProductoSel = document.getElementById('idProductoSeleccionado');
    const precioProductoSel = document.getElementById('precioProductoSeleccionado');
    inputBuscar.addEventListener('input', function() {
      const val = this.value.toLowerCase();
      sugerencias.innerHTML = '';
      idProductoSel.value = '';
      precioProductoSel.value = '';
      if (!val) { sugerencias.style.display = 'none'; return; }
      const filtrados = productos.filter(p => p.nombre.toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '').includes(val.normalize('NFD').replace(/\p{Diacritic}/gu, '')));
      if (filtrados.length === 0) {
        sugerencias.innerHTML = '<li style="padding:8px 12px;color:#bbb;">No se encontraron productos</li>';
        sugerencias.style.display = 'block';
        return;
      }
      filtrados.forEach(p => {
        const li = document.createElement('li');
        li.textContent = `${p.nombre} – $${parseFloat(p.precio).toFixed(2)}`;
        li.style.padding = '8px 12px';
        li.style.cursor = 'pointer';
        li.onmouseover = () => li.style.background = '#333';
        li.onmouseout = () => li.style.background = '';
        li.onclick = () => {
          inputBuscar.value = p.nombre;
          idProductoSel.value = p.id;
          precioProductoSel.value = p.precio;
          sugerencias.style.display = 'none';
        };
        sugerencias.appendChild(li);
      });
      sugerencias.style.display = 'block';
    });
    inputBuscar.addEventListener('blur', function() {
      setTimeout(() => { sugerencias.style.display = 'none'; }, 150);
    });
    inputBuscar.addEventListener('change', function() {
      if (!productos.some(p => p.nombre === this.value)) {
        idProductoSel.value = '';
        precioProductoSel.value = '';
      }
    });
    document.getElementById('btnAgregarItem').onclick = function() {
      const idStock = idProductoSel.value;
      const nombre = inputBuscar.value;
      const precioBase = parseFloat(precioProductoSel.value) || 0;
      if (!idStock || !nombre) return alert('Seleccione un producto de la lista');
      if ([...document.querySelector('#tablaItems tbody').children].some(r => r.dataset.idStock === idStock)) {
        return alert('Ya agregaste este producto');
      }
      const recargo = parseFloat(document.getElementById('recargoProducto').value) || 0;
      const precio = precioBase * (1 + recargo / 100);
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
      document.querySelector('#tablaItems tbody').appendChild(row);
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
      // Limpiar selección
      inputBuscar.value = '';
      idProductoSel.value = '';
      precioProductoSel.value = '';
    };
  }

  // --- Cálculo de totales ---
  function calcularTotal() {
    let total = 0;
    document.querySelectorAll('#tablaItems tbody tr').forEach(row => {
      const sub = parseFloat(row.querySelector('input[name="subtotal[]"]').value) || 0;
      total += sub;
    });
    document.getElementById('totalPresupuesto').textContent = '$' + total.toFixed(2);
    // Calcular recargo al total
    const recargoTotal = parseFloat(document.getElementById('recargoTotal').value) || 0;
    const totalFinal = total * (1 + recargoTotal / 100);
    document.getElementById('totalConRecargo').textContent = '$' + totalFinal.toFixed(2);
  }
  document.getElementById('recargoProducto').addEventListener('input', () => {
    document.querySelectorAll('#tablaItems tbody tr').forEach(row => {
      const precioBase = parseFloat(row.dataset.precioBase);
      const recargo = parseFloat(document.getElementById('recargoProducto').value) || 0;
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
  document.getElementById('recargoTotal').addEventListener('input', calcularTotal);

  // --- Limpiar modal ---
  function limpiarModalPresupuesto() {
    console.log('Limpiando modal');
    document.getElementById('formPresupuestoModal').reset();
    document.querySelector('#formPresupuestoModal input[name="id_presupuesto"]')?.remove();
    document.querySelector('#tablaItems tbody').innerHTML = '';
    document.getElementById('totalPresupuesto').textContent = '$0.00';
    document.getElementById('totalConRecargo').textContent = '$0.00';
    mostrarPaso(1);
  }

  // --- Cargar presupuesto en el modal para editar ---
  function cargarPresupuestoEnModal(id) {
    console.log('Cargando presupuesto en modal:', id);
    fetch('presupuesto_action.php?get_presupuesto=' + id)
      .then(res => res.json())
      .then(data => {
        console.log('Datos del presupuesto recibidos:', data);
        limpiarModalPresupuesto();
        mostrarPaso(2);
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
          row.dataset.precioBase = item.precio_unitario;
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
      })
      .catch(error => {
        console.error('Error al cargar presupuesto:', error);
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

  // --- RESUMEN DE ÍTEMS EN LA TABLA DE PRESUPUESTOS ---
  document.querySelectorAll('.btn-ver-items').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.dataset.id;
      let row = this.closest('tr');
      // Si ya está abierto, cerrar
      if (row.nextElementSibling && row.nextElementSibling.classList.contains('resumen-items')) {
        row.nextElementSibling.remove();
        return;
      }
      // Cerrar otros abiertos
      document.querySelectorAll('.resumen-items').forEach(el => el.remove());
      fetch('presupuesto_action.php?get_presupuesto=' + id)
        .then(res => res.json())
        .then(data => {
          const tr = document.createElement('tr');
          tr.className = 'resumen-items';
          tr.innerHTML = `<td colspan="6">
            <strong>Ítems del presupuesto:</strong>
            <ul style="margin:8px 0 0 0; padding:0 0 0 18px;">
              ${data.items.map(it => `<li>${it.nombre_stock} - Cantidad: ${it.cantidad} - Precio: $${parseFloat(it.precio_unitario).toFixed(2)} - Subtotal: $${parseFloat(it.subtotal).toFixed(2)}</li>`).join('')}
            </ul>
          </td>`;
          row.parentNode.insertBefore(tr, row.nextSibling);
        });
    };
  });

  // --- Validaciones visuales y feedback ---
  function mostrarErrorInput(input, mensaje) {
    input.style.border = '2px solid #e57373';
    input.focus();
    if (mensaje) alert(mensaje);
  }
}); 