

<?php
// config/sendgrid.php
//arreglo de configuracion
return [
  'api_key'    => getenv('SENDGRID_API_KEY')    ?: 'TU_SENDGRID_API_KEY',
  'from_email' => getenv('SENDGRID_FROM_EMAIL') ?: 'no-reply@tudominio.com',
  'from_name'  => getenv('SENDGRID_FROM_NAME')  ?: 'VissApp',
];
