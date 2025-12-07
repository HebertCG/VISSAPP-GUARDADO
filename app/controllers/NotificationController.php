<?php
// Comprobación estricta 
declare(strict_types=1);

namespace App\Controllers;

// Importamos el servicio de la logica de notificaciones.
use App\Services\NotificationService;

// Definición 
class NotificationController
{
    
    //Define la acción 
    public function index(): void
    {
        //Evita que el navegador guarde en caché la página.
       
        header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        //  incluye la vista
        require __DIR__ . '/../../views/dashboard/notifications.php';
    }

    
    
    public function list(): void
    {
        // La respuesta es en formato JSON.
        header('Content-Type: application/json');

        // Crea una instancia y obtiene todas las notificaciones , usamos listAll() para obtener todas las notificaciones.
            
        $notifications = (new NotificationService())->listAll();

        // convierte el array de notificaciones en una cadena JSON y la enviamos al cliente.
        echo json_encode($notifications);
    }
}
