<?php
// 1) Habilita comprobación estricta
declare(strict_types=1);

namespace App\Controllers;

use App\Services\PersonaService;

// Agrupa la lógica 
class DashboardController
{
    //  prepara y renderiza el dashboard
    public function index(): void
    {
        // lo usamos para desactivar el caché del navegador

        header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        
       //servicio para acceder a la BDS
        $service = new PersonaService();

        // llama a getAll.conecta a la BD y hace SELECT * FROM persona
        $data    = $service->getAll();

        // Creamos un objeto inmutable
        $hoy     = new \DateTimeImmutable();

        // Inicializamos contadores 
        $red    = 0;  
        $yellow = 0;  
        $green  = 0;  

        // Recorremos cada persona por referencia (&$persona) 
        foreach ($data as &$persona) {
            // Verificar que fechaFinal existe y no está vacío
            if (empty($persona['fechaFinal'])) {
                $persona['daysRemaining'] = 0;
                $red++;
                continue;
            }
            
            // creamos un DateTimeImmutable con la fecha final de la visa
            $fin = new \DateTimeImmutable($persona['fechaFinal']);
            // Calculamos la diferencia absoluta en días
            $diffDays = $fin->diff($hoy)->days;
            // Evitamos valores negativos
            $diasRestantes = max(0, $diffDays);
            // Añadimos al array el nuevo campo para usarlo en la vista:
            $persona['daysRemaining'] = $diasRestantes;

            // contador correspondiente según el rango
            if ($diasRestantes < 30) {
                $red++;
            } elseif ($diasRestantes < 60) {
                $yellow++;
            } elseif ($diasRestantes < 90) {
                $green++;
            }
        }
        // Eliminamos la referencia para evitar bucle
        unset($persona);

        // 
        // Contamos cuántas personas hay para mostrar
        $total = count($data);

        // Renderizado de la vista
        require __DIR__ . '/../../views/dashboard/index.php';
    }

    public function soporte(): void
    {
        // Desactivar el caché del navegador
        header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Renderizar la vista de soporte
        require __DIR__ . '/../../views/dashboard/soporte.php';
    }

}
