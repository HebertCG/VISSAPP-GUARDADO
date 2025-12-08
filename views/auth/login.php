<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>VisApp · Iniciar Sesión</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/stylelogin.css">
</head>

<body>
  <div class="container-fluid">
    <div class="card card0 border-0">
      <div class="row d-flex">
        <div class="col-lg-6">
          <div class="card1">
            <div class="row justify-content-center">
              <!-- La ilustración está muy cerca del cuadro, sin scroll vertical -->
              <img src="img/login.png" class="image" alt="Ilustración">
            </div>
          </div>
        </div>
        <div class="col-lg-6 d-flex justify-content-center align-items-center">
          <!-- El contenedor derecho (card2) con fondo blanco puro y sombra reforzada -->
          <div class="card2 card border-0">
            <!-- Logo centrado -->
            <div class="row justify-content-center">
              <img src="img/letras.png" class="letras" alt="Logo">
            </div>

            <!-- “Sign in with” y botones -->
            <div class="row social-row px-3">
              <h6 class="mb-0 mr-3">Sign in with</h6>
              <div class="facebook">
                <div class="fa fa-facebook"></div>
              </div>
              <div class="twitter ml-2">
                <div class="fa fa-twitter"></div>
              </div>
              <div class="linkedin ml-2">
                <div class="fa fa-linkedin"></div>
              </div>
            </div>

            <!-- Formulario compacto -->
            <form id="formLogin" style="width: 100%;">
              <div class="row px-3 field-row">
                <label class="mb-1">
                  <h6 class="mb-0 text-sm">Usuario</h6>
                </label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
              </div>
              <div class="row px-3 field-row">
                <label class="mb-1">
                  <h6 class="mb-0 text-sm">Contraseña</h6>
                </label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
              </div>
              <div class="row px-3 checkbox-row">
                <div class="custom-control custom-checkbox custom-control-inline">
                  <input id="chk1" type="checkbox" name="chk" class="custom-control-input">
                  <label for="chk1" class="custom-control-label text-sm">Recordarme</label>
                </div>
                
              </div>
              <div class="row mb-3 px-3">
                <button type="submit" class="btn btn-blue text-center">Iniciar Sesión</button>
              </div>
            </form>

          </div>
        </div>
      </div>
      <div class="bg-blue py-4">
        <div class="row px-3">
          <small class="ml-4 ml-sm-5 mb-2">Copyright &copy; VisApp 2025</small>
          <div class="social-contact ml-4 ml-sm-auto">
            <span class="fa fa-facebook mr-4 text-sm"></span>
            <span class="fa fa-google-plus mr-4 text-sm"></span>
            <span class="fa fa-linkedin mr-4 text-sm"></span>
            <span class="fa fa-twitter mr-4 mr-sm-5 text-sm"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <script src="vendor/sweetalert2/sweetalert2.all.min.js"></script>
  <script>
    $(function() {
      $('#formLogin').on('submit', function(e) {
        e.preventDefault();
        const usuario = $('#usuario').val().trim();
        const password = $('#password').val().trim();

        $.ajax({
          url: 'index.php?route=login', // Mantienes la misma URL de tu login
          method: 'POST',
          dataType: 'json',
          data: {
            usuario,
            password
          },
          success(res) {
            if (res.success) {
              window.location.href = 'index.php?route=dashboard';
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Credenciales inválidas',
                text: 'Usuario o contraseña incorrectos. Intenta nuevamente.',
                confirmButtonText: 'Entendido',
                scrollbarPadding: false,
                backdrop: false
              });
            }
          },
          error() {
            Swal.fire({
              icon: 'error',
              title: 'Error de conexión',
              text: 'No se pudo conectar al servidor. Intenta más tarde.',
              confirmButtonText: 'Cerrar',
              scrollbarPadding: false,
              backdrop: false
            });
          }
        });
      });
    });
  </script>
</body>

</html>