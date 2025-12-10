<?php
declare(strict_types=1);
 // Asegúrate de que la sesión esté iniciada para acceder a $_SESSION
$user = $_SESSION['user'] ?? 'Invitado';
// Simulación de datos para $count30, $count60, $count90 y $data, ya que no se proporcionan en el script
$count30 = $count30 ?? 0;
$count60 = $count60 ?? 0;
$count90 = $count90 ?? 0;
$data = $data ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VisApp · Lista Usuarios</title>
  <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="vendor/datatables/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="css/stylelista.css"> <link rel="stylesheet" href="css/styletopbar.css"> <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    
    .sidebar {
      position: fixed; top:0; left:0; bottom:0;
      width:240px; overflow-y:auto;
      background:#007bff; z-index:1000; /* z-index debe ser menor que el topbar si hay superposición */
    }
    .main-content {
      margin-left:240px; /* Espacio para el sidebar */
      padding-top: 64px;  /* Espacio para el topbar-clean (altura 64px) */
      display:flex;
      flex-direction:column;
      min-height:100vh; /* Usar min-height para asegurar que el footer quede abajo */
      box-sizing: border-box;
    }
    /* .navbar { overflow:visible!important; z-index:1100; } Esta regla puede ser eliminada o ajustada si causa conflictos, topbar-clean ya tiene z-index:1100 */
    .table-wrapper { flex:1; overflow-y:auto; }
    .table-responsive { min-width:1100px; }
    .filter-card { transition: .2s; cursor:pointer; }
    .filter-card.active { background:#e9ecef; border-width:2px!important; }
    .no-results td { text-align:center; font-style:italic; color:#777; }

    /* Estilos para el footer dentro de main-content flex */
    .main-content > footer {
      margin-top: auto; /* Empuja el footer al final del contenedor flex */
    }
  </style>
</head>
<body> 

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

  <nav class="navbar topbar-clean">
    <form class="search-wrapper" onsubmit="return false;">
      <input id="searchInput" type="text" placeholder="Buscar…">
      <i class="fas fa-search search-icon"></i>
    </form>

      <ul class="navbar-nav">
      
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

  <div class="main-content">


    <div class="container-fluid mt-3">
      <div class="row text-center">
        <div class="col-md-4 mb-3">
          <div id="filter-30" class="card border-danger shadow-sm filter-card" data-min="0" data-max="29">
            <div class="card-body">
              <i class="fas fa-calendar-times fa-2x text-danger"></i>
              <h6 class="mt-2">Menos de 30 días</h6>
              
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div id="filter-60" class="card border-warning shadow-sm filter-card" data-min="30" data-max="59">
            <div class="card-body">
              <i class="fas fa-calendar-alt fa-2x text-warning"></i>
              <h6 class="mt-2">30–59 días</h6>
              
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div id="filter-90" class="card border-success shadow-sm filter-card" data-min="60" data-max="89">
            <div class="card-body">
              <i class="fas fa-calendar-check fa-2x text-success"></i>
              <h6 class="mt-2">60–89 días</h6>
             
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid mb-5 table-wrapper">
      <div class="table-responsive">
        <table id="tablaLista" class="table table-striped table-bordered w-100 mb-0">
          <thead class="text-center">
            <tr>
              <th>Id</th><th>Nombre</th><th>Apellido</th>
              <th>Tipo Visa</th><th>País</th><th>Correo</th>
              <th>Teléfono</th><th>Referencia</th><th>Fecha Inicio</th><th>Fecha Final</th>
              <th>Días Restantes</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($data as $u): ?>
            <tr data-id="<?= (int)$u['id'] ?>"
                data-email="<?= htmlspecialchars((string)($u['correo'] ?? ''), ENT_QUOTES) ?>"
                data-tel="<?= htmlspecialchars((string)($u['telefono'] ?? ''), ENT_QUOTES) ?>"
                data-dias="<?= (int)($u['daysRemaining'] ?? 0) ?>">
              <td><?= htmlspecialchars((string)($u['id'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['nombre'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['apellido'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['tipoVisa'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['pais'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['correo'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['telefono'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['referenciaTransaccion'] ?? '—'), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['fechaInicio'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars((string)($u['fechaFinal'] ?? ''), ENT_QUOTES) ?></td>
              <td><?= (int)($u['daysRemaining'] ?? 0) ?> días</td>
              <td class="text-center">
                <button class="btn btn-sm btn-primary btn-send-email">
                  <i class="fas fa-envelope"></i>
                </button>
              </td>
            </tr>
            <?php endforeach;?>
            <?php if (empty($data)): ?>
              <td colspan="13">No hay usuarios para mostrar</td>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <footer class="bg-white py-3 text-center shadow-sm"> 
      <span>Copyright © VisApp 2026</span>
    </footer>
  </div>

  <script src="vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
  $(function(){
    const $tbody = $('#tablaLista tbody');

    // Manejador de clic del boton de enviar
    $tbody.on('click','.btn-send-email',function(){
      const $tr = $(this).closest('tr'),
            id   = $tr.data('id'),           // AGREGADO: obtener el ID
            mail = $tr.data('email'),
            dias = $tr.data('dias'),
            name = $tr.find('td').eq(1).text() + ' ' + $tr.find('td').eq(2).text();

      //Mostrar el modal de carga
      Swal.fire({
        title: 'Enviando correo...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();

          //peticion ajax para enviar email
          $.post('index.php?route=send_email',{ id, mail, dias, name },res=>{
            Swal.close();
            if(res.success){
              //exito 
              Swal.fire({
                icon: 'success',
                title: 'Email enviado',
                text: `Correo enviado a ${mail}`,
                confirmButtonText: 'OK',
                backdrop:false,
                scrollbarPadding:false
              });
            } else {
              //falso
              Swal.fire({
                icon: 'error',
                title: 'Error al enviar',
                text: res.error||'No se pudo enviar el email.',
                confirmButtonText: 'Cerrar',
                backdrop:false,
                scrollbarPadding:false
              });
            }
          },'json').fail(()=>{
            //error de conexion
            Swal.close();
            Swal.fire({
              icon:'error',
              title:'Error de conexión',
              text:'No se pudo conectar al servidor.',
              confirmButtonText:'Cerrar',
              backdrop:false,
              scrollbarPadding:false
            });
          });
        }
      });
    });

    // Example search input functionality (basic)
    // Adapt this if you have specific search logic
    $('#searchInput').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $("#tablaLista tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
      // Show "no results" if all rows are hidden
      if ($("#tablaLista tbody tr:visible").length === 0) {
        if ($(".no-results").length === 0) { // Avoid adding multiple "no results" rows
          $tbody.append('<tr><td colspan="11" class="no-results">No se encontraron resultados para su búsqueda.</td></tr>');
        } else {
          $(".no-results").show();
        }
      } else {
        $(".no-results").hide(); // Hide if there are visible results
      }
    });

    // Filter cards functionality (if it was present or intended)
    $('.filter-card').on('click', function() {
      $('.filter-card').removeClass('active');
      $(this).addClass('active');
      
      const minDays = parseInt($(this).data('min'));
      const maxDays = parseInt($(this).data('max'));
      let visibleRows = 0;

      $("#tablaLista tbody tr").each(function() {
        const rowDays = parseInt($(this).data('dias'));
        if (!$(this).hasClass('no-results')) { // Don't try to filter the "no-results" row
            if (rowDays >= minDays && rowDays <= maxDays) {
                $(this).show();
                visibleRows++;
            } else {
                $(this).hide();
            }
        }
      });
       // Show "no results" if all rows are hidden by filter
        if (visibleRows === 0) {
            if ($(".no-results").length === 0) {
                $tbody.append('<tr><td colspan="11" class="no-results">No hay usuarios en este rango de días.</td></tr>');
            } else {
                $(".no-results").text('No hay usuarios en este rango de días.').show();
            }
        } else {
            $(".no-results").hide();
        }
    });

  });
  </script>
</body>
</html>