<?php
namespace App\Services;

/**
 * Plantillas HTML modernas para correos electrónicos.
 */
class EmailTemplates
{
    /**
     * Plantilla base con diseño moderno y responsive.
     * 
     * @param string $title Título del correo
     * @param string $content Contenido HTML del cuerpo
     * @return string HTML completo
     */
    public static function base(string $title, string $content): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:15px;font-family:Arial,sans-serif;background:#f4f7fa;">
<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#fff;border-radius:6px;">
<tr><td style="background:#007bff;padding:20px;text-align:center;"><h1 style="color:#fff;font-size:20px;margin:0;">📧 {$title}</h1></td></tr>
<tr><td style="padding:20px;">{$content}</td></tr>
<tr><td style="background:#f8f9fa;padding:15px;text-align:center;border-top:1px solid #e9ecef;font-size:12px;color:#6c757d;">
<strong>VissApp</strong> - Sistema de Gestión de Visas<br>© 2025 VissApp
</td></tr>
</table>
</body>
</html>
HTML;
    }
    
    /**
     * Plantilla para notificación de vencimiento de visa.
     * 
     * @param array $data Datos de la persona y visa
     * @return string HTML del correo
     */
    public static function visaExpiration(array $data): string
    {
        $nombre = $data['nombre'] ?? 'Usuario';
        $apellido = $data['apellido'] ?? '';
        $diasRestantes = $data['dias_restantes'] ?? 0;
        $fechaFinal = $data['fecha_final'] ?? 'N/A';
        $numeroVisa = $data['numero_visa'] ?? 'N/A';
        $tipoVisa = $data['tipo_visa'] ?? 'N/A';
        
        // Determinar el tipo de alerta según los días
        $alertBg = '#d1ecf1';
        $alertColor = '#17a2b8';
        $alertMessage = '';
        
        if ($diasRestantes <= 0) {
            $alertBg = '#f8d7da';
            $alertColor = '#dc3545';
            $alertMessage = '⚠️ ¡URGENTE! Tu visa ha vencido o vence hoy.';
        } elseif ($diasRestantes <= 7) {
            $alertBg = '#f8d7da';
            $alertColor = '#dc3545';
            $alertMessage = '⚠️ ¡Atención! Tu visa vence en menos de una semana.';
        } elseif ($diasRestantes <= 30) {
            $alertBg = '#fff3cd';
            $alertColor = '#ffc107';
            $alertMessage = '⚠️ Tu visa vencerá pronto. Toma acción.';
        } else {
            $alertBg = '#d1ecf1';
            $alertColor = '#17a2b8';
            $alertMessage = 'ℹ️ Esta es una notificación informativa sobre tu visa.';
        }
        
        $content = <<<HTML
<p style="margin:0 0 10px 0;font-size:16px;color:#333;"><strong>Hola {$nombre} {$apellido},</strong></p>
<p style="margin:0 0 15px 0;font-size:15px;color:#333;">Te quedan <strong style="color:#007bff;">{$diasRestantes} días</strong> para renovar tu visa.</p>
<table width="100%" cellpadding="10" cellspacing="0" style="background:{$alertBg};border-left:3px solid {$alertColor};margin:0 0 15px 0;">
<tr><td style="font-size:13px;color:#333;">{$alertMessage}</td></tr>
</table>
<table width="100%" cellpadding="8" cellspacing="0" style="background:#f8f9fa;margin:0 0 10px 0;font-size:14px;">
<tr><td style="border-bottom:1px solid #e9ecef;"><strong>Visa:</strong> {$numeroVisa}</td></tr>
<tr><td style="border-bottom:1px solid #e9ecef;"><strong>Tipo:</strong> {$tipoVisa}</td></tr>
<tr><td style="border-bottom:1px solid #e9ecef;"><strong>Vence:</strong> {$fechaFinal}</td></tr>
<tr><td><strong>Días:</strong> {$diasRestantes}</td></tr>
</table>
<p style="margin:0;font-size:13px;color:#666;">Renueva antes del vencimiento.</p>
HTML;
        
        return self::base('Notificación de Vencimiento de Visa', $content);
    }
    
    /**
     * Plantilla genérica para mensajes simples.
     * 
     * @param string $title Título del mensaje
     * @param string $message Mensaje principal
     * @param string $type Tipo: success, info, warning, danger
     * @return string HTML del correo
     */
    public static function simple(string $title, string $message, string $type = 'info'): string
    {
        $icon = [
            'success' => '✅',
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'danger' => '🚨'
        ][$type] ?? 'ℹ️';
        
        $content = <<<HTML
<p style="margin:0;font-size:16px;color:#333;">{$icon} {$message}</p>
HTML;
        
        return self::base($title, $content);
    }
}
