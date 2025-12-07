<?php
declare(strict_types=1);
$user = $_SESSION['user'] ?? 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VisApp · Notificaciones</title>
  <link rel="stylesheet" href="/VissApp_v2/public/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="d-flex">
  <!-- Incluye tu sidebar y topbar aquí, igual que en dashboard/index.php -->

  <div class="flex-fill d-flex flex-column">
    <!-- Título y botón de regreso -->
    <nav class="navbar navbar-light bg-white shadow-sm px-4">
      <button class="btn btn-link" onclick="location.href='index.php?route=dashboard'">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
      </button>
    </nav>
    <div class="container-fluid mt-4">
      <h1 class="h3">Notificaciones recientes</h1>
    </div>

    <div class="container-fluid mb-5">
      <div class="table-responsive">
        <table id="tablaNotificaciones" class="table table-striped table-bordered w-100">
          <thead class="text-center">
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Tipo</th>
              <th>Detalle</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody>
            <!-- Se llenará con AJAX -->
          </tbody>
        </table>
      </div>
    </div>

    <footer class="bg-white py-3 text-center">
      <span>Copyright &copy; VisApp 2025</span>
    </footer>
  </div>

  <script src="/VissApp_v2/public/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="/VissApp_v2/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    $(function(){
      // Carga vía AJAX las notificaciones
      $.getJSON('index.php?route=notifications_json', function(data){
        const tbody = $('#tablaNotificaciones tbody').empty();
        data.forEach(n => {
          $('<tr>')
            .append(`<td>${n.id}</td>`)
            .append(`<td>${n.usuario_id}</td>`)
            .append(`<td>${n.tipo}</td>`)
            .append(`<td>${n.detalle}</td>`)
            .append(`<td>${n.created_at}</td>`)
            .appendTo(tbody);
        });
      });
    });
  </script>
</body>
</html>
