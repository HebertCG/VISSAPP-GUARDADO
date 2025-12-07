$(function(){
    // Filtrado manual de la tabla
    const $input = $('#searchInput'),
          $btn   = $('#searchBtn'),
          $tbody = $('#tablaPersonas tbody');

    function filtrarTabla() {
        const term = $input.val().trim().toLowerCase();
        let visibleCount = 0;
        $tbody.find('tr').each(function(){
            const $tr = $(this),
                  nombre     = $tr.find('td').eq(1).text().toLowerCase(),
                  apellido   = $tr.find('td').eq(2).text().toLowerCase(),
                  numeroVisa = $tr.find('td').eq(8).text().toLowerCase();

            const match = nombre.includes(term) || apellido.includes(term) || numeroVisa.includes(term);
            $tr.toggle(match);
            if (match) visibleCount++;
        });
        $tbody.find('tr.no-results').remove(); // Elimina la fila "no-results" si ya existe
        if (visibleCount === 0) {
            const colspan = $('#tablaPersonas thead th').length;
            $tbody.append(`<tr class="no-results"><td colspan="${colspan}">No hay usuarios para mostrar</td></tr>`);
        }
    }

    $input.on('keyup', filtrarTabla);
    $btn.on('click', filtrarTabla);

    // Filtrado por tarjetas (Alertas)
    $('.filter-card').on('click', function() {
        const $this = $(this);
        const minDays = parseInt($this.data('min'));
        const maxDays = parseInt($this.data('max'));

        // Remueve la clase 'active' de todas las tarjetas y la añade a la seleccionada
        $('.filter-card').removeClass('active');
        $this.addClass('active');

        let visibleCount = 0;
        $tbody.find('tr').each(function() {
            const $tr = $(this);
            // Asegúrate de obtener los días restantes del span con la clase .badge
            const daysRemainingText = $tr.find('td').eq(11).find('.badge').text();
            const daysRemaining = parseInt(daysRemainingText);

            let match = false;
            if (!isNaN(minDays) && !isNaN(maxDays)) {
                // Rango de días (para Rojo, Amarillo, Verde)
                match = (daysRemaining >= minDays && daysRemaining <= maxDays);
            } else {
                // Tarjeta "Total Visas" (muestra todo)
                match = true;
            }

            $tr.toggle(match);
            if (match) visibleCount++;
        });

        $tbody.find('tr.no-results').remove();
        if (visibleCount === 0) {
            const colspan = $('#tablaPersonas thead th').length;
            $tbody.append(`<tr class="no-results"><td colspan="${colspan}">No hay usuarios para mostrar en este filtro.</td></tr>`);
        }
    });

    // Inicializa la tabla para mostrar todos los usuarios al cargar la página
    // O puedes activar una tarjeta por defecto, ej. Alerta Roja
    $('.filter-card[data-min="0"][data-max="999"]').click(); // Activa "Total Visas" por defecto al cargar


    // Editar usuario
    $('#tablaPersonas').on('click', '.btnEditar', function(){
        const id = $(this).closest('tr').find('td').eq(0).text().trim();
        // Asumiendo que index.php?route=persona_edit maneja la edición
        window.location.href = `index.php?route=persona_edit&id=${id}`;
    });

    // Borrar usuario con SweetAlert2
    $('#tablaPersonas').on('click', '.btnBorrar', function(){
        const $tr = $(this).closest('tr'),
              id  = $tr.find('td').eq(0).text().trim();

        Swal.fire({
            title: `¿Eliminar usuario #${id}?`,
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true // Pone el botón de confirmación a la derecha
        }).then(result => {
            if (!result.isConfirmed) return;

            // Simula la llamada AJAX a tu backend (index.php?route=persona_delete)
            // En un entorno real, descomentarías la llamada $.post
            // y manejarías la respuesta real del servidor.

            // $.post('index.php?route=persona_delete', { id: id }, function(resp){
            //     if (resp.success) {
            //         Swal.fire('Eliminado', `Usuario #${id} eliminado.`, 'success');
            //         $tr.fadeOut(200, ()=> $tr.remove());
            //         // Opcional: Volver a filtrar la tabla o actualizar los contadores de alerta
            //     } else {
            //         Swal.fire('Error', resp.error || 'No se pudo eliminar el usuario.', 'error');
            //     }
            // }, 'json').fail(() => {
            //     Swal.fire('Error', 'La petición falló. Intenta de nuevo.', 'error');
            // });

            // --- Código de simulación para pruebas (BORRAR EN PRODUCCIÓN) ---
            setTimeout(() => {
                const simulatedSuccess = true; // Cambia a false para probar el error
                if (simulatedSuccess) {
                    Swal.fire('Eliminado', `Usuario #${id} eliminado simuladamente.`, 'success');
                    $tr.fadeOut(200, ()=> $tr.remove());
                    filtrarTabla(); // Vuelve a filtrar después de eliminar
                } else {
                    Swal.fire('Error', 'Error simulado al eliminar.', 'error');
                }
            }, 500); // Pequeño retardo para simular la red
            // --- Fin del código de simulación ---

        });
    });

    // Notificaciones AJAX (AJUSTADO PARA EL NUEVO HTML)
    // Simula la llamada AJAX a tu backend (index.php?route=notifications_json)
    // En un entorno real, descomentarías el $.getJSON
    // y manejarías la respuesta real del servidor.

    // $.getJSON('index.php?route=notifications_json', function(data){
    //     const $dd = $('#notifDropdown').empty();
    //     $('#notifCount').text(data.length);
    //     if (!data.length) {
    //         $dd.append('<p class="text-center text-muted small m-0 py-2">Sin notificaciones</p>');
    //     } else {
    //         data.forEach(n => {
    //             // Ajusta 'n.field', 'n.old_value', 'n.new_value' si tu JSON tiene otra estructura
    //             // Y añade lógica para el icono si lo necesitas (e.g., n.type para determinar el color)
    //             $dd.append(`
    //                 <a class="dropdown-item notif-item" href="#">
    //                     <div class="notif-icon ${n.type || 'blue'}">
    //                         <i class="fas fa-info"></i>
    //                     </div>
    //                     <div>
    //                         <div class="notif-date">${n.changed_at}</div>
    //                         <div class="notif-text">${n.field.replace('_',' ')}: de "${n.old_value}" a "${n.new_value}"</div>
    //                     </div>
    //                 </a>
    //             `);
    //         });
    //     }
    // });

    // --- Código de simulación de notificaciones para pruebas (BORRAR EN PRODUCCIÓN) ---
    setTimeout(() => {
        const simulatedNotifications = [
            { changed_at: "Hace 5 min", field: "estado_visa", old_value: "Pendiente", new_value: "Aprobado", type: "green" },
            { changed_at: "Ayer", field: "telefono", old_value: "12345", new_value: "98765", type: "yellow" },
            { changed_at: "Hace 3 días", field: "fecha_final", old_value: "2024-12-31", new_value: "2025-01-15", type: "red" }
        ];
        const $dd = $('#notifDropdown').empty();
        $('#notifCount').text(simulatedNotifications.length);
        if (!simulatedNotifications.length) {
            $dd.append('<p class="text-center text-muted small m-0 py-2">Sin notificaciones</p>');
        } else {
            simulatedNotifications.forEach(n => {
                const iconClass = n.type === 'red' ? 'fa-exclamation-triangle' :
                                  n.type === 'yellow' ? 'fa-bell' :
                                  n.type === 'green' ? 'fa-check' : 'fa-info';
                $dd.append(`
                    <a class="dropdown-item notif-item" href="#">
                        <div class="notif-icon ${n.type || 'blue'}">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div>
                            <div class="notif-date">${n.changed_at}</div>
                            <div class="notif-text">${n.field.replace(/_/g,' ')}: de "${n.old_value}" a "${n.new_value}"</div>
                        </div>
                    </a>
                `);
            });
        }
    }, 100);
    // --- Fin del código de simulación ---


});