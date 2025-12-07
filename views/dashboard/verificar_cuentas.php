<?php

declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$user = $_SESSION['user'] ?? 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VisApp · Verificar Cuentas</title>
  <link rel="stylesheet" href="/VissApp_v3/public/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="/VissApp_v3/public/css/styleverify.css">
  <link rel="stylesheet" href="/VissApp_v3/public/css/styletopbar.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .main-content { margin-left: 220px; padding: 1rem; }
    .sidebar {
      position: fixed; top: 0; left: 0; bottom: 0; width: 240px;
      background-color: #007bff; color: white; z-index: 1000;
    }
    .sidebar .nav-link { color: white; padding-left: 1rem; }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active { background-color: rgba(255,255,255,0.1); }
    .table-responsive {
      background: white; padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <nav class="sidebar text-white p-4 vh-100">
    <h4><i class="fas fa-tachometer-alt"></i> VissApp</h4>
    <hr class="border-light">
    <small class="text-white-50">Usuarios</small>
    <ul class="nav flex-column mb-4">
      <li class="nav-item mb-1"><a href="index.php?route=dashboard" class="nav-link text-white pl-0"><i class="fas fa-user-plus"></i> Ingresar Usuario</a></li>
      <li class="nav-item"><a href="index.php?route=lista_usuarios" class="nav-link text-white pl-0"><i class="fas fa-users"></i> Lista Usuarios</a></li>
      <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'soporte'])): ?>
        <li class="nav-item"><a href="index.php?route=verificar_cuentas" class="nav-link text-white pl-0"><i class="fas fa-check-circle"></i> Verificar Cuentas</a></li>
      <?php endif; ?>
    </ul>
    <small class="text-white-50">Soporte</small>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="index.php?route=soporte" class="nav-link text-white pl-0"><i class="fas fa-life-ring"></i> Soporte</a></li>
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
          <img class="avatar-sm" src="/VissApp_v3/public/img/user.png" alt="avatar">
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
  <?php if (isset($_SESSION['user']) && $_SESSION['user'] === 'soporte'): ?>
    <div class="d-flex justify-content-start mb-3" style="padding-left: 1rem;">
      <a href="index.php?route=add_usuario" class="btn btn-success">
        <i class="fas fa-plus mr-2"></i> Añadir Cuenta
      </a>
    </div>
  <?php endif; ?>


    <div class="table-card">
      <div class="table-responsive">
        <table id="tablaCuentas" class="table table-bordered table-striped text-center bg-white">
          <thead class="thead-white">
            <tr><th>Id</th><th>Nombre de Usuario</th><th>Rol</th><th>Acciones</th></tr>
          </thead>
          <tbody>
            <?php foreach ($cuentas as $cuenta): ?>
              <?php
              $esPropiaCuenta = $cuenta['id'] == ($_SESSION['id'] ?? -1);
              $nombreCuenta = $cuenta['nombre_usuario'] ?? '';
              $rolCuentaDB = $cuenta['rol'] ?? 'usuario';
              $rolCuenta = $nombreCuenta === 'admin' ? 'admin' : $rolCuentaDB;
              $usuarioActual = $_SESSION['user'] ?? '';
              $rolActual = $_SESSION['rol'] ?? 'usuario';
              $esSoporte = $usuarioActual === 'soporte';
              $esCuentaSoporte = $nombreCuenta === 'soporte';
              $esCuentaUsuario = $rolCuenta === 'usuario';
              $badgeClass = $rolCuenta === 'admin' ? 'badge-admin' : ($rolCuenta === 'usuario' ? 'badge-usuario' : 'badge-invalido');
              ?>
              <tr>
                <td><?= (int)$cuenta['id'] ?></td>
                <td class="author-cell" data-id="<?= $cuenta['id'] ?>">
                  <img class="avatar" src="https://ui-avatars.com/api/?name=<?= urlencode($nombreCuenta) ?>&background=random&size=64" alt="avatar" onerror="this.src='/VissApp_v3/public/img/default.png'">
                  <div>
                    <p class="name mb-0"><?= htmlspecialchars($nombreCuenta) ?></p>
                    <p class="sub mb-0 text-capitalize"><?= htmlspecialchars($rolCuenta) ?></p>
                  </div>
                </td>
                <td>
                  <?php if ($esSoporte && !$esCuentaSoporte && !$esPropiaCuenta): ?>
                    <select class="form-control form-control-sm select-rol" data-id="<?= $cuenta['id'] ?>">
                      <option value="usuario" <?= $rolCuenta === 'usuario' ? 'selected' : '' ?>>usuario</option>
                      <option value="admin" <?= $rolCuenta === 'admin' ? 'selected' : '' ?>>administrador</option>
                    </select>
                  <?php elseif ($esCuentaSoporte): ?>
                    <span class="badge badge-warning">Indefinido</span>
                  <?php else: ?>
                    <span class="badge <?= $badgeClass ?> text-uppercase"> <?= $rolCuenta === 'admin' ? 'Administrador' : 'Usuario' ?> </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($esSoporte && !$esPropiaCuenta): ?>
                    <button class="btn btn-info btn-sm btnEditar" data-id="<?= $cuenta['id'] ?>">👤 Cambiar Usuario</button>
                    <button class="btn btn-warning btn-sm btnCambiarContrasena" data-id="<?= $cuenta['id'] ?>">🔑 Cambiar Contraseña</button>
                    <button class="btn btn-danger btn-sm btnEliminarCuenta" data-id="<?= $cuenta['id'] ?>">🗑 Eliminar Cuenta</button>
                  <?php elseif ($rolActual === 'admin' && $esCuentaUsuario): ?>
                    <button class="btn btn-info btn-sm btnEditar" data-id="<?= $cuenta['id'] ?>">👤 Cambiar Usuario</button>
                    <button class="btn btn-warning btn-sm btnCambiarContrasena" data-id="<?= $cuenta['id'] ?>">🔑 Cambiar Contraseña</button>
                    <button class="btn btn-danger btn-sm btnEliminarCuenta" data-id="<?= $cuenta['id'] ?>">🗑 Eliminar Cuenta</button>
                  <?php else: ?>
                    <span class="badge badge-dark">NO DISPONIBLE</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <footer class="bg-white py-3 text-center">
      <span>Copyright © VisApp 2025</span>
    </footer>
  </div>

  <script src="/VissApp_v3/public/vendor/jquery/jquery-3.7.1.min.js"></script>
  <script src="/VissApp_v3/public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    $(function() {
      $('#searchInput').on('keyup', function() {
        const term = this.value.toLowerCase();
        $('#tablaCuentas tbody tr').each(function() {
          const nombre = $(this).find('td').eq(1).text().toLowerCase();
          $(this).toggle(nombre.includes(term));
        });
      });

      $(document).on('click', '.btnEditar', function() {
        const id = $(this).data('id');
        Swal.fire({
          title: 'Cambiar Usuario', input: 'text',
          inputLabel: 'Nuevo nombre de usuario',
          inputPlaceholder: 'Ejemplo: juan.perez',
          showCancelButton: true,
          confirmButtonText: 'Guardar',
          cancelButtonText: 'Cancelar',
          preConfirm: (nuevoUsuario) => {
            if (!nuevoUsuario || nuevoUsuario.trim().length < 3) {
              Swal.showValidationMessage('Debe tener al menos 3 caracteres');
              return false;
            }
            return $.post('index.php?route=cambiar_usuario', {
              id: id, nuevo_usuario: nuevoUsuario.trim()
            }, null, 'json').then(r => {
              if (!r.success) throw new Error(r.error || 'Error al actualizar');
              return r;
            }).catch(() => {
              Swal.showValidationMessage('Usuario ya existente');
              return false;
            });
          }
        }).then((r) => {
          if (r.isConfirmed && r.value) {
            Swal.fire({ icon: 'success', title: 'Usuario actualizado', text: `Nuevo nombre: ${r.value.nuevo_usuario}`, timer: 2000, showConfirmButton: false });
            $(`td.author-cell[data-id="${id}"] .name`).text(r.value.nuevo_usuario);
          }
        });
      });

         $('.btnCambiarContrasena').on('click', function() {
  const id = $(this).data('id');

  Swal.fire({
  title: 'Cambiar Contraseña',
  html: `
    <p style="font-size: 14px; margin-bottom: 10px;">
      La nueva contraseña debe contener al menos:
      <ul style="text-align: left; font-size: 13px;">
        <li>10 caracteres</li>
        <li>Una letra mayúscula</li>
        <li>Una letra minúscula</li>
        <li>Un número</li>
        <li>Un carácter especial (@, #, $, %, &, !...)</li>
      </ul>
    </p>
    <div style="position: relative;">
      <input id="swal-input1" class="swal2-input" type="password" placeholder="Ingresa la nueva contraseña" />
      <button id="togglePass" type="button"
        style="
          position: absolute;
          right: 10px;
          top: 12px;
          background: white;
          border: 2px solid #999;
          border-radius: 6px;
          padding: 2px 6px;
          font-size: 18px;
          cursor: pointer;
        ">
        👁️
      </button>
    </div>
  `,
  showCancelButton: true,
  confirmButtonText: 'Guardar',
  cancelButtonText: 'Cancelar',
  didOpen: () => {
    const toggleBtn = document.getElementById('togglePass');
    const input = document.getElementById('swal-input1');

    toggleBtn.addEventListener('click', () => {
      if (input.type === 'password') {
        input.type = 'text';
        toggleBtn.textContent = '🙈';
      } else {
        input.type = 'password';
        toggleBtn.textContent = '👁️';
      }
    });
  },
  preConfirm: () => {
    const password = document.getElementById('swal-input1').value;
    const esValida = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/.test(password);
    if (!esValida) {
      Swal.showValidationMessage('La contraseña no cumple con los requisitos de seguridad');
      return false;
    }
    return password;
  }
  }).then((result) => {
    if (result.isConfirmed && result.value) {
      const nueva = result.value;

      $.post('index.php?route=cambiar_contrasena', {
        id: id,
        nueva_contrasena: nueva
      }, function(response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Contraseña actualizada',
            text: 'Se guardó correctamente la nueva contraseña',
            timer: 2500,
            showConfirmButton: false
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.error || 'No se pudo actualizar la contraseña'
          });
        }
      }, 'json');
    }
  });
});


      $('.btnEliminarCuenta').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
          title: '¿Estás seguro?', icon: 'warning', text: 'Eliminará la cuenta permanentemente.',
          showCancelButton: true, confirmButtonText: 'Sí, eliminar'
        }).then((r) => {
          if (r.isConfirmed) {
            $.post('index.php?route=eliminar_cuenta', { id }, function(res) {
              Swal.fire({ icon: res.success ? 'success' : 'error', title: res.success ? 'Eliminado' : 'Error', text: res.error || '', timer: 2500, showConfirmButton: false }).then(() => location.reload());
            }, 'json');
          }
        });
      });

      $('.select-rol').on('change', function() {
        const id = $(this).data('id');
        const nuevoRol = $(this).val();
        $.post('index.php?route=cambiar_rol', {
          id: id,
          nuevo_rol: nuevoRol
        }, function(response) {
          if (response.success) {
            Swal.fire({ icon: 'success', title: 'Rol actualizado', timer: 2500,showConfirmButton: false }).then(() => location.reload());
            
          } else {
            Swal.fire({ icon: 'error', title: 'Error', text: response.error || 'No se pudo cambiar el rol' });
          }
        }, 'json');
      });
    });
  </script>
</body>
</html>
