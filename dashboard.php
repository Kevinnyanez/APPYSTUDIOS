<?php


session_start();
require_once 'includes/db.php';
if (!isset($_SESSION['id'])) {
    header('Location: index.php');
    exit();
}

$nombre = $_SESSION['nombre_usuario'];
$sql = "SELECT * FROM stock";

$result = $conn->query($sql);

$sql_clientes = "SELECT * FROM clientes";
$result_clientes = $conn ->query($sql_clientes);

// --- CONSULTAS PARA DASHBOARD ---
// 1. Resumen de presupuestos
$resPresup = $conn->query("SELECT COUNT(*) as total, SUM(total_con_recargo) as suma, SUM(CASE WHEN estado='abierto' THEN 1 ELSE 0 END) as abiertos, SUM(CASE WHEN estado='cerrado' THEN 1 ELSE 0 END) as cerrados FROM presupuestos");
$presupResumen = $resPresup->fetch_assoc();
$resProm = $conn->query("SELECT AVG(total_con_recargo) as promedio FROM presupuestos");
$promPresupuesto = $resProm->fetch_assoc()['promedio'] ?? 0;

// 2. Estado de clientes
$resClientes = $conn->query("SELECT COUNT(*) as total FROM clientes");
$totalClientes = $resClientes->fetch_assoc()['total'] ?? 0;
$ultimosClientes = $conn->query("SELECT * FROM clientes ORDER BY fecha_registro DESC LIMIT 5");
$clientesMasPresup = $conn->query("SELECT c.*, COUNT(p.id_presupuesto) as cantidad FROM clientes c LEFT JOIN presupuestos p ON c.id_cliente = p.id_cliente GROUP BY c.id_cliente ORDER BY cantidad DESC, c.nombre ASC LIMIT 5");

// 3. Estado del stock
$stockBajo = $conn->query("SELECT * FROM stock ORDER BY cantidad ASC, nombre ASC LIMIT 5");
$tiposMasUsados = $conn->query("SELECT s.tipo, COUNT(pi.id_item) as usados FROM presupuesto_items pi JOIN stock s ON pi.id_stock = s.id_stock GROUP BY s.tipo ORDER BY usados DESC LIMIT 3");

// 4. Últimos presupuestos
$ultimosPresup = $conn->query("SELECT p.*, c.nombre as nombre_cliente FROM presupuestos p JOIN clientes c ON p.id_cliente = c.id_cliente ORDER BY p.fecha_creacion DESC LIMIT 5");
$presupAbiertosAntiguos = $conn->query("SELECT p.*, c.nombre as nombre_cliente FROM presupuestos p JOIN clientes c ON p.id_cliente = c.id_cliente WHERE p.estado='abierto' ORDER BY p.fecha_creacion ASC LIMIT 5");
$presupAbiertos = $conn->query("SELECT p.*, c.nombre as nombre_cliente FROM presupuestos p JOIN clientes c ON p.id_cliente = c.id_cliente WHERE p.estado='abierto' ORDER BY p.fecha_creacion DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <style>

        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #cbd5e1;
            color: #222;
            margin: 0;
            padding: 20px;
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

        /* Contenedor general con flex para dividir en columnas */
        .dashboard {
            display: flex;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap; /* para que sea responsivo */
        }

        .welcome-container {
            background: #ffffff;
            border-left: 5px solid #38bdf8;
            padding: 20px 25px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            flex: 1 1 300px;
            font-size: 1.1em;
            color: #334155;
            line-height: 1.5;
            transition: background-color 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            min-width: 280px;
        }

        .welcome-container:hover {
            background-color: #e0f2fe;
            box-shadow: 0 4px 12px rgba(56,189,248,0.3);
            border-color: #0284c7;
            cursor: default;
        }

        .welcome-container strong {
            color: #2563eb;
            font-weight: 700;
        }

        .welcome-container p {
            margin-top: 12px;
            color: #64748b;
            font-weight: 500;
        }

        /* Contenedor tabla stock */
        table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
  background-color: #222;
  border-radius: 8px;
  overflow: hidden;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #444;
}

th {
  background-color: #333;
  color: #fff;
  font-weight: bold;
  font-size: 14px;
}

td {
  font-size: 14px;
  color: #fff;
}

tr:hover {
  background-color:#383838;
  transition: background-color 0.3s ease;
}

a {
  color: #007bff;
  text-decoration: none;
  margin: 0 5px;
}

a:hover {
  text-decoration: underline;
}

.btn-volver {
  display: inline-block;
  margin-top: 20px;
  padding: 10px 16px;
  background-color: #007bff;
  color: white;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: background-color 0.2s ease;
}

.btn-volver:hover {
  background-color: #0056b3;
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
        
        .btn-volver {
            
            display: inline-block; padding: 8px 12px; background: #222; color: white; text-decoration: none; border-radius: 4px; margin: 10px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.10);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            margin-bottom: 0;
            position: relative;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .card h2 {
            font-size: 1.25rem;
            margin: 0 0 1.2rem 0;
            color: #0077b6;
            font-weight: 700;
        }
        .kpi {
            font-size: 2.2rem;
            font-weight: 800;
            color: #0077b6;
            margin-bottom: 0.2rem;
        }
        .kpi-label {
            font-size: 1rem;
            color: #555;
            margin-bottom: 1.2rem;
        }
        .kpi-green { color: #22c55e; }
        .kpi-red { color: #ef4444; }
        .kpi-orange { color: #f59e42; }
        .card ul {
            list-style: none;
            padding: 0;
            margin: 0 0 0.5rem 0;
        }
        .card ul li {
            margin-bottom: 0.4rem;
            font-size: 1rem;
            color: #333;
        }
        .card .ver-mas {
            color: #0077b6;
            cursor: pointer;
            font-size: 0.98rem;
            margin-top: 0.2rem;
            display: inline-block;
            text-decoration: underline;
        }
        .card .ver-mas:hover { color: #023e8a; }
        .card .btn-volver {
            background: #38bdf8;
            color: #222;
            font-weight: 700;
            border-radius: 8px;
            padding: 10px 18px;
            margin-top: 1.2rem;
            font-size: 1.1rem;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            border: none;
            transition: background 0.2s;
        }
        .card .btn-volver:hover { background: #0ea5e9; color: #fff; }
        .card .alerta {
            color: #ef4444;
            font-weight: 600;
            font-size: 1.05rem;
        }
        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav>
        <a href="stock.php">Ver Stock</a>
        <a href="presupuestos.php">Presupuestos</a>
        <a href="ventas.php">Ventas</a>
        <a href="clientes.php">Clientes</a>
        <a href="recomendaciones.php">Recomendaciones</a>
        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </nav>

    <div class="dashboard">
        <div class="welcome-container">
            Bienvenido, <strong><?= htmlspecialchars($nombre) ?></strong> a tu panel de control.
            <p>Desde aquí podés administrar el stock, crear presupuestos y controlar las ventas.</p>
        </div>

       <div class="dashboard-grid">
  <!-- Resumen de Presupuestos -->
  <div class="card">
    <h2>Resumen de Presupuestos</h2>
    <div class="kpi"><?= $presupResumen['total'] ?></div>
    <div class="kpi-label">Total creados</div>
    <div class="kpi kpi-orange">Abiertos: <?= $presupResumen['abiertos'] ?></div>
    <div class="kpi kpi-green">Cerrados: <?= $presupResumen['cerrados'] ?></div>
    <div class="kpi-label">Monto total: <span class="kpi">$<?= number_format($presupResumen['suma'] ?? 0,2) ?></span></div>
    <div class="kpi-label">Promedio por cliente: <span class="kpi">$<?= number_format($promPresupuesto,2) ?></span></div>
    <div style="margin-top:1.2rem;">
      <strong>Últimos presupuestos</strong>
      <ul id="ultimosPresupuestos">
        <?php $i=0; while($p = $ultimosPresup->fetch_assoc()): if($i++>=3) break; ?>
          <li><strong>#<?= $p['id_presupuesto'] ?></strong> - <?= htmlspecialchars($p['nombre_cliente']) ?> - <?= ucfirst($p['estado']) ?> - $<?= number_format($p['total_con_recargo'],2) ?> <span style="color:#888;">(<?= date('d/m/Y', strtotime($p['fecha_creacion'])) ?>)</span></li>
        <?php endwhile; ?>
      </ul>
      <span class="ver-mas" onclick="toggleVerMas('ultimosPresupuestos', <?= $i-1 ?>)">Ver más</span>
    </div>
  </div>
  <!-- Estado de Clientes -->
  <div class="card">
    <h2>Estado de Clientes</h2>
    <div class="kpi kpi-green"><?= $totalClientes ?></div>
    <div class="kpi-label">Clientes registrados</div>
    <strong>Últimos añadidos</strong>
    <ul id="ultimosClientes">
      <?php $i=0; while($c = $ultimosClientes->fetch_assoc()): if($i++>=3) break; ?>
        <li><?= htmlspecialchars($c['nombre']) ?> (<?= htmlspecialchars($c['email']) ?>)</li>
      <?php endwhile; ?>
    </ul>
    <span class="ver-mas" onclick="toggleVerMas('ultimosClientes', <?= $i-1 ?>)">Ver más</span>
    <strong style="margin-top:1rem; display:block;">Top clientes</strong>
    <ul id="topClientes">
      <?php $i=0; while($c = $clientesMasPresup->fetch_assoc()): if($i++>=3) break; ?>
        <li><?= htmlspecialchars($c['nombre']) ?> (<?= $c['cantidad'] ?> presupuestos)</li>
      <?php endwhile; ?>
    </ul>
    <span class="ver-mas" onclick="toggleVerMas('topClientes', <?= $i-1 ?>)">Ver más</span>
    <strong style="margin-top:1rem; display:block;">Contactos rápidos</strong>
    <ul id="contactosRapidos">
      <?php $ultimosClientes2 = $conn->query("SELECT * FROM clientes ORDER BY fecha_registro DESC LIMIT 5"); $i=0; while($c = $ultimosClientes2->fetch_assoc()): if($i++>=3) break; ?>
        <li><a href="mailto:<?= htmlspecialchars($c['email']) ?>">📧 <?= htmlspecialchars($c['nombre']) ?></a> - <?= htmlspecialchars($c['telefono']) ?></li>
      <?php endwhile; ?>
    </ul>
    <span class="ver-mas" onclick="toggleVerMas('contactosRapidos', <?= $i-1 ?>)">Ver más</span>
  </div>
  <!-- Estado del Stock -->
  <div class="card">
    <h2>Estado del Stock</h2>
    <strong>Productos con menor disponibilidad</strong>
    <ul id="stockBajo">
      <?php $i=0; while($s = $stockBajo->fetch_assoc()): if($i++>=3) break; ?>
        <li><?= htmlspecialchars($s['nombre']) ?> <span class="alerta">(<?= $s['cantidad'] ?> unidades)</span></li>
      <?php endwhile; ?>
    </ul>
    <span class="ver-mas" onclick="toggleVerMas('stockBajo', <?= $i-1 ?>)">Ver más</span>
    <strong style="margin-top:1rem; display:block;">Tipos más usados en presupuestos</strong>
    <ul id="tiposMasUsados">
      <?php $i=0; while($t = $tiposMasUsados->fetch_assoc()): if($i++>=3) break; ?>
        <li><?= htmlspecialchars($t['tipo']) ?> (<?= $t['usados'] ?> usos)</li>
      <?php endwhile; ?>
    </ul>
    <span class="ver-mas" onclick="toggleVerMas('tiposMasUsados', <?= $i-1 ?>)">Ver más</span>
  </div>
  <!-- Acciones rápidas -->
  <div class="card">
    <h2>Acciones rápidas</h2>
    <a href="presupuesto_form.php" class="btn-volver">+ Nuevo presupuesto</a>
    <strong style="margin-top:1.2rem; display:block;">Presupuestos pendientes de revisión</strong>
    <ul id="presupAbiertos">
      <?php $i=0; while($p = $presupAbiertos->fetch_assoc()): if($i++>=3) break; ?>
        <li><a href="presupuesto_form.php?id_presupuesto=<?= $p['id_presupuesto'] ?>">#<?= $p['id_presupuesto'] ?> - <?= htmlspecialchars($p['nombre_cliente']) ?></a></li>
      <?php endwhile; ?>
    </ul>
    <span class="ver-mas" onclick="toggleVerMas('presupAbiertos', <?= $i-1 ?>)">Ver más</span>
  </div>
</div>

    </div>

    <script>
    function toggleVerMas(id, mostrados) {
        const ul = document.getElementById(id);
        if (!ul) return;
        const lis = ul.querySelectorAll('li');
        let ocultos = 0;
        lis.forEach((li, idx) => {
            if (idx > 2) {
                if (li.style.display === 'none' || !li.style.display) {
                    li.style.display = 'list-item';
                    ocultos++;
                } else {
                    li.style.display = 'none';
                }
            }
        });
        // Cambia el texto del botón
        const btn = ul.nextElementSibling;
        if (btn) btn.textContent = ocultos ? 'Ver menos' : 'Ver más';
    }
    // Al cargar, oculta los extras
    window.addEventListener('DOMContentLoaded', () => {
        ['ultimosPresupuestos','ultimosClientes','topClientes','contactosRapidos','stockBajo','tiposMasUsados','presupAbiertos'].forEach(id => {
            const ul = document.getElementById(id);
            if (!ul) return;
            const lis = ul.querySelectorAll('li');
            lis.forEach((li, idx) => { if (idx > 2) li.style.display = 'none'; });
        });
    });
    </script>
</body>
</html>
