<?php
namespace App\Services;

use Mailgun\Mailgun;

class EmailService
{
    /**
     * Cliente de Mailgun.
     *
     * @var \Mailgun\Mailgun
     */
    private $mg;

    /**
     * Dominio de Mailgun (sandbox o propio).
     *
     * @var string
     */
    private $domain;


    public function __construct()
    {
        // Creamos el cliente de Mailgun con la API Key obtenida de getenv()
        $this->mg     = Mailgun::create(getenv('MAILGUN_API_KEY'));

        // Guardamos el dominio (sandbox o propio) desde getenv()
        $this->domain = getenv('MAILGUN_DOMAIN');
    }

    /**
     * Envía un email HTML sencillo.
     *
     * @param string $to      
     * @param string $subject 
     * @param string $body    
     *
     * @return void
     */
    public function send(string $to, string $subject, string $body): void
    {
        // Construimos y enviamos el mensaje a través de la API de Mailgun:
        $this->mg
            ->messages()               // Subservicio de "mensajes"
            ->send(                    // Método para enviar
                $this->domain,         // Dominio desde el que enviamos
                [
                    // Remitente mejorado para evitar SPAM
                    'from'    => sprintf(
                                    'VissApp Notificaciones <no-reply@%s>',
                                    $this->domain
                                ),

                    // Destinatario del mensaje
                    'to'      => $to,

                    // Asunto del correo
                    'subject' => $subject,

                    // Cuerpo del mensaje en HTML
                    'html'    => $body,
                    
                    // Headers adicionales para evitar SPAM
                    'h:X-Mailgun-Tag' => 'visa-notification',
                    'h:Reply-To' => 'soporte@vissapp.com',
                    'h:X-Priority' => '1',
                    'h:Importance' => 'high',
                    
                    // Texto plano alternativo (mejora deliverability)
                    'text'    => strip_tags($body)
                ]
            );
    }
}
