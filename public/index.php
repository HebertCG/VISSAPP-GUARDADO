<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$root       = dirname(__DIR__);
$dotenvPath = $root . '/.env';

$dotenv = Dotenv\Dotenv::createMutable($root);
$dotenv->load();

foreach ($_ENV as $name => $value) {
    putenv("$name=$value");
}

if (isset($_GET['env_test'])) {
    header('Content-Type: text/plain');
    echo "Working dir: " . getcwd() . "\n";
    echo "Buscando .env en: $dotenvPath\n";
    echo "¿Existe .env? " . (file_exists($dotenvPath) ? 'SÍ' : 'NO') . "\n\n";
    $parsed = parse_ini_file($dotenvPath, false, INI_SCANNER_RAW);
    echo "--- parse_ini_file(): ---\n";
    var_export($parsed);
    echo "\n\n";
    echo "--- \$_ENV: ---\n";
    var_export($_ENV);
    echo "\n\n";
    echo "--- getenv(): ---\n";
    echo "MAILGUN_API_KEY = [" . getenv('MAILGUN_API_KEY') . "]\n";
    echo "MAILGUN_DOMAIN  = [" . getenv('MAILGUN_DOMAIN') . "]\n";
    echo "TWILIO_SID      = [" . getenv('TWILIO_SID') . "]\n";
    echo "TWILIO_TOKEN    = [" . getenv('TWILIO_TOKEN') . "]\n";
    echo "TWILIO_FROM     = [" . getenv('TWILIO_FROM') . "]\n";
    exit;
}

session_start();

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PersonaController;
use App\Controllers\NotificationController;
use App\Controllers\UserController;
use App\Services\NotificationService;

$method = $_SERVER['REQUEST_METHOD'];
$route  = $_GET['route'] ?? '';

$auth         = new AuthController();
$dash         = new DashboardController();
$persona      = new PersonaController();
$notification = new NotificationController();
$user         = new UserController();

if ($method === 'GET' && $route === 'error') {
    require __DIR__ . '/../views/error/error.php';
    exit;
}


if ($method === 'GET' && $route === '') {
    $auth->showLogin();
    exit;
}
if ($method === 'POST' && $route === 'login') {
    $auth->login();
    exit;
}
if ($method === 'GET' && $route === 'logout') {
    $auth->logout();
}

if ($method === 'GET' && $route === 'dashboard') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.0 404 Not Found');
        header('Location: index.php?route=error');
        exit;
    }
    $dash->index();
    exit;
}

if ($method === 'GET' && $route === 'persona_add') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.0 404 Not Found');
        header('Location: index.php?route=error');
        exit;
    }
    $persona->add();
    exit;
}
if ($method === 'GET' && $route === 'persona_edit') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.0 404 Not Found');
        header('Location: index.php?route=error');
        exit;
    }
    $persona->edit();
    exit;
}
if ($method === 'GET' && $route === 'personas') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
    exit;
}
if ($method === 'POST' && $route === 'persona_save') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
    $persona->save();
    exit;
}
if ($method === 'POST' && $route === 'persona_delete') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
    $persona->delete();
    exit;
}

if ($method === 'GET' && $route === 'lista_usuarios') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.0 404 Not Found');
        header('Location: index.php?route=error');
        exit;
    }
    $persona->listView();
    exit;
}
if ($method === 'GET' && $route === 'notifications') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.0 404 Not Found');
        header('Location: index.php?route=error');
        exit;
    }
    $notification->index();
    exit;
}
if ($method === 'GET' && $route === 'notifications_json') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
    $svc = new NotificationService();
    header('Content-Type: application/json');
    echo json_encode($svc->listAll());
    exit;
}

if ($method === 'POST' && $route === 'send_sms') {
    header('Content-Type: application/json');
    try {
        $persona->sendSms(
            (int) ($_POST['id']   ?? 0),
            $_POST['tel']         ?? '',
            (int) ($_POST['dias'] ?? 0),
            $_POST['name']        ?? ''
        );
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
if ($method === 'POST' && $route === 'send_email') {
    header('Content-Type: application/json');
    try {
        $persona->sendEmail(
            (int) ($_POST['id']    ?? 0),
            $_POST['mail']         ?? '',
            (int) ($_POST['dias']  ?? 0),
            $_POST['name']         ?? ''
        );
        echo json_encode(['success' => true]);
    } catch (\Exception $e) {
        echo json_encode([
            'success' => false,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
    }
    exit;
}

if ($method === 'GET' && $route === 'verificar_cuentas') {
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'soporte'])) {
        header('Location: index.php?route=error');
        exit;
    }
    $user->verificarCuentas();
    exit;
}


if ($_GET['route'] === 'cambiar_usuario') {
    (new \App\Controllers\UserController())->cambiarUsuario();
    exit;
}

if ($method === 'POST' && $route === 'cambiar_contrasena') {
    $user->cambiarContrasena();
    exit;
}
if ($method === 'POST' && $route === 'eliminar_cuenta') {
    $user->eliminarCuenta();
    exit;
}

if ($method === 'POST' && $route === 'cambiar_rol') {
    $rolSesion = $_SESSION['rol'] ?? '';
    $usuarioSesion = $_SESSION['user'] ?? '';

    // Permitir si el usuario es 'soporte' o el rol de sesión es 'soporte'
    if ($rolSesion !== 'soporte' && $usuarioSesion !== 'soporte') {
        echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
        exit;
    }

    $user->cambiarRol();
    exit;
}


if ($method === 'POST' && $route === 'guardar_usuario') {
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'soporte'])) {
        echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
        exit;
    }

    $user->guardarUsuario(); 
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'preview_visa_pdf') {
    $persona->previewVisaPdf();
        exit;
}

if ($method === 'GET' && $route === 'soporte') {
    if (empty($_SESSION['user'])) {
        header('HTTP/1.0 404 Not Found');
        header('Location: index.php?route=error');
        exit;
    }
    $dash->soporte();
    exit;
}

if ($method === 'GET' && $route === 'add_usuario') {
    $user->showAddUsuarioForm();
    exit;
}



header('HTTP/1.0 404 Not Found');
header('Location: index.php?route=error');
exit;
