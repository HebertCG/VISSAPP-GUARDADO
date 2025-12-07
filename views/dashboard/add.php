<?php
/**
 * ------------------------------------------------------------------
 *  VISTA add.php  (Crear / Editar Persona)
 * ------------------------------------------------------------------
 *  - Si $data['id'] > 0 actúa en modo *edición*, de lo contrario
 *    muestra un formulario vacío (modo *crear*).
 *  - El front-end usa Bootstrap 4, jQuery y SweetAlert2.
 *  - El archivo se divide en 3 grandes bloques:
 *      1)  <head>   →  CSS y título dinámico.
 *      2)  <body>   →  Formulario + preview PDF.
 *      3)  <script> →  Lógica de OCR + validaciones + envío AJAX.
 * ------------------------------------------------------------------
 */
declare(strict_types=1);
$user = $_SESSION['user'] ?? '';   // por si la vista quiere mostrarlo
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">

  <!-- Título cambio dinánimo según si editamos o creamos ------------ -->
  <title><?= $data['id'] ? 'Editar' : 'Nueva' ?> Persona</title>

  <!-- HOJAS DE ESTILO (Bootstrap + SweetAlert2 + Animaciones) -------- -->
  <link rel="stylesheet" href="/VissApp_v3/public/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="/VissApp_v3/public/vendor/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Estilos puntuales de la vista --------------------------------- -->
  <style>
    body{background:#f0f2f5;}

    /* cabecera de cada card */
    .card-header{padding:.75rem 1.25rem;font-weight:600;}

    /* iframe del PDF */
    .pdf-preview{width:100%;height:500px;border:none;}

    /* input file */
    .file-input{max-width:100%;}

    /* inputs modo readonly: color gris y cursor bloqueado */
    .form-control[readonly]{background:#e9ecef!important;cursor:not-allowed;opacity:1;}
  </style>
</head>

<body>
<div class="container-fluid py-4">

  <!-- Encabezado principal ------------------------------------------ -->
  <h3 class="mb-4 text-secondary">
    <?= $data['id'] ? "Editar persona #{$data['id']}" : 'Nueva persona' ?>
  </h3>

  <div class="row gx-4">
    <!-- =============================================================
         1. FORMULARIO (col-7)
    ============================================================= -->
    <div class="col-lg-7 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <i class="fas fa-user-plus me-2"></i> Datos de la Persona
        </div>

        <div class="card-body">
          <!-- enctype=multipart -> enviamos PDF -->
          <form id="formPersona" enctype="multipart/form-data" method="post">
            <!-- Campo oculto con el id (0 si es nuevo) -->
            <input type="hidden" name="id" value="<?= htmlspecialchars((string)$data['id']) ?>">

            <!-- 1. Nombre / Apellido -------------------------------- -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input name="nombre" class="form-control"
                       value="<?= htmlspecialchars($data['nombre']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input name="apellido" class="form-control"
                       value="<?= htmlspecialchars($data['apellido'] ?? '') ?>" required>
              </div>
            </div>

            <!-- 2. País / Tipo visa (readonly: los rellena OCR) ------ -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">País</label>
                <input name="pais" id="pais" class="form-control"
                       value="<?= htmlspecialchars($data['pais']) ?>"
                       readonly required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tipo de Visa</label>
                <input name="tipoVisa" id="tipoVisa" class="form-control"
                       value="<?= htmlspecialchars($data['tipoVisa']) ?>"
                       readonly required>
              </div>
            </div>

            <!-- 3. Correo / Teléfono -------------------------------- -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control"
                       value="<?= htmlspecialchars($data['correo']) ?>"
                       pattern="^[A-Za-z0-9._%+-]+@(gmail\.com|outlook\.com|hotmail\.com)$"
                       title="Debe ser un correo válido de Gmail, Outlook o Hotmail"
                       required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input name="telefono" class="form-control"
                       value="<?= htmlspecialchars($data['telefono']) ?>" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="referenciaTransaccion">Referencia de Transacción</label>
                <input type="text" class="form-control" name="referenciaTransaccion" id="referenciaTransaccion" value="<?= htmlspecialchars($data['referenciaTransaccion']) ?>" readonly>
              </div>
               <div class="form-group col-md-6">
               <!-- Campo vacío para mantener alineación -->
             </div>
            </div>

            <!-- 4. Edad / Nº Visa / Fechas --------------------------- -->
            <div class="row mb-4">
              <div class="col-md-3">
                <label class="form-label">Edad</label>
                <input type="number" name="edad" class="form-control"
                       value="<?= htmlspecialchars((string)$data['edad']) ?>"
                       min="15" max="80" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Número Visa</label>
                <input name="numeroVisa" id="numeroVisa" class="form-control"
                       value="<?= htmlspecialchars($data['numeroVisa']) ?>"
                       readonly required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fechaInicio" id="fechaInicio" class="form-control"
                       value="<?= htmlspecialchars($data['fechaInicio']) ?>"
                       readonly required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Fecha Final</label>
                <input type="date" name="fechaFinal" id="fechaFinal" class="form-control"
                       value="<?= htmlspecialchars($data['fechaFinal']) ?>"
                       readonly required>
              </div>
            </div>

            <!-- 5. Input file para PDF (acepta solo application/pdf) -->
            <div class="mb-4">
              <label class="form-label">Documento de Visa (PDF)</label>
              <input type="file" name="visaPdf" id="visaPdf"
                     class="form-control file-input" accept="application/pdf">
            </div>

            <!-- 6. Botones ------------------------------------------ -->
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <?= $data['id'] ? 'Guardar cambios' : 'Crear persona' ?>
              </button>
              <a href="index.php?route=dashboard" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- =============================================================
         2. PREVIEW PDF (col-5)
    ============================================================= -->
    <div class="col-lg-5 mb-4">
      <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
          <i class="fas fa-eye me-2"></i> Vista previa del PDF
        </div>
        <div class="card-body p-0">
          <iframe id="pdfPreview" class="pdf-preview"></iframe> <!-- src se inyecta vía JS -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===============================================================
     3.  SCRIPTS (jQuery + SweetAlert2 + lógica OCR/AJAX)
================================================================ -->
<script src="/VissApp_v3/public/vendor/jquery/jquery-3.7.1.min.js"></script>
<script src="/VissApp_v3/public/vendor/sweetalert2/sweetalert2.all.min.js"></script>
<script>
/* ---------------------------------------------------------------
   FLAGS Y UTILIDADES 
---------------------------------------------------------------- */
const isEdit = <?= $data['id'] ? 'true' : 'false' ?>; // true si estamos editando

// Devuelve true si la fecha ISO está en el pasado
function fechaExpirada(iso) {
  const hoy = new Date(); hoy.setHours(0,0,0,0);
  const f   = new Date(iso);
  return !isNaN(f) && f < hoy;
}

/* ---------------------------------------------------------------
   EVENTO  #visaPdf.change          (Sube PDF → extrae datos)
---------------------------------------------------------------- */
$('#visaPdf').on('change', function () {
  const file = this.files[0];
  if (!file) return;

  // 1) Muestra preview en el <iframe>
  $('#pdfPreview').attr('src', URL.createObjectURL(file));

  // 2) Envía archivo por AJAX a preview_visa_pdf
  const fd = new FormData(); fd.append('visaPdf', file);
  $.ajax({
    url : 'index.php?route=preview_visa_pdf',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json'
  }).done(res => {
    // 3) Rellena campos si los obtuvo
    if (res.numeroVisa)  $('#numeroVisa').val(res.numeroVisa);
    if (res.fechaInicio) $('#fechaInicio').val(res.fechaInicio);
    if (res.fechaFinal)  $('#fechaFinal').val(res.fechaFinal);

    // Traduce “STUDENT visa” → “ESTUDIANTIL” (map básico)
    if (res.visa) {
      const map = {STUDENT:'ESTUDIANTIL', WORK:'LABORAL'};
      const key = res.visa.split(' ')[0].toUpperCase();
      $('#tipoVisa').val(map[key] ?? res.visa);
    }

    // Capitaliza nombre del país
    if (res.passportCountry) {
      $('#pais').val(
        res.passportCountry.toLowerCase().replace(/\b\w/g,l=>l.toUpperCase())
      );
    }

    if (res.referenciaTransaccion) {
  $('#referenciaTransaccion').val(res.referenciaTransaccion); // ✅ ESTA LÍNEA
}

    // 4) Valida que la fecha final NO sea previa a hoy
    if (res.fechaFinal && fechaExpirada(res.fechaFinal)) {
      Swal.fire({
        icon : 'error',
        title: 'Fecha final vencida',
        text : 'La fecha final de la visa ya venció. Selecciona un archivo válido.'
      });
      // Limpia todo para forzar al usuario a intentar de nuevo
      $('#fechaFinal, #fechaInicio, #numeroVisa, #tipoVisa, #pais').val('');
      $('#pdfPreview').attr('src','');
      $('#visaPdf').val('');
      return;
    }

    // 5) Feedback visual ✔️
    const msg=[];
    if(res.numeroVisa)   msg.push('Número de visa');
    if(res.fechaInicio)  msg.push('Fecha de concesión');
    if(res.fechaFinal)   msg.push('Duración de estadía');
    if(res.visa)         msg.push('Tipo de visa');
    if(res.referenciaTransaccion) msg.push('Transaccion  de pago')
    if(res.passportCountry) msg.push('País');
    if(msg.length){
      Swal.fire({
        html : `<i class="fas fa-check-circle fa-4x animate__animated animate__bounceIn"></i>
                <h4>${msg.join(', ')} cargados</h4>`,
        timer: 1800, showConfirmButton:false,
        showClass:{popup:'animate__animated animate__zoomIn'},
        hideClass:{popup:'animate__animated animate__zoomOut'}
      });
    }
  }).fail(() => {
    Swal.fire({icon:'error', title:'Error', text:'No se pudo analizar el PDF.'});
  });
});

/* ---------------------------------------------------------------
   ENVÍO DEL FORMULARIO  (AJAX → persona_save)
---------------------------------------------------------------- */
$('#formPersona').on('submit', function (e) {
  // Validación bootstrap HTML5
  if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); return; }

  // Validación extra: fecha final no vencida
  const fechaFin = $('#fechaFinal').val();
  if (!fechaFin || fechaExpirada(fechaFin)) {
    e.preventDefault(); e.stopPropagation();
    Swal.fire({
      icon : 'error',
      title: 'Fecha final vencida o vacía',
      text : 'La fecha final de la visa debe ser hoy o posterior.'
    });
    return;
  }

  e.preventDefault();  // evita submit estándar

  // Empaqueta todo en FormData para incluir PDF
  const fd = new FormData(this);

  $.ajax({
    url : 'index.php?route=persona_save',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json'
  }).done(() => {
    // Éxito visual + redirección al Dashboard
    Swal.fire({
      html:`<i class="fas fa-check-circle fa-4x animate__animated animate__bounceIn"></i>
            <h3>${isEdit ? '¡Actualizada!' : '¡Creada!'}</h3>`,
      timer: 1500, showConfirmButton:false,
      showClass:{popup:'animate__animated animate__bounceIn'},
      hideClass:{popup:'animate__animated animate__bounceOut'}
    }).then(() =>
      location.href = 'index.php?route=dashboard&status=' +
                      (isEdit ? 'updated' : 'saved')
    );
  }).fail(xhr => {
    // Error devuelto por el backend
    const err = xhr.responseJSON?.error ?? 'No se pudo guardar la persona.';
    Swal.fire({icon:'error', title:'Error', text: err});
  });
});
</script>
</body>
</html>
