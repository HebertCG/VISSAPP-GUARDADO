<?php

declare(strict_types=1);
$user = $_SESSION['user'] ?? 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VisApp - Dashboard</title>

  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="css/stylebd.css">

</head>

<body class="d-flex">
  <!-- ────────── SIDEBAR ────────── -->
  <nav class="sidebar text-white p-4 vh-100">
    <h4><i class="fas fa-tachometer-alt"></i> VissApp</h4>
    <hr class="border-light">
    <small class="text-white-50">Usuarios</small>
    <ul class="nav flex-column mb-4">
      <li class="nav-item mb-1">
        <a href="index.php?route=dashboard" class="nav-link text-white pl-0">
          <i class="fas fa-user-plus"></i> Ingresar Usuario
        </a>
      </li>
      <li class="nav-item">
        <a href="index.php?route=lista_usuarios" class="nav-link text-white pl-0">
          <i class="fas fa-users"></i> Lista Usuarios
        </a>
      </li>
      <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'soporte'): ?>
        <li class="nav-item">
          <a href="index.php?route=verificar_cuentas" class="nav-link text-white pl-0">
            <i class="fas fa-check-circle"></i> Verificar Cuentas
          </a>
        </li>
      <?php endif; ?>
    </ul>
    <small class="text-white-50">Soporte</small>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="index.php?route=soporte" class="nav-link text-white pl-0">
          <i class="fas fa-life-ring"></i> Soporte
        </a>
      </li>
    </ul>
  </nav>

  <!-- ────────── CONTENIDO PRINCIPAL ────────── -->
  <div class="main-content">

    <!-- Topbar limpio -->
    <nav class="navbar topbar-clean">
      <form class="search-wrapper" onsubmit="return false;">
        <input id="searchInput" type="text" placeholder="Buscar…">
        <button id="searchBtn" type="button" class="icon-btn">
          <i class="fas fa-search search-icon"></i>
        </button>
      </form>

      <ul class="navbar-nav">
        <li class="nav-item dropdown mx-2">
          <a id="notifMenu" class="nav-link icon-btn" data-toggle="dropdown" href="#">
            <i class="fas fa-bell"></i>
            <span id="notifCount" class="badge-pill-notif">0</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right notif-dropdown">
            <div class="dropdown-header">Notificaciones</div>
            <div id="notifDropdown" class="notif-scroll">
              <p class="list-group-item text-center text-muted small m-0">Sin notificaciones</p>
            </div>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a id="userMenu" class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
            <span class="d-none d-lg-inline mr-2"><?= htmlspecialchars($user) ?></span>
            <img class="avatar-sm" src="img/user.png" alt="avatar">
          </a>
          <div class="dropdown-menu dropdown-menu-right user-dropdown">
            <h6 class="dropdown-header">
              <?= htmlspecialchars($user) ?><br>
              <small><?= $_SESSION['rol'] ?? '' ?></small>
            </h6>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="index.php?route=logout">
              <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Tarjetas de alerta -->
    <div class="container-fluid mt-3">
      <div class="row text-center">
        <div class="col-md-3 mb-3">
          <div class="card border-danger shadow-sm filter-card" data-min="0" data-max="29">
            <div class="card-body">
              <i class="fas fa-calendar-times fa-2x text-danger"></i>
              <h6 class="mt-2">Alerta Roja (&lt;30 días)</h6>
              <span class="h4"><?= $red ?? 0 ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card border-warning shadow-sm filter-card" data-min="30" data-max="59">
            <div class="card-body">
              <i class="fas fa-calendar-alt fa-2x text-warning"></i>
              <h6 class="mt-2">Alerta Amarilla (30–59 días)</h6>
              <span class="h4"><?= $yellow ?? 0 ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card border-success shadow-sm filter-card" data-min="60" data-max="89">
            <div class="card-body">
              <i class="fas fa-calendar-check fa-2x text-success"></i>
              <h6 class="mt-2">Alerta Verde (60–89 días)</h6>
              <span class="h4"><?= $green ?? 0 ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card border-info shadow-sm filter-card">
            <div class="card-body">
              <i class="fas fa-globe fa-2x text-info"></i>
              <h6 class="mt-2">Total Visas</h6>
              <span class="h4"><?= $total ?? 0 ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón añadir -->
    <div class="container-fluid mb-3">
      <button class="btn btn-success" onclick="location.href='index.php?route=persona_add'">
        <i class="fas fa-plus mr-1"></i> Añadir Usuario
      </button>
    </div>

    <!-- Tabla -->
    <div class="container-fluid mb-5 d-flex flex-column flex-grow-1">
      <div class="table-scroll-body flex-grow-1">
        <div class="table-responsive">
          <!-- ‼️ Cambiamos las clases a  table table-custom -->
          <table id="tablaPersonas" class="table table-custom w-100 mb-0">
            <thead>
              <tr>
                <th>Id</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Tipo Visa</th>
                <th>País</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Edad</th>
                <th>NumeroVisa</th>
                <th>Referencia</th>
                <th>Fecha Inicio</th>
                <th>Fecha Final</th>
                <th>Días Restantes</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data)): ?>
                <?php foreach ($data as $dat): ?>
                  <tr>
                    <td><?= (int)$dat['id'] ?></td>
                    <td><?= htmlspecialchars($dat['nombre']) ?></td>
                    <td><?= htmlspecialchars($dat['apellido'] ?? '') ?></td>
                    <td><?= htmlspecialchars($dat['tipoVisa'] ?? '') ?></td>
                    <td><?= htmlspecialchars($dat['pais']) ?></td>
                    <td><?= htmlspecialchars($dat['correo']) ?></td>
                    <td><?= htmlspecialchars($dat['telefono']) ?></td>
                    <td><?= (int)$dat['edad'] ?></td>
                    <td><?= htmlspecialchars($dat['numeroVisa']) ?></td>
                    <td><?= htmlspecialchars($dat['referenciaTransaccion'] ?? '') ?></td>
                    <td><?= htmlspecialchars($dat['fechaInicio']) ?></td>
                    <td><?= htmlspecialchars($dat['fechaFinal']) ?></td>
                    <td>
                      <?php
                      $d = (int)$dat['daysRemaining'];
                      $cls = $d < 30 ? 'badge-danger' : ($d < 60 ? 'badge-warning' : ($d < 90 ? 'badge-success' : 'badge-secondary'));
                      ?>
                      <span class="badge <?= $cls ?>"><?= $d ?> días</span>
                    </td>
                    <td>
                      <button class="btn btn-sm btn-warning btnEditar">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-danger btnBorrar">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr class="no-results">
                  <td colspan="13">No hay usuarios para mostrar</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <footer class="bg-white py-3 text-center mt-auto">
      <span>Copyright © VisApp 2026</span>
    </footer>
  </div>

  <!-- ────────── SCRIPTS ────────── -->
  <script src="vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 <script>
    $(function() {
        const $input = $('#searchInput'),
            $btn = $('#searchBtn'),
            $tbody = $('#tablaPersonas tbody');

        /* Filtrado manual */
        function filtrar() {
            const term = $input.val().trim().toLowerCase();
            let visible = 0;
            $tbody.find('tr').each(function() {
                const $tr = $(this),
                    nombre = $tr.find('td').eq(1).text().toLowerCase(),
                    apellido = $tr.find('td').eq(2).text().toLowerCase(),
                    numeroVisa = $tr.find('td').eq(8).text().toLowerCase(),
                    match = nombre.includes(term) || apellido.includes(term) || numeroVisa.includes(term);
                $tr.toggle(match);
                if (match) visible++;
            });
            $tbody.find('.no-results').remove();
            if (visible === 0) {
                const colspan = $('#tablaPersonas thead th').length;
                $tbody.append(`<tr class="no-results"><td colspan="${colspan}">No hay usuarios para mostrar</td></tr>`);
            }
        }
        $input.on('keyup', filtrar);
        $btn.on('click', filtrar);

        /* Editar */
        $('#tablaPersonas').on('click', '.btnEditar', function() {
            const id = $(this).closest('tr').find('td').eq(0).text().trim();
            location.href = `index.php?route=persona_edit&id=${id}`;
        });

        /* Borrar */
        $('#tablaPersonas').on('click', '.btnBorrar', function() {
            const $tr = $(this).closest('tr'),
                id = $tr.find('td').eq(0).text().trim();
            Swal.fire({
                title: `¿Eliminar usuario #${id}?`,
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post('index.php?route=persona_delete', {
                    id
                }, resp => {
                    if (resp.success) {
                        Swal.fire('Eliminado', `Usuario #${id} eliminado.`, 'success');
                        $tr.fadeOut(200, () => $tr.remove());
                        loadNotifications(); // Recargar notificaciones después de una eliminación
                    } else {
                        Swal.fire('Error', resp.error || 'No se pudo eliminar.', 'error');
                    }
                }, 'json').fail(() => Swal.fire('Error', 'La petición falló.', 'error'));
            });
        });

        /* Notificaciones */
        function loadNotifications() {
            $.getJSON('index.php?route=notifications_json', function(data) {
                const $dd = $('#notifDropdown').empty(); // Asegúrate de que este sea el ID de tu contenedor de notificaciones
                const $notifCount = $('#notifCount'); // Asegúrate de que este sea el ID del elemento que muestra el conteo

                // Actualiza el conteo de notificaciones y activa/desactiva la animación
                const notifCount = data.length;
                $notifCount.text(notifCount);
                if (notifCount > 0) {
                    $notifCount.addClass('active');
                } else {
                    $notifCount.removeClass('active');
                }

                if (!Array.isArray(data) || !data.length) {
                    $dd.append('<p class="text-center text-muted small m-0 py-2">Sin notificaciones</p>');
                    return;
                }

                // Contenedor para las notificaciones, para aplicar scroll si hay muchas
                const $scrollContainer = $('<div class="notif-scroll"></div>');
                $dd.append($scrollContainer);

                data.forEach((n, index) => {
                    let iconClass = 'fas fa-info-circle'; // Icono por defecto
                    let iconColorClass = 'icon-default'; // Clase de color por defecto
                    let notificationTitle = 'Actualización';
                    let notificationBody = '';

                    // Determinar el icono y el texto del mensaje según el campo que cambió
                    switch (n.field) {
                        case 'nombre':
                            iconClass = 'fas fa-user';
                            iconColorClass = 'icon-name-change';
                            notificationTitle = `Cambio de Nombre`;
                            notificationBody = `El nombre de usuario cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'apellido':
                            iconClass = 'fas fa-user-tag';
                            iconColorClass = 'icon-name-change'; // Reutilizamos el color de nombre
                            notificationTitle = `Cambio de Apellido`;
                            notificationBody = `El apellido de usuario cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'telefono':
                            iconClass = 'fas fa-phone';
                            iconColorClass = 'icon-phone-change';
                            notificationTitle = `Teléfono Actualizado`;
                            notificationBody = `El número de teléfono cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'correo':
                            iconClass = 'fas fa-envelope';
                            iconColorClass = 'icon-email-change';
                            notificationTitle = `Correo Electrónico Actualizado`;
                            notificationBody = `El correo cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'pais':
                            iconClass = 'fas fa-globe';
                            iconColorClass = 'icon-country-change';
                            notificationTitle = `País Actualizado`;
                            notificationBody = `El país cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'edad':
                            iconClass = 'fas fa-birthday-cake';
                            iconColorClass = 'icon-age-change';
                            notificationTitle = `Edad Actualizada`;
                            notificationBody = `La edad cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'numeroVisa':
                            iconClass = 'fas fa-passport';
                            iconColorClass = 'icon-visa-change';
                            notificationTitle = `Número de Visa Actualizado`;
                            notificationBody = `El número de visa cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                        case 'deleted': // Suponiendo que hay un tipo de notificación para eliminación
                            iconClass = 'fas fa-trash-alt';
                            iconColorClass = 'icon-delete';
                            notificationTitle = `Usuario Eliminado`;
                            notificationBody = `El usuario "<strong>${n.old_value}</strong>" ha sido eliminado.`;
                            break;
                        case 'added': // Suponiendo que hay un tipo de notificación para añadir
                            iconClass = 'fas fa-user-plus';
                            iconColorClass = 'icon-add';
                            notificationTitle = `Nuevo Usuario Registrado`;
                            notificationBody = `Se ha registrado un nuevo usuario: "<strong>${n.new_value}</strong>".`;
                            break;
                        default:
                            // Si no hay un campo específico, usar el valor por defecto y el mensaje genérico
                            notificationTitle = `Cambio en ${n.field.replace('_',' ')}`;
                            notificationBody = `El valor de "${n.field.replace('_',' ')}" cambió de "<strong>${n.old_value}</strong>" a "<strong>${n.new_value}</strong>".`;
                            break;
                    }

                    // Formatear la fecha para un display más amigable (ej. "2 hrs ago")
                    const timestamp = new Date(n.changed_at.replace(' ', 'T') + 'Z'); // Convertir a ISO 8601 y UTC
                    const now = new Date();
                    const diffMs = now - timestamp;
                    const diffMinutes = Math.round(diffMs / (1000 * 60));
                    const diffHours = Math.round(diffMs / (1000 * 60 * 60));
                    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));

                    let timeAgo;
                    if (diffMinutes < 1) {
                        timeAgo = "justo ahora";
                    } else if (diffMinutes < 60) {
                        timeAgo = `${diffMinutes} min${diffMinutes === 1 ? '' : 's'} ago`;
                    } else if (diffHours < 24) {
                        timeAgo = `${diffHours} hr${diffHours === 1 ? '' : 's'} ago`;
                    } else {
                        timeAgo = `${diffDays} día${diffDays === 1 ? '' : 's'} ago`;
                    }


                    const $notificationItem = $(`
                        <a class="dropdown-item" href="#">
                            <div class="notif-avatar-icon ${iconColorClass}">
                                <i class="${iconClass}"></i>
                            </div>
                            <div class="notif-content">
                                <span class="notif-title">${notificationTitle}</span>
                                <p class="notif-body">${notificationBody}</p>
                                <span class="notif-timestamp">${timeAgo}</span>
                            </div>
                        </a>
                    `).hide(); // Oculta inicialmente

                    $scrollContainer.append($notificationItem); // Añade al contenedor de scroll

                    // Animar la aparición de cada notificación con un pequeño retraso
                    setTimeout(() => {
                        $notificationItem.fadeIn(300);
                    }, 100 * index);
                });
            }).fail(function() {
                console.error("Error al cargar notificaciones.");
                $('#notifDropdown').empty().append('<p class="text-center text-muted small m-0 py-2">Error al cargar notificaciones.</p>');
            });
        }

        // Cargar notificaciones al inicio
        loadNotifications();

        // Puedes recargar las notificaciones periódicamente si deseas, o solo al hacer clic en el ícono de la campana
        // setInterval(loadNotifications, 30000); // Cargar cada 30 segundos, si es necesario

        $('#notifMenu').on('click', function() {
            // Eliminar la clase 'active' para detener la animación del badge al abrir el dropdown
            $('#notifCount').removeClass('active');
            // Opcional: recargar notificaciones cada vez que se abre el dropdown para asegurar que estén actualizadas
            // loadNotifications();
        });
    });
</script>
</body>

</html>