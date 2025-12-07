/* -------------  public/js/notificaciones.js  ------------- */
(function (window, $) {
  'use strict';

  /* Si jQuery no existe, abortamos con un aviso en consola */
  if (typeof $ === 'undefined') {
    console.error('jQuery no está disponible – notificaciones desactivadas');
    return;
  }

  $(function () {

    /* ---------- Construye el bloque HTML de cada aviso ---------- */
    function buildItem (n) {

      /* color según el campo modificado */
      let colorCls = 'notif-red';
      switch (n.field) {
        case 'telefono': colorCls = 'notif-yellow'; break;
        case 'correo'  : colorCls = 'notif-blue';   break;
        case 'nombre'  : colorCls = 'notif-green';  break;
      }

      return `
        <div class="notif-item d-flex align-items-start py-2 px-3">
          <div class="notif-icon ${colorCls} mr-2 flex-shrink-0">
            <i class="fas fa-exclamation"></i>
          </div>
          <div class="flex-grow-1 small">
            <div class="notif-date text-muted mb-1">${n.changed_at}</div>
            <div class="notif-text">
              <strong>ID ${n.persona_id}</strong> – <em>${n.field}</em>:
              “${n.old_value}” → “${n.new_value}”
            </div>
          </div>
        </div>`;
    }

    /* ----------- Carga/actualiza la lista de notificaciones ----------- */
    function loadNotifications () {
      $.getJSON('index.php?route=notifications_json')
        .done(function (data) {

          /* --- contador en la campana --- */
          const $counter = $('#notifCount');
          if (Array.isArray(data) && data.length) {
            $counter.text(data.length).show();
          } else {
            $counter.hide();
          }

          /* --- lista dentro del dropdown --- */
          const $box = $('#notifDropdown').empty();
          if (!data || !data.length) {
            $box.append(
              '<p class="text-center text-muted small py-3 m-0">Sin notificaciones</p>'
            );
            return;
          }

          /* máximo 8 resultados */
          data.slice(0, 8).forEach(n => $box.append(buildItem(n)));
        })
        .fail(function (_, textStatus) {
          console.error('Error cargando notificaciones:', textStatus);
        });
    }

    /* primera carga + (opcional) refresco cada minuto */
    loadNotifications();
    // setInterval(loadNotifications, 60000);

    /* mostrar / ocultar dropdown (y cerrarlo al clicar fuera) */
    $('#notifMenu').on('click', function (e) {
      e.stopPropagation();
      $('.notif-dropdown').toggle();
    });
    $(document).on('click', () => $('.notif-dropdown').hide());
  });


})(window, window.jQuery);


