<?php
// views/admin/dashboard.php

/**
 * Variables que envía el controlador DashboardController:
 *   $ventasHoy     (float): total de ventas del día
 *   $ventasMes     (float): total de ventas del mes
 *   $totalStock    (int)  : total de productos en stock
 *   $ultimasVentas (array): arreglo de objetos con las últimas ventas
 *   $datosSemana   (array): arreglo de 7 elementos con:
 *       [
 *         'etiqueta' => 'DD/MM',
 *         'valor'    => total de ventas ese día (float)
 *       ]
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Control Admin | Pecosol</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Favicon -->
  <link rel="icon"
        href="<?php echo BASE_URL; ?>assets/img/LogoPecosol.png"
        type="image/png">

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />

  <!-- Estilos generales -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">


  <style>
    body {
  background-color: #1a1a2e;
  background-image: url('<?php echo BASE_URL; ?>assets/img/overlapping-circles.svg');
  background-repeat: repeat;
  background-size: 60px;
  background-attachment: fixed;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #eaeaea;
  margin: 0;
  padding: 0;
}

/* Container general */
.container {
  max-width: 1100px;
  margin: 50px auto;
  padding: 0 20px;
}

/* Título */
h1 {
  text-align: center;
  color: #00fff0;
  margin-bottom: 40px;
}

/* Tarjetas de resumen */
.cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 40px;
}

.card {
  flex: 1;
  background-color: #0f3460;
  border-radius: 16px;
  padding: 25px;
  text-align: center;
  box-shadow: 0 0 20px rgba(0,255,240,0.1);
  transition: transform 0.2s ease;
}
.card:hover {
  transform: translateY(-5px);
}
.card h3 {
  color: #a0a0a0;
  font-size: 1rem;
  margin-bottom: 12px;
}
.card p {
  font-size: 28px;
  font-weight: bold;
  color: #00fff0;
}

/* Gráfica */
.chart-container {
  background-color: #16213e;
  border-radius: 16px;
  padding: 25px;
  margin-bottom: 40px;
  box-shadow: 0 0 15px rgba(0,255,240,0.08);
}
.chart-container h2 {
  color: #00fff0;
  margin-bottom: 20px;
  font-size: 20px;
}

/* Tabla de ventas */
.lst-ventas {
  color: #00fff0;
  margin-bottom: 15px;
  font-size: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background-color: #16213e;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 0 12px rgba(0,255,240,0.1);
}

th, td {
  padding: 14px 12px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  text-align: left;
}

th {
  background-color: #0f3460;
  color: #00fff0;
  font-weight: 600;
}

tr:last-child td {
  border-bottom: none;
}

/* ─── NAVBAR MODERNO ───────────────────────────── */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #0f172a; 
  padding: 12px 30px;
  border-bottom: 2px solid #00fff0;
  box-shadow: 0 2px 10px rgba(0, 255, 240, 0.05);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.navbar-left img {
  height: 28px;
}

.navbar-left span {
  font-size: 18px;
  font-weight: bold;
  color: #00fff0;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 25px;
}

.navbar-right a {
  text-decoration: none;
  color: #e0e0e0;
  font-weight: 500;
  transition: color 0.2s;
}

.navbar-right a:hover {
  color: #00fff0;
}

.navbar-right .logout {
  color: #ff6b6b;
  font-weight: bold;
}


  </style>
</head>
<body>

  <!-- Header -->
  <?php include __DIR__ . '/partials/header.php'; ?>

  <div class="container">
    <!-- Título principal -->
    <h1>Panel de Administración</h1>

    <!-- Tarjetas de resumen -->
    <div class="cards">
      <div class="card">
        <h3>Ventas Hoy</h3>
        <p>S/. <?php echo number_format($ventasHoy, 2, '.', ','); ?></p>
      </div>
      <div class="card">
        <h3>Ventas Mes</h3>
        <p>S/. <?php echo number_format($ventasMes, 2, '.', ','); ?></p>
      </div>
      <div class="card">
        <h3>Total Stock</h3>
        <p><?php echo $totalStock; ?> unid.</p>
      </div>
    </div>

    <!-- Gráfica de barras -->
    <div class="chart-container">
      <h2>Ventas Últimos 7 Días</h2>
      <canvas id="ventasChart" width="800" height="300"></canvas>
    </div>

    <!-- Últimas Ventas -->
    <h2 class="lst-ventas">Últimas Ventas</h2>
    <?php if (!empty($ultimasVentas)): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Empleado</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Total</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasVentas as $venta): ?>
            <tr>
              <td><?php echo $venta->id; ?></td>
              <td><?php echo htmlspecialchars($venta->user_name); ?></td>
              <td><?php echo htmlspecialchars($venta->product_name); ?></td>
              <td><?php echo $venta->quantity; ?></td>
              <td>S/. <?php echo number_format($venta->unit_price, 2, '.', ','); ?></td>
              <td>S/. <?php echo number_format($venta->total_price, 2, '.', ','); ?></td>
              <td><?php echo date('d-m-Y H:i', strtotime($venta->sale_date)); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="color:#a0a0a0;">No hay ventas registradas aún.</p>
    <?php endif; ?>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.4.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo BASE_URL; ?>assets/js/chart.umd.js"></script>
  <script>
    const etiquetas = <?php echo json_encode(array_column($datosSemana, 'etiqueta')); ?>;
    const datos     = <?php echo json_encode(array_column($datosSemana, 'valor')); ?>;
    const ctx = document.getElementById('ventasChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: etiquetas,
        datasets: [{
          label: 'Ventas (S/.)',
          data: datos,
          backgroundColor: 'rgba(0, 255, 240, 0.5)',
          borderColor:   'rgba(0, 255, 240, 1)',
          borderWidth: 1
        }]
      },
      options: {
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  </script>

  <!-- Chatbot Widget -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/chatbot-widget.css">
  <script src="<?php echo BASE_URL; ?>assets/js/chatbot-widget.js?v=<?php echo time(); ?>"></script>
</body>
</html>
