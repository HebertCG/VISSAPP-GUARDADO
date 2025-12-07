/* ---------- public/js/lista_usuarios.js ---------- */
(function (window, $) {
  'use strict';
  if (typeof $ === 'undefined') return;

  /* ------------ referencias rápidas --------------- */
  const $tbody = $('#tablaLista tbody');
  const $cards = $('.filter-card');
  const COLS   = $('#tablaLista thead th').length;

  /* ============ BUSCADOR ========================== */
  $('#searchInput').on('keyup', function () {
    const term = this.value.toLowerCase().trim();

    $tbody.find('tr').not('.no-results').each(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(term) !== -1);
    });
    toggleNoResults();
  });

  /* ============ TARJETAS FILTRO =================== */
  $cards.on('click', function () {
    const $card = $(this);
    const active = $card.hasClass('active');

    $cards.removeClass('active');
    if (!active) $card.addClass('active');

    const min = +$card.data('min');
    const max = +$card.data('max');

    $tbody.find('tr').not('.no-results').each(function () {
      const dias = +$(this).data('dias');
      const show = active ? true : (dias >= min && dias <= max);
      $(this).toggle(show);
    });
    toggleNoResults();
  });

  /* ============ ENVÍO DE EMAIL ==================== */
  $tbody.on('click', '.btn-send-email', function () {
    const $tr   = $(this).closest('tr');
    const id    = $tr.data('id');
    const mail  = $tr.data('email');
    const dias  = $tr.data('dias');
    const name  = $tr.children().eq(1).text();

    Swal.fire({
      title: 'Enviando correo…',
      allowOutsideClick: false,
      didOpen () {
        Swal.showLoading();

        $.post('index.php?route=send_email',
               { id, mail, dias, name },
               res => {
                 Swal.close();
                 if (res.success) {
                   /* actualizar contador visual */
                   const sent = +$tr.data('sent') + 1;
                   $tr.data('sent', sent)
                      .find('.sent-count').text(sent);

                   Swal.fire({icon:'success', title:'Correo enviado', text:`Se envió un correo a ${mail}`, confirmButtonText:'OK'});
                 } else {
                   Swal.fire({icon:'error', title:'Error', text:res.error||'No se pudo enviar.'});
                 }
               },
               'json')
         .fail(() => {
           Swal.close();
           Swal.fire({icon:'error', title:'Error de conexión', text:'No se pudo contactar al servidor.'});
         });
      }
    });
  });

  /* ============ “Sin resultados” ================== */
  function toggleNoResults () {
    $tbody.find('.no-results').remove();
    if ($tbody.find('tr:visible').length) return;
    $tbody.append(
      `<tr class="no-results"><td colspan="${COLS}" class="py-4 text-muted">Sin resultados…</td></tr>`
    );
  }

})(window, window.jQuery);
