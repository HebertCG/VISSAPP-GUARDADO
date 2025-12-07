$(function() {
  $('#formLogin').on('submit', function(e) {
    e.preventDefault();
    
    const usuario  = $('#usuario').val().trim();
    const password = $('#password').val().trim();

    $.ajax({
      url: 'index.php?route=login',
      method: 'POST',
      dataType: 'json',
      data: { usuario, password },
      success(res) {
        if (res.success) {
          window.location.href = 'index.php?route=dashboard';
        } else {
          alert('Credenciales inválidas, intenta nuevamente.');
        }
      },
      error(xhr, status, error) {
        console.error('Error en login AJAX:', error);
      }
    });
  });
});
