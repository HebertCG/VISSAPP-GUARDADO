<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación de rol
$rol = $_SESSION['rol'] ?? 'usuario';
if (!in_array($rol, ['admin', 'soporte'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Obtener datos JSON
$data = json_decode(file_get_contents("php://input"), true);

// DEBUG opcional (verifica si llegan los datos)
file_put_contents(__DIR__ . '/../debug.log', print_r($data, true), FILE_APPEND);

// Validación de formato
if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Ruta del archivo .txt que guarda el contenido editable
$contenidoPath = __DIR__ . '/../views/dashboard/contenido/contenido_completo.txt';

// Asegurar que el directorio exista
$dir = dirname($contenidoPath);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Armar las líneas para el archivo
$lineas = [];
foreach ($data as $item) {
    $clave = trim($item['seccion']);
    $valor = trim($item['contenido']);

    // Reemplazar saltos de línea por espacios si no deseas multilínea
    $valor = preg_replace('/\r\n|\r|\n/', ' ', $valor);

    $lineas[] = $clave . ':::' . $valor;
}

// Guardar en el archivo
$resultado = file_put_contents($contenidoPath, implode(PHP_EOL, $lineas));

if ($resultado !== false) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar el archivo.']);
}
