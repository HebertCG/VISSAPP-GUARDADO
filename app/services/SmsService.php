<?php
// app/Services/SmsService.php
namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    private Client $client;
    private string $from;

    public function __construct()
    {
        // Leemos las credenciales directamente desde $_ENV (cargado por phpdotenv)
        $sid   = $_ENV['TWILIO_SID']   ?? '';
        $token = $_ENV['TWILIO_TOKEN'] ?? '';
        $from  = $_ENV['TWILIO_FROM']  ?? '';

        if (! $sid || ! $token || ! $from) {
            throw new \RuntimeException(
                "Faltan credenciales de Twilio en \$_ENV: "
                . "SID=[".substr($sid,0,4)."…], "
                . "TOKEN=[".substr($token,0,4)."…], "
                . "FROM=[".$from."]"
            );
        }

        $this->client = new Client($sid, $token);
        $this->from   = $from;
    }

    /**
     * Envía un SMS.
     *
     * @param string $to      Número destino en E.164 (p.ej. "+51911222333")
     * @param string $message Texto del SMS
     */
    public function send(string $to, string $message): void
    {
        $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }
}
