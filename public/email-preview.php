<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - Email VissApp</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background-color: #e9ecef;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .preview-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <h1>📧 Preview del Nuevo Diseño de Email</h1>
        <div class="info">
            <strong>ℹ️ Nota:</strong> Este es un preview del correo que se enviará. El logo se cargará desde tu servidor cuando esté en producción.
        </div>
        
        <!-- AQUÍ VA EL EMAIL REAL -->
        <?php
        // Simular datos de ejemplo
        $data = [
            'nombre' => 'Gabriel Esteban',
            'apellido' => 'VARGAS MORENO',
            'dias_restantes' => 15,
            'fecha_final' => '2026-01-22',
            'numero_visa' => '2009503713509',
            'tipo_visa' => 'Student (subclass 500)'
        ];
        
        // Cargar la clase de plantillas
        require_once __DIR__ . '/../app/services/EmailTemplates.php';
        
        // Generar y mostrar el HTML
        echo \App\Services\EmailTemplates::visaExpiration($data);
        ?>
    </div>
</body>
</html>
