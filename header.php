<?php
// Header de navegación unificado para toda la app
?>
<style>
nav.appy-navbar {
  background: #232b36;
  border-radius: 12px;
  padding: 22px 36px 18px 36px;
  margin-bottom: 32px;
  display: flex;
  align-items: center;
  gap: 36px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.10);
  position: sticky;
  top: 0;
  z-index: 100;
}
nav.appy-navbar a {
  color: #fff;
  text-decoration: none;
  font-size: 1.18rem;
  font-weight: 500;
  margin-right: 18px;
  transition: color 0.18s, background 0.18s, box-shadow 0.18s;
  padding: 6px 12px;
  border-radius: 6px;
}
nav.appy-navbar a:hover {
  background: #0077b6;
  color: #fff;
  box-shadow: 0 2px 8px rgba(0,119,182,0.10);
}
nav.appy-navbar .logout {
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
nav.appy-navbar .logout:hover {
  background: #b91c1c;
  color: #fff;
}
@media (max-width: 900px) {
  nav.appy-navbar {
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 8px 10px 8px;
  }
  nav.appy-navbar a {
    font-size: 1rem;
    margin-right: 8px;
    padding: 6px 8px;
  }
}
</style>
<nav class="appy-navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="stock.php">Ver Stock</a>
    <a href="clientes.php">Clientes</a>
    <a href="presupuestos.php">Presupuestos</a>
    <a href="ventas.php">Ventas</a>
    <a href="recomendaciones.php">Recomendaciones</a>
    <a href="logout.php" class="logout">Cerrar Sesión</a>
</nav> 