// public/js/main.js

$(document).ready(function(){
  console.log(">> main.js cargado y listo");

  // Inicializa DataTable con AJAX
  const tablaPersonas = $("#tablaPersonas").DataTable({
    ajax: {
      url: 'index.php?route=personas',
      dataSrc: ''
    },
    columns: [
      { data: 'id' },
      { data: 'nombre' },
      { data: 'pais' },
      { data: 'correo' },
      { data: 'telefono' },
      { data: 'edad' },
      { data: 'numeroVisa' },
      { data: 'fechaInicio' },
      { data: 'fechaFinal' },
      {
        data: null,
        className: "text-center",
        render: function(rowData) {
          return `
            <button class="btn btn-sm btn-warning btnEditar" data-id="${rowData.id}">
              <i class="fas fa-edit"></i> Editar
            </button>
            <button class="btn btn-sm btn-danger btnBorrar" data-id="${rowData.id}">
              <i class="fas fa-trash-alt"></i> Borrar
            </button>
          `;
        }
      }
    ]
  });

  // Handler Editar
  $(document).on("click", ".btnEditar", function(){
    const id = $(this).data("id");
    console.log(`DEBUG: .btnEditar clickeado, id=${id}`);
    // si ves este log, la vinculación funciona
    window.location.href = `index.php?route=persona_edit&id=${id}`;
  });

  // Handler Borrar
  $(document).on("click", ".btnBorrar", function(){
    const id = $(this).data("id");
    console.log(`DEBUG: .btnBorrar clickeado, id=${id}`);
    if (confirm(`¿Eliminar registro ${id}?`)) {
      $.post('index.php?route=persona_delete', { id }, function(res){
        console.log("DEBUG: respuesta delete:", res);
        tablaPersonas.ajax.reload();
      }, 'json');
    }
  });
});


// ==== Notificaciones en la campana ====

$(function(){
  function loadBell(){
    $.getJSON('index.php?route=notifications_json', function(list){
      list = list.slice(0,5);
      $('#notifList').empty();
      if (!list.length) {
        $('#notifList').append(
          '<li class="list-group-item text-center text-muted">Sin novedades</li>'
        );
        $('#bellCount').hide();
      } else {
        list.forEach(n => {
          $('#notifList').append(`
            <li class="list-group-item small">
              <strong>ID ${n.persona_id}</strong>: <em>${n.field}</em><br>
              de “${n.old_value}” a “${n.new_value}”
              <div class="text-right">
                <small class="text-muted">${n.changed_at}</small>
              </div>
            </li>
          `);
        });
        $('#bellCount')
          .text(list.length)
          .show();
      }
    });
  }

  loadBell();

  $('#bellBtn').on('click', function(e){
    e.stopPropagation();
    $('#notifDropdown').toggle();
  });
  $(document).on('click', function(){
    $('#notifDropdown').hide();
  });
});
