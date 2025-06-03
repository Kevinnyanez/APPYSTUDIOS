document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM Content Loaded - Inicializando eventos del modal');
  
  // Variable para almacenar la descripción del presupuesto
  let presupuestoDescripcion = '';

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

  // --- MODALES SIMPLES POR PASO ---
  function abrirModalCliente() {
    document.getElementById('modalCliente').style.display = 'block';
  }
  function cerrarModalCliente() {
    document.getElementById('modalCliente').style.display = 'none';
  }
  function abrirModalProductos() {
    document.getElementById('modalProductos').style.display = 'block';
  }
  function cerrarModalProductos() {
    document.getElementById('modalProductos').style.display = 'none';
  }
  function abrirModalResumen() {
    document.getElementById('modalResumen').style.display = 'block';
  }
  function cerrarModalResumen() {
    document.getElementById('modalResumen').style.display = 'none';
  }

  // --- Abrir modal inicial ---
 const btnAbrir = document.getElementById('abrirModalPresupuesto');
if (btnAbrir) {
  btnAbrir.addEventListener('click', function (e) {
    console.log('Click en botón abrir modal. Llamando a limpieza y abriendo modal cliente.');
    e.preventDefault(); // Ahora sí funciona
    limpiarTodoPresupuesto(); // Aseguramos la limpieza
    abrirModalCliente();
  });
} else {
  console.error('No se encontró el botón abrir modal');
}


  // --- Cerrar modales ---
  document.getElementById('cerrarModalCliente').onclick = cerrarModalCliente;
  document.getElementById('cerrarModalProductos').onclick = cerrarModalProductos;
  document.getElementById('cerrarModalResumen').onclick = cerrarModalResumen;
  document.getElementById('cancelarModalCliente').onclick = cerrarModalCliente;

  // --- Paso 1: Cliente -> Productos ---
  document.getElementById('siguienteCliente').onclick = function() {
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
    cerrarModalCliente();
    abrirModalProductos();
  };
  // --- Paso 2: Productos -> Resumen ---
  document.getElementById('siguienteProductos').onclick = function() {
    if (document.querySelectorAll('#tablaItems tbody tr').length === 0) {
      alert('Agregue al menos un producto');
      return;
    }

    // --- Capturar la descripción del Paso 2 antes de cerrar el modal ---
    const descripcionInputPaso2 = document.getElementById('descripcionPresupuesto');
    if (descripcionInputPaso2) {
      presupuestoDescripcion = descripcionInputPaso2.value; // Guardar el valor
    } else {
      presupuestoDescripcion = ''; // Asegurarse de que está vacío si el input no existe (no debería pasar)
      console.warn('Input de descripcionPresupuesto no encontrado en Paso 2.');
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

    // Agregar descripción al resumen si existe
    const descripcion = document.getElementById('descripcionPresupuesto').value;
    if (descripcion) {
      document.getElementById('resumenCliente').innerHTML += `
        <p><strong>Descripción:</strong> ${descripcion}</p>
      `;
    }

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
    cerrarModalProductos();
    abrirModalResumen();
  };
  // --- Paso 2: Productos <- Cliente ---
  document.getElementById('anteriorProductos').onclick = function() {
    cerrarModalProductos();
    abrirModalCliente();
  };
  // --- Paso 3: Resumen <- Productos ---
  document.getElementById('anteriorResumen').onclick = function() {
    cerrarModalResumen();
    abrirModalProductos();
  };
  // --- Limpiar todo al cancelar o cerrar ---
  function limpiarTodoPresupuesto() {
    console.log('Iniciando limpieza total de formularios y estado.');
    const formCliente = document.getElementById('formCliente');
    const formProductos = document.getElementById('formProductos');
    const formResumen = document.getElementById('formResumen');

    console.log('Intentando resetear formCliente:', formCliente);
    if (formCliente && typeof formCliente.reset === 'function') {
      formCliente.reset();
      console.log('formCliente reseteado.');
    } else {
      console.warn('formCliente no encontrado o reset no es una función.', formCliente);
    }

    console.log('Intentando resetear formProductos:', formProductos);
    if (formProductos && typeof formProductos.reset === 'function') {
      formProductos.reset();
      console.log('formProductos reseteado.');
    } else {
       console.warn('formProductos no encontrado o reset no es una función.', formProductos);
    }

    console.log('Intentando resetear formResumen:', formResumen);
     if (formResumen && typeof formResumen.reset === 'function') {
      formResumen.reset();
      console.log('formResumen reseteado.');
    } else {
       console.warn('formResumen no encontrado o reset no es una función.', formResumen);
    }

    // Limpiar campos de nuevo cliente y ocultar la sección
    const nuevoNombre = document.getElementById('nuevoNombre');
    const nuevoEmail = document.getElementById('nuevoEmail');
    const nuevoTelefono = document.getElementById('nuevoTelefono');
    const nuevoDireccion = document.getElementById('nuevoDireccion');
    const nuevoClienteFields = document.getElementById('nuevoClienteFields');
    const clienteInfo = document.getElementById('clienteInfo');
    const selectCliente = document.getElementById('selectCliente');

    if (nuevoNombre) nuevoNombre.value = '';
    if (nuevoEmail) nuevoEmail.value = '';
    if (nuevoTelefono) nuevoTelefono.value = '';
    if (nuevoDireccion) nuevoDireccion.value = '';
    if (nuevoClienteFields) nuevoClienteFields.style.display = 'none';
    if (clienteInfo) clienteInfo.style.display = 'none';
     if (selectCliente) selectCliente.value = ''; // Restablecer select cliente

    // Limpiar tabla de ítems y totales
    const tablaItemsBody = document.querySelector('#tablaItems tbody');
    const totalPresupuesto = document.getElementById('totalPresupuesto');
    const totalConRecargo = document.getElementById('totalConRecargo');

    if (tablaItemsBody) tablaItemsBody.innerHTML = '';
    if (totalPresupuesto) totalPresupuesto.textContent = '$0.00';
    if (totalConRecargo) totalConRecargo.textContent = '$0.00';

    // Limpiar resumen
    const resumenCliente = document.getElementById('resumenCliente');
    const resumenProductos = document.getElementById('resumenProductos');
    const resumenTotales = document.getElementById('resumenTotales');
    if (resumenCliente) resumenCliente.innerHTML = '';
    if (resumenProductos) resumenProductos.innerHTML = '';
    if (resumenTotales) resumenTotales.innerHTML = '';

    // Restablecer estilos de validación (si se aplicaron)
    document.querySelectorAll('.form-group input, .form-group select').forEach(input => {
      if(input) input.style.border = '';
    });

    // Remover input hidden del ID de presupuesto para edición si existe
    const idPresupuestoHidden = document.querySelector('#formResumen input[name="id_presupuesto"]');
    if (idPresupuestoHidden) idPresupuestoHidden.remove();

    console.log('Limpieza total finalizada.');
  }

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
      // Asegurar que el precio base es un número válido y con punto decimal
      let precioBase = precioProductoSel.value.replace(',', '.');
      precioBase = parseFloat(precioBase) || 0;
      if (!idStock || !nombre) return alert('Seleccione un producto de la lista');
      if ([...document.querySelector('#tablaItems tbody').children].some(r => r.dataset.idStock === idStock)) {
        return alert('Ya agregaste este producto');
      }
      const recargo = parseFloat(document.getElementById('recargoProducto').value) || 0;
      // Calcular precio con recargo (ej: 150% -> precio final = base + 150% del base)
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
      // Usar siempre el precio base guardado en el dataset
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

  // --- Cargar presupuesto en los modales para edición ---
  function cargarPresupuestoParaEdicion(id) {
    console.log('Cargando presupuesto para edición:', id);
    // Limpiar cualquier estado previo de los modales antes de cargar nuevos datos
    limpiarTodoPresupuesto();

    fetch('presupuesto_action.php?get_presupuesto=' + id) // Endpoint para obtener datos de 1 presupuesto
      .then(res => res.json())
      .then(data => {
        console.log('Datos del presupuesto para edición recibidos:', data);
        if (!data || !data.presupuesto) {
          alert('No se pudieron cargar los datos del presupuesto para edición.');
          return;
        }

        // ** Importante: Agregar el ID del presupuesto al formulario de Resumen para la actualización **
        // Lo añadimos aquí tan pronto como tenemos el ID
        const formResumen = document.getElementById('formResumen');
        if (formResumen) {
            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id_presupuesto'; // El backend espera este nombre para actualizar
            idInput.value = data.presupuesto.id_presupuesto;
            formResumen.appendChild(idInput); // Añadir al formulario que se enviará
        } else {
             console.error('formResumen no encontrado al intentar agregar id_presupuesto para edición.');
             // Continuar pero la edición podría no funcionar correctamente
        }

        // Rellenar Paso 1 (Cliente) - abrir este modal primero para empezar el flujo de edición
        abrirModalCliente();
        const selectCliente = document.getElementById('selectCliente');
        if (selectCliente && data.presupuesto.id_cliente) {
             selectCliente.value = data.presupuesto.id_cliente;
             // Disparar el evento change para mostrar la info del cliente existente
             selectCliente.dispatchEvent(new Event('change'));
        } else if (selectCliente) {
            // Si no hay id_cliente en el presupuesto (lo cual sería raro para uno existente),
            // o si selectCliente no se encuentra, manejar el caso.
            console.warn('ID de cliente no encontrado en los datos del presupuesto o selectCliente no está en el DOM.');
            // Considerar qué hacer aquí: forzar selección de cliente o mostrar error.
        }

        // Rellenar Paso 2 (Productos) - Los datos se rellenan, pero el modal aún no se abre hasta que el usuario pase del Paso 1
        const formProductos = document.getElementById('formProductos');
        if (formProductos && formProductos.fecha_creacion) {
             formProductos.fecha_creacion.value = data.presupuesto.fecha_creacion.substr(0,10);
        } else {
             console.warn('formProductos o campo fecha_creacion no encontrados.');
        }

        // Cargar descripción si existe
        const descripcionInput = document.getElementById('descripcionPresupuesto');
        if (descripcionInput && data.presupuesto.descripcion) {
            descripcionInput.value = data.presupuesto.descripcion;
        }

        const tbody = document.querySelector('#tablaItems tbody');
        if (tbody) {
            tbody.innerHTML = ''; // Limpiar ítems existentes (si los hay)
            data.items.forEach(item => {
              const row = document.createElement('tr');
              row.dataset.idStock = item.id_stock;
              row.dataset.precioBase = item.precio_unitario; // Asumimos precio_unitario guardado es el base antes del recargo por ítem.
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
              // Re-adjuntar listeners para cantidad y eliminar item en las filas recién creadas
              const qtyInput = row.querySelector('.input-cantidad');
              const textSub = row.querySelector('.subtotal-text');
              const hiddenSub = row.querySelector('input[name="subtotal[]"]');
              if(qtyInput && textSub && hiddenSub) {
                   qtyInput.addEventListener('input', function(){
                        const qty = parseFloat(this.value) || 0;
                        // Usar precio_unitario del input visible para recalcular, ya que este ya podría tener el recargo por ítem aplicado
                        const pr  = parseFloat(row.querySelector('input[name="precio_unitario[]"]').value) || 0;
                        const st  = qty * pr;
                        textSub.textContent = st.toFixed(2);
                        hiddenSub.value = st.toFixed(2);
                        calcularTotal();
                   });
                   row.querySelector('.btn-eliminar-item')
                      .addEventListener('click', () => { row.remove(); calcularTotal(); });
              } else {
                  console.warn('No se encontraron elementos necesarios para adjuntar listeners en la fila de ítem.', row);
              }

            });
        } else {
            console.error('Elemento tbody de tablaItems no encontrado.');
        }

        // Rellenar totales y recargo total
        const recargoTotalInput = document.getElementById('recargoTotal');
        if (recargoTotalInput) {
            if (data.presupuesto.recargo_final !== undefined && data.presupuesto.recargo_final !== null) {
                 recargoTotalInput.value = parseFloat(data.presupuesto.recargo_final);
            } else {
                 // Si no hay recargo final guardado, usar el valor por defecto del input o 0.
                 recargoTotalInput.value = recargoTotalInput.defaultValue || 0;
            }
        } else {
            console.warn('Input recargoTotal no encontrado.');
        }

        calcularTotal(); // Recalcular basado en los ítems cargados y recargoTotal

        // El resumen (Paso 3) se generará dinámicamente al pasar al paso 3. Los datos ya están en los inputs/tabla.

        // La función ahora solo abre el modal del cliente. El usuario navegará al paso 2 y 3.


      })
      .catch(error => {
        console.error('Error al cargar presupuesto para edición:', error);
        alert('Error al cargar los datos del presupuesto.');
      });
  }

  // --- Adaptar botones de editar en la tabla ---
  document.querySelectorAll('.btn-link.editar').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const id = this.href.split('id_presupuesto=')[1];
      cargarPresupuestoParaEdicion(id);
    };
  });

  // --- Manejar Submit del formulario de Resumen ---
  const formResumen = document.getElementById('formResumen');
  if (formResumen) {
    formResumen.addEventListener('submit', function(e) {
      e.preventDefault();
      console.log('Submit del formulario de resumen interceptado');

      const formData = new FormData();

      // 1. Datos del Cliente (obtener del formCliente)
      const selectCliente = document.getElementById('selectCliente');
      if (!selectCliente) { console.error('selectCliente no encontrado'); return; }
      formData.append('id_cliente', selectCliente.value);
      if (selectCliente.value === 'nuevo') {
        const nuevoNombre = document.getElementById('nuevoNombre');
        const nuevoEmail = document.getElementById('nuevoEmail');
        const nuevoTelefono = document.getElementById('nuevoTelefono');
        const nuevoDireccion = document.getElementById('nuevoDireccion');
        if (!nuevoNombre || !nuevoEmail || !nuevoTelefono || !nuevoDireccion) { console.error('Campos de nuevo cliente no encontrados'); return; }

        formData.append('crear_cliente', 'true'); // Indica al backend que cree un cliente
        formData.append('nuevo_nombre', nuevoNombre.value);
        formData.append('nuevo_email', nuevoEmail.value);
        formData.append('nuevo_telefono', nuevoTelefono.value);
        formData.append('nuevo_direccion', nuevoDireccion.value);
      }

      // 2. Datos generales (obtener del formProductos)
      const fechaCreacionInput = document.getElementById('fecha_creacion');
      const recargoTotalInput = document.getElementById('recargoTotal');
      if (!fechaCreacionInput || !recargoTotalInput) { console.error('Campos de fecha o recargo total no encontrados'); return; }

      formData.append('fecha_creacion', fechaCreacionInput.value);
      formData.append('recargo_final', recargoTotalInput.value); // Usar 'recargo_final' según la base de datos

      // 3. Datos de los ítems (de la tabla)
      const itemsRows = document.querySelectorAll('#tablaItems tbody tr');
      if (itemsRows.length === 0) {
        alert('No se pueden crear presupuestos sin ítems.');
        return;
      }
      itemsRows.forEach(row => {
        const idStock = row.dataset.idStock;
        const cantidadInput = row.querySelector('.input-cantidad');
        const precioUnitarioInput = row.querySelector('input[name="precio_unitario[]"]');
        const subtotalInput = row.querySelector('input[name="subtotal[]"]');
        if (!idStock || !cantidadInput || !precioUnitarioInput || !subtotalInput) { console.error('Campos de ítem no encontrados en la fila', row); return; }

        formData.append('id_stock[]', idStock);
        formData.append('cantidad[]', cantidadInput.value);
        formData.append('precio_unitario[]', precioUnitarioInput.value);
        formData.append('subtotal[]', subtotalInput.value);
      });

       // 4. ID de presupuesto para edición (si existe, se añadió en cargarPresupuestoParaEdicion)
      const idPresupuestoHidden = document.querySelector('#formResumen input[name="id_presupuesto"]');
      if (idPresupuestoHidden && idPresupuestoHidden.value) {
           formData.append('id_presupuesto', idPresupuestoHidden.value); // Añadir ID para UPDATE
      }

      // 2. Descripción del presupuesto
      // Obtener el valor desde la variable que guardamos al pasar del Paso 2 al 3
      formData.append('descripcion', presupuestoDescripcion);

      // Enviar datos al backend
      fetch('presupuesto_action.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text()) // Leer como texto para ver 'ok' o errores
      .then(text => {
        console.log('Respuesta del servidor:', text);
        // Asumimos que presupuesto_action.php devuelve 'ok' si la creación/actualización fue exitosa.
        // Si el backend solo devuelve 'cliente_id:X' sin un 'ok' final, la lógica del backend necesitaría ajuste.
        // Me baso en la estructura original donde presupuesto_action.php parece manejar ambas cosas en el mismo script POST.

        if (text.trim() === 'ok') {
          alert('Presupuesto guardado con éxito!');
          // Limpiar y cerrar modales antes de recargar
          limpiarTodoPresupuesto();
          cerrarModalResumen(); // Cierra el modal actual
          // Cerrar otros modales por si acaso (aunque solo Resumen debería estar abierto)
          cerrarModalCliente();
          cerrarModalProductos();

          window.location.reload(); // Recargar la página para ver el nuevo/actualizado presupuesto
        } else if (text.trim().startsWith('error:')) {
          // Si el backend devuelve un error específico
          alert('Error al guardar el presupuesto: ' + text.trim().substring(6));
        } else {
          // Otras respuestas inesperadas del backend
          alert('Respuesta inesperada del servidor: ' + text.trim() + '. Por favor, recarga la página y verifica si el presupuesto se creó/actualizó.');
          // Opcional: No recargar automáticamente aquí para permitir al usuario inspeccionar el estado.
          // cerrarModalResumen();
          // limpiarTodoPresupuesto();
        }
      })
      .catch(error => {
        console.error('Error en la petición Fetch:', error);
        alert('Hubo un problema de conexión o del servidor al intentar guardar el presupuesto. Detalles en la consola.');
      });
    });
  }

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
        })
        .catch(error => {
            console.error('Error al cargar ítems del presupuesto:', error);
            alert('Error al cargar los ítems.');
        });
    };
  });

  // --- Validaciones visuales y feedback ---
  function mostrarErrorInput(input, mensaje) {
    input.style.border = '2px solid #e57373';
    // Remover borde de error después de un tiempo o al corregir
    input.addEventListener('input', function() {
        this.style.border = ''; // O restaurar borde original
    });
    if (mensaje) {
        // Podrías usar un elemento para mostrar el mensaje junto al input en lugar de alert
        console.warn('Validación fallida:', mensaje);
        // alert(mensaje); // Evitar múltiples alerts molestos, usar console o UI feedback
    }
  }
}); 