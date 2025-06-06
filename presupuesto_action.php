<?php
require_once 'includes/db.php'; // conexión centralizada

// Eliminar presupuesto
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Eliminar ítems primero
    $conn->query("DELETE FROM presupuesto_items WHERE id_presupuesto = $id");
    // Eliminar presupuesto
    $conn->query("DELETE FROM presupuestos WHERE id_presupuesto = $id");
    header("Location: presupuestos.php?mensaje=ok");
    exit;
}

// Cerrar presupuesto
if (isset($_GET['cerrar'])) {
    $id = (int)$_GET['cerrar'];
    $conn->query("UPDATE presupuestos SET estado = 'cerrado' WHERE id_presupuesto = $id");
    header("Location: presupuestos.php?mensaje=ok");
    exit;
}

// Obtener presupuesto y sus ítems (para editar, AJAX)
if (isset($_GET['get_presupuesto'])) {
    $id = (int)$_GET['get_presupuesto'];
    $pres = $conn->query("SELECT * FROM presupuestos WHERE id_presupuesto = $id")->fetch_assoc();
    $items = [];
    $res = $conn->query("SELECT pi.*, s.nombre AS nombre_stock FROM presupuesto_items pi JOIN stock s ON pi.id_stock = s.id_stock WHERE id_presupuesto = $id");
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode(['presupuesto' => $pres, 'items' => $items]);
    exit;
}

// Endpoint para obtener los ítems de un presupuesto (para el resumen desplegable)
if (isset($_GET['get_presupuesto'])) {
    $id = (int)$_GET['get_presupuesto'];
    $items = [];
    $sql = "SELECT pi.*, s.nombre AS nombre_stock FROM presupuesto_items pi JOIN stock s ON pi.id_stock = s.id_stock WHERE pi.id_presupuesto = $id";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode(['items' => $items]);
    exit;
}

// *** Lógica para guardar solo la descripción desde la tabla de presupuestos ---
if (isset($_POST['action']) && $_POST['action'] === 'guardar_descripcion') {
    $id_presupuesto = isset($_POST['id_presupuesto']) ? (int)$_POST['id_presupuesto'] : 0;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

    // Logs detallados para debug
    error_log("Guardar Descripción - Recibido ID: " . $id_presupuesto . ", Descripción: " . $descripcion);

    // Validar ID y descripción
    if ($id_presupuesto > 0) {
        // Preparar y ejecutar la sentencia UPDATE
        $stmt = $conn->prepare("UPDATE presupuestos SET descripcion = ? WHERE id_presupuesto = ?");

        if ($stmt === false) {
             error_log("Guardar Descripción - Error al preparar sentencia: " . $conn->error);
             echo 'error: Error interno al preparar la actualización.';
             $conn->close();
             exit;
        }

        $stmt->bind_param("si", $descripcion, $id_presupuesto);

        if ($stmt->execute()) {
            // Log de éxito
            error_log("Guardar Descripción - Éxito al actualizar ID: " . $id_presupuesto);
            echo 'ok'; // Indicar éxito
        } else {
            // Logear error si la ejecución falla
            error_log("Guardar Descripción - Error al ejecutar UPDATE (ID: " . $id_presupuesto . "): " . $stmt->error);
            echo 'error: Error al actualizar la descripción en la base de datos.';
        }
        $stmt->close();
    } else {
        error_log("Guardar Descripción - ID de presupuesto inválido: " . $id_presupuesto);
        echo 'error: ID de presupuesto inválido.';
    }
    $conn->close();
    exit; // Terminar script después de manejar la petición
}

// *** Lógica Unificada para Crear o Actualizar Presupuesto ***
// Esta sección manejará tanto la creación de un cliente nuevo (si aplica) como la creación/actualización del presupuesto y sus ítems.

// Asegurar que la conexión use UTF-8
$conn->set_charset("utf8mb4");

// Validar que los datos mínimos necesarios para un presupuesto estén presentes en el POST
if (isset($_POST['id_cliente'], $_POST['id_stock'], $_POST['cantidad'], $_POST['precio_unitario'], $_POST['subtotal'])) {

    $id_presupuesto = $_POST['id_presupuesto'] ?? null; // Será null para nueva creación
    $id_cliente = $_POST['id_cliente'];
    $fecha_creacion = $_POST['fecha_creacion'] ?? date('Y-m-d');
    // Recargo final opcional, usar 0 si no viene o está vacío
    $recargo_final = isset($_POST['recargo_final']) && is_numeric($_POST['recargo_final']) ? (float)$_POST['recargo_final'] : 0.0;
    // Asegurarnos de que la descripción se procese correctamente
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

    // Log para debug: Verificar el valor de la descripción que llega al backend
    error_log("Descripción recibida en presupuesto_action.php: " . $descripcion);

    $id_stock_array = $_POST['id_stock'];
    $cantidad_array = $_POST['cantidad'];
    $precio_unitario_array = $_POST['precio_unitario'];
    $subtotal_array = $_POST['subtotal'];

    // Validar que haya al menos un ítem
    if (count($id_stock_array) === 0) {
        echo 'error:sin_items';
        exit;
    }

    // Calcular el total sumando los subtotales (siempre recalcular en backend es más seguro)
    $total = 0;
    foreach ($subtotal_array as $sub) {
        $total += (float)$sub;
    }

    // Calcular el total con recargo final
    $total_con_recargo = $total * (1 + $recargo_final / 100);

    $conn->begin_transaction();
    try {
        // 1. Manejar la creación de nuevo cliente si id_cliente es 'nuevo'
        if ($id_cliente === 'nuevo') {
            $nombre = trim($_POST['nuevo_nombre'] ?? '');
            $email = trim($_POST['nuevo_email'] ?? '');
            $telefono = trim($_POST['nuevo_telefono'] ?? '');
            $direccion = trim($_POST['nuevo_direccion'] ?? '');

            if (!$nombre) {
                // Rollback si no hay nombre para el nuevo cliente
                $conn->rollback();
                echo 'error:Nombre del nuevo cliente requerido';
                exit;
            }

            $stmt_cliente = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
            $stmt_cliente->bind_param("ssss", $nombre, $email, $telefono, $direccion);
            $stmt_cliente->execute();
            $id_cliente = $stmt_cliente->insert_id; // Usar el ID del cliente recién creado
            $stmt_cliente->close();

            if (!$id_cliente) {
                 // Rollback si falló la creación del cliente
                $conn->rollback();
                echo 'error:Error al crear el nuevo cliente';
                exit;
            }
        }

        // 2. Insertar o Actualizar el Presupuesto
        if ($id_presupuesto) {
            // Actualizar presupuesto existente
            $stmt_presupuesto = $conn->prepare("UPDATE presupuestos SET id_cliente=?, fecha_creacion=?, total=?, recargo_final=?, total_con_recargo=?, descripcion=? WHERE id_presupuesto=?");
            $stmt_presupuesto->bind_param("isddddsi", $id_cliente, $fecha_creacion, $total, $recargo_final, $total_con_recargo, $descripcion, $id_presupuesto);
            $stmt_presupuesto->execute();
            $stmt_presupuesto->close();

            // Eliminar ítems viejos antes de insertar los nuevos
            $conn->query("DELETE FROM presupuesto_items WHERE id_presupuesto = " . (int)$id_presupuesto);

        } else {
            // Insertar nuevo presupuesto
            $estado = 'abierto'; // Estado por defecto para nuevo presupuesto
            $stmt_presupuesto = $conn->prepare("INSERT INTO presupuestos (id_cliente, fecha_creacion, estado, total, recargo_final, total_con_recargo, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_presupuesto->bind_param("issddds", $id_cliente, $fecha_creacion, $estado, $total, $recargo_final, $total_con_recargo, $descripcion);
            $stmt_presupuesto->execute();
            $id_presupuesto = $stmt_presupuesto->insert_id; // Obtener el ID del presupuesto recién creado
            $stmt_presupuesto->close();

            if (!$id_presupuesto) {
                // Rollback si falló la creación del presupuesto
                $conn->rollback();
                echo 'error:Error al crear el presupuesto';
                exit;
            }
        }

        // 3. Insertar los Ítems del Presupuesto (para creación y actualización)
        $stmt_item = $conn->prepare("INSERT INTO presupuesto_items (id_presupuesto, id_stock, cantidad, precio_unitario, subtotal, precio_con_recargo) VALUES (?, ?, ?, ?, ?, ?)");

        for ($i = 0; $i < count($id_stock_array); $i++) {
            $id_stock        = (int)$id_stock_array[$i];
            $cantidad        = (int)$cantidad_array[$i];
            // Usar el precio_unitario tal como viene, ya que JS le aplicó el recargo por ítem si aplica
            $precio_unitario_item = (float)$precio_unitario_array[$i];
            $subtotal_item        = (float)$subtotal_array[$i];

            // Calcular precio_con_recargo por ítem si se guardara individualmente (opcional, si la BD lo pide)
            // En tu BD hay un campo precio_con_recargo en presupuesto_items. Necesitamos calcularlo.
            // Si recargo_producto venía del frontend, deberíamos haberlo enviado.
            // Asumimos que precio_unitario_item ya tiene el recargo POR ITEM aplicado por JS.
            // Si no, habría que añadir un campo para recargo_producto en el frontend y enviarlo.
            // Por ahora, asumiré que precio_unitario_item YA INCLUYE el recargo por producto si lo hubo.
            // Y subtotal_item es cantidad * precio_unitario_item.
            // El campo `precio_con_recargo` en la tabla `presupuesto_items` probablemente se refiere al precio unitario CON recargo por item.
            $precio_con_recargo_item = $precio_unitario_item; // Si precio_unitario_item ya tiene el recargo por item.


            $stmt_item->bind_param("iiiddd", $id_presupuesto, $id_stock, $cantidad, $precio_unitario_item, $subtotal_item, $precio_con_recargo_item);
            $stmt_item->execute();
             // Verificar si hubo error en la inserción del item
             if ($stmt_item->error) {
                 throw new Exception("Error al insertar ítem: " . $stmt_item->error);
             }
        }
        $stmt_item->close();

        // Si todo fue bien, confirmar la transacción y responder 'ok'
        $conn->commit();
        $conn->close();
        echo 'ok'; // Responder 'ok' para que JS sepa que todo fue bien
        exit; // Terminar el script aquí

    } catch (Exception $e) {
        // Si algo falla, hacer rollback y responder con error
        $conn->rollback();
        $conn->close();
        // Responder con un mensaje de error más detallado si es posible
        echo 'error: ' . $e->getMessage();
        exit; // Terminar el script aquí después del error
    }

}

// Si la petición POST no tiene los datos mínimos esperados para crear/actualizar un presupuesto,
// simplemente terminamos el script sin hacer nada (o podrías loggear/manejar esto si es un error inesperado)
?>
