<?php
// config/twilio.php
return [
  'sid'   => getenv('TWILIO_SID')   ?: 'TU_TWILIO_ACCOUNT_SID',
  'token' => getenv('TWILIO_TOKEN') ?: 'TU_TWILIO_AUTH_TOKEN',
  'from'  => getenv('TWILIO_FROM')  ?: '+1234567890',  // tu número Twilio en formato E.164
];
