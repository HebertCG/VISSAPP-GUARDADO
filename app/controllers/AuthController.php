<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Models\Database;
use PDO;

class AuthController
{
    // Muestra el formulario de login
    public function showLogin(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        require __DIR__ . '/../../views/auth/login.php';
    }

    // Procesa el login
    public function login(): void
    {
        $user = $_POST['usuario'] ?? '';
        $pass = $_POST['password'] ?? '';

        $auth = new AuthService();
        $success = $auth->authenticate($user, $pass);

        header('Content-Type: application/json');

        if ($success) {
            // Conexión a la base de datos
            $db = Database::connect();
            $stmt = $db->prepare("SELECT id, rol FROM usuarios WHERE usuario = ?");
            $stmt->execute([$user]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Guardar en sesión
            $_SESSION['user'] = $user;
            $_SESSION['rol']  = $userData['rol'] ?? 'usuario'; // Por defecto 'usuario'
            $_SESSION['id']   = $userData['id']  ?? 0;

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    // Cierra la sesión
    public function logout(): void
    {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
