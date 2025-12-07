<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'soporte'])) {
    header('Location: index.php?route=error');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VisApp - Añadir Usuario</title>
  <link rel="stylesheet" href="/VissApp_v3/public/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-default/default.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="/VissApp_v3/public/vendor/jquery/jquery-3.7.1.min.js"></script>
  <style>
    body {
      background-color: #f0f2f5;
    }
    .card {
      max-width: 480px;
      margin: 4rem auto;
      border: none;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .card-header {
      background-color: #007bff;
      color: white;
      font-weight: bold;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
    }
    .card-header i {
      margin-right: 10px;
    }
    .btn-success {
      background-color: #28a745;
      border: none;
    }
    .btn-success:hover {
      background-color: #218838;
    }
    .password-wrapper {
      position: relative;
    }
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      border: 2px solid #999;
      border-radius: 6px;
      background-color: white;
      padding: 2px 6px;
      font-size: 16px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      <i class="fas fa-user-plus"></i> Añadir Nuevo Usuario
    </div>
    <div class="card-body bg-light">
      <form id="formNuevoUsuario" method="POST">
        <div class="form-group">
          <label for="usuario"><i class="fas fa-user"></i> Nombre de Usuario</label>
          <input type="text" name="usuario" id="usuario" class="form-control" required autocomplete="off">
        </div>

        <div class="form-group">
          <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
          <div class="password-wrapper">
            <input type="password" name="password" id="password" class="form-control" required autocomplete="new-password">
            <span id="togglePassword" class="toggle-password">👁️</span>
          </div>
          <small class="form-text text-muted mt-2">
            La contraseña debe contener al menos:
            <ul style="font-size: 13px;">
              <li>10 caracteres</li>
              <li>Una letra mayúscula</li>
              <li>Una letra minúscula</li>
              <li>Un número</li>
              <li>Un carácter especial (@, #, $, %, &, !...)</li>
            </ul>
          </small>
        </div>

        <div class="form-group">
          <label for="rol"><i class="fas fa-users-cog"></i> Rol</label>
          <select name="rol" id="rol" class="form-control" required>
            <option value="usuario">Usuario</option>
            <option value="admin">Administrador</option>
          </select>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-check-circle"></i> Guardar Usuario
          </button>
          <a href="index.php?route=verificar_cuentas" class="btn btn-secondary">
            <i class="fas fa-times-circle"></i> Cancelar
          </a>
        </div>
      </form>
    </div>
  </div>

  

  <script>

    function validarPassword(password) {
  const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/;
  return regex.test(password);
}

    // Mostrar/ocultar contraseña
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordInput = document.getElementById('password');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.textContent = '🙈';
      } else {
        passwordInput.type = 'password';
        this.textContent = '👁️';
      }
    });

    // Validar y enviar con AJAX
    $('#formNuevoUsuario').on('submit', function (e) {
  e.preventDefault();
  const datos = $(this).serialize();
  const password = $('#password').val();

  // Validación personalizada
  if (!validarPassword(password)) {
    Swal.fire({
      icon: 'warning',
      title: 'Contraseña inválida',
      html: `
        Asegúrate de que la contraseña contenga:<br><br>
        <ul style="text-align:left; font-size:14px;">
          <li>✅ Al menos 10 caracteres</li>
          <li>✅ Una letra mayúscula</li>
          <li>✅ Una letra minúscula</li>
          <li>✅ Un número</li>
          <li>✅ Un carácter especial (@, #, $, %, &, ...)</li>
        </ul>
      `,
      confirmButtonText: 'Entendido'
    });
    return;
  }

  // Si pasa la validación, envía el formulario por AJAX
  $.post('index.php?route=guardar_usuario', datos, function (respuesta) {
    if (respuesta.success) {
      Swal.fire({
        icon: 'success',
        title: 'Usuario creado',
        text: `Nuevo usuario: ${respuesta.usuario}`,
        confirmButtonText: 'Aceptar'
      }).then(() => {
        window.location.href = 'index.php?route=verificar_cuentas';
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: respuesta.error || 'No se pudo registrar el usuario'
      });
    }
  }, 'json').fail(() => {
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo conectar al servidor'
    });
  });
});

  </script>
</body>
</html>
