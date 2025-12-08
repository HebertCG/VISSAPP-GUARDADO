<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegúrate de que $user y $rol estén definidos
$user = $_SESSION['user'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 'usuario'; // Asumimos que el rol por defecto es 'usuario'

$contenido = [];
$contenidoPath = __DIR__ . '/contenido/contenido_completo.txt';

if (file_exists($contenidoPath)) {
    $lineas = file($contenidoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $partes = explode(':::', $linea, 2);
        if (count($partes) === 2) {
            $contenido[$partes[0]] = $partes[1];
        }
    }
}
 else {
    $contenido['introduccion'] = 'Bienvenido al Centro de Soporte de VisApp!';
}


// Asegúrate de que $contenido['introduccion'] tenga un valor predeterminado si no está definido
$introduccion = $contenido['introduccion'] ?? 'Bienvenido al Centro de Soporte de VisApp!';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VisApp - Soporte</title>
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Tu CSS -->
    <link rel="stylesheet" href="css/stylebd.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 240px;
            overflow-y: auto;
            background-color: #007bff;
            z-index: 1000;
        }
        .main-content {
            margin-left: 240px;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .navbar {
            overflow: visible !important;
            z-index: 1100;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px 10px 0 0;
        }
        .accordion .card-header {
            padding: 0;
        }
        .accordion .btn-link {
            text-decoration: none;
            padding: 15px;
            display: block;
            text-align: left;
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        .accordion .btn-link:hover, .accordion .btn-link:focus {
            background-color: #e9ecef;
        }
        .highlight {
            background-color: #fffde7;
            padding: 15px;
            border-left: 4px solid #ffd600;
            margin-bottom: 20px;
        }
        .icon-box {
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
        }
        .icon-box i {
            font-size: 40px;
            color: #007bff;
            margin-bottom: 15px;
        }
        .header-content {
            text-align: center;
            margin: 20px 0;
        }
        .header-content h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .header-content p {
            font-size: 1.2rem;
            color: #6c757d;
        }
        #editBtn {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1200;
        }
        [contenteditable="true"] {
            border: 1px dashed orange;
            padding: 5px;
        }
    </style>
</head>
<body class="d-flex">
    <!-- Botón de edición -->
    <?php if (in_array($rol, ['admin', 'soporte'])): ?>
        <button id="editBtn" type="button" class="btn btn-warning">
  <i class="fas fa-edit"></i> Editar Contenido
</button>

    <?php endif; ?>

    <!-- Sidebar -->
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
             <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['admin', 'soporte'])): ?>
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4">
            <ul class="navbar-nav d-flex align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" data-toggle="dropdown">
                        <span class="mr-2 text-gray-600"><?= htmlspecialchars($user) ?></span>
                        <img src="img/user.png" class="rounded-circle" width="32">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="index.php?route=logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Contenido de Soporte -->
        <div class="container-fluid mt-3 px-4">
            <div class="header-content">
                <h1 id="titulo" class="no-editar" contenteditable="false">Centro de Soporte</h1>
                <p id="introduccion" contenteditable="false"><?= htmlspecialchars($introduccion) ?></p>
            </div>

            <div class="highlight">
                <h5 id="ayuda-inmediata" class="no-editar" contenteditable="false">¿Necesitas ayuda inmediata?</h5>
                <p id="ayuda-texto" contenteditable="false"><?= htmlspecialchars($contenido['ayuda-texto'] ?? 'Si no encuentras lo que buscas, no dudes en contactar a nuestro equipo de soporte técnico.') ?></p>
                <a href="https://chat.whatsapp.com/BzungD1qrA64gBgLhfy4Ev" target="_blank" class="btn btn-success">
  <i class="fab fa-whatsapp"></i> Contactar Soporte
</a>

            </div>

            <div class="card">
                <div class="card-body">
                    <h5 id="info-general" class="no-editar" contenteditable="false" style="text-align: center;">Información General</h5>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="icon-box">
                                <i class="fas fa-question-circle"></i>
                                <h5 id="que-es-visa" contenteditable="false">¿Qué es una visa?</h5>
                                <p id="que-es-visa-texto" contenteditable="false"><?= htmlspecialchars($contenido['que-es-visa-texto'] ?? 'Una visa es un documento que permite la entrada a un país por un período específico y para un propósito particular.') ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="icon-box">
                                <i class="fas fa-file-alt"></i>
                                <h5 id="como-solicitar" contenteditable="false">¿Cómo solicitar una visa?</h5>
                                <p id="como-solicitar-texto" contenteditable="false"><?= htmlspecialchars($contenido['como-solicitar-texto'] ?? 'Puedes solicitar una visa a través de nuestra plataforma. Navega a "Ingresar Usuario" y sigue las instrucciones.') ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="icon-box">
                                <i class="fas fa-clock"></i>
                                <h5 id="cuanto-tiempo" contenteditable="false">¿Cuánto tiempo tarda?</h5>
                                <p id="cuanto-tiempo-texto" contenteditable="false"><?= htmlspecialchars($contenido['cuanto-tiempo-texto'] ?? 'El tiempo de procesamiento varía, pero generalmente toma entre 5 a 10 días hábiles.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preguntas Frecuentes -->
            <div class="card">
                <div class="card-body">
                   <h5 id="preguntas-frecuentes" class="no-editar" contenteditable="false" style="text-align: center;">Preguntas Frecuentes</h5>
                    <div class="accordion" id="faqAccordion">
                        <div class="card">
                            <div class="card-header" id="faqOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <i class="fas fa-search"></i> <span id="faq1-pregunta" contenteditable="false"><?= htmlspecialchars($contenido['faq1-pregunta'] ?? '¿Cómo puedo verificar el estado de mi visa?') ?></span>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="faqOne" data-parent="#faqAccordion">
                                <div class="card-body" id="faq1-respuesta" contenteditable="false">
                                    <?= htmlspecialchars($contenido['faq1-respuesta'] ?? 'Puedes verificar el estado de tu visa navegando a "Lista Usuarios" y buscando tu nombre en la lista. La tabla mostrará los días restantes de tu visa.') ?>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faqTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        <i class="fas fa-exclamation-triangle"></i> <span id="faq2-pregunta" contenteditable="false"><?= htmlspecialchars($contenido['faq2-pregunta'] ?? '¿Qué debo hacer si mi visa está a punto de expirar?') ?></span>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="faqTwo" data-parent="#faqAccordion">
                                <div class="card-body" id="faq2-respuesta" contenteditable="false">
                                    <?= htmlspecialchars($contenido['faq2-respuesta'] ?? 'Si tu visa está a punto de expirar, te recomendamos que contactes a las autoridades de inmigración para renovarla. También puedes usar nuestra plataforma para gestionar la renovación.') ?>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="faqThree">
                                <h2 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        <i class="fas fa-phone"></i> <span id="faq3-pregunta" contenteditable="false"><?= htmlspecialchars($contenido['faq3-pregunta'] ?? '¿Cómo puedo contactar al soporte técnico?') ?></span>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="faqThree" data-parent="#faqAccordion">
                                <div class="card-body" id="faq3-respuesta" contenteditable="false">
                                    <?= htmlspecialchars($contenido['faq3-respuesta'] ?? 'Puedes contactar al soporte técnico enviando un correo a soporte@visapp.com o llamando al +51 936811144.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sostenibilidad -->
            <div class="card">
                <div class="card-body">
                    <h5 id="sostenibilidad" class="no-editar" contenteditable="false" style="text-align: center;">Sostenibilidad</h5>

                    <div class="accordion" id="sustainabilityAccordion">
                        <div class="card">
                            <div class="card-header" id="sustainabilityOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseSustainabilityOne" aria-expanded="true" aria-controls="collapseSustainabilityOne">
                                        <i class="fas fa-leaf"></i> <span id="sostenibilidad-pregunta" contenteditable="false"><?= htmlspecialchars($contenido['sostenibilidad-pregunta'] ?? '¿Qué es la sostenibilidad en el contexto de visas?') ?></span>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseSustainabilityOne" class="collapse show" aria-labelledby="sustainabilityOne" data-parent="#sustainabilityAccordion">
                                <div class="card-body" id="sostenibilidad-respuesta" contenteditable="false">
                                    <?= htmlspecialchars($contenido['sostenibilidad-respuesta'] ?? 'La sostenibilidad en el contexto de visas se refiere a prácticas que aseguran que el proceso de solicitud y gestión de visas sea eficiente y respetuoso con el medio ambiente.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white py-3 text-center">
            <span id="footer" contenteditable="false"><?= htmlspecialchars($contenido['footer'] ?? 'Copyright © VisApp 2025') ?></span>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery-3.7.1.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('editBtn')?.addEventListener('click', function (e) {
  e.preventDefault();

  const editBtn = document.getElementById('editBtn');
  const isSaving = editBtn.innerHTML.includes('Guardar Contenido');

  // Solo elementos que NO tengan la clase no-editar
  const editableElements = Array.from(document.querySelectorAll('[contenteditable]')).filter(el => !el.classList.contains('no-editar'));

  if (isSaving) {
    const data = [];
    let camposVacios = [];

    editableElements.forEach(el => {
      const id = el.id;
      const contenido = el.textContent.trim(); // mantiene saltos de línea si el backend respeta \n
      if (contenido === '') {
        camposVacios.push(id);
      }
      data.push({ seccion: id, contenido });
    });

    if (camposVacios.length > 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos vacíos',
        html: 'Los siguientes campos están vacíos y no se pueden guardar:<br><code>' + camposVacios.join(', ') + '</code>',
      });
      return;
    }

    fetch('guardar_contenido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'Guardado correctamente',
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: res.error || 'No se pudo guardar el contenido.',
        });
      }
    })
    .catch(err => {
      console.error('Error:', err);
      Swal.fire({
        icon: 'error',
        title: 'Error inesperado',
        text: 'No se pudo guardar el contenido.',
      });
    });

    editableElements.forEach(el => {
      el.setAttribute('contenteditable', false);
      el.style.border = '';
      el.style.backgroundColor = '';
    });

    editBtn.innerHTML = '<i class="fas fa-edit"></i> Editar Contenido';
    editBtn.classList.remove('btn-success');
    editBtn.classList.add('btn-warning');

  } else {
    editableElements.forEach(el => {
      el.setAttribute('contenteditable', true);
      el.style.border = '1px dashed orange';
      el.style.backgroundColor = '#fffbe6';
    });

    editBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Contenido';
    editBtn.classList.remove('btn-warning');
    editBtn.classList.add('btn-success');
  }
});
</script>

</body>
</html>
