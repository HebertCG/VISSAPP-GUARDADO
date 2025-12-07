<?php
namespace App\Controllers;

use App\Models\Database;
use PDO;

class UserController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function verificarCuentas(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!in_array($_SESSION['rol'], ['admin', 'soporte'])) {
            header('Location: index.php?route=error');
            exit;
        }

        $stmt = $this->db->query("SELECT id, usuario AS nombre_usuario, rol FROM usuarios");
        $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hoy = new \DateTime();
        $red = $yellow = $green = 0;

        $stmtFechas = $this->db->query("SELECT fechaFinal FROM personas");
        $fechas = $stmtFechas->fetchAll(PDO::FETCH_COLUMN);

        foreach ($fechas as $fechaFinal) {
            if (!$fechaFinal) continue;
            $fin = new \DateTime($fechaFinal);
            $diasRestantes = (int)$hoy->diff($fin)->format('%r%a');

            if ($diasRestantes < 0) continue;
            if ($diasRestantes < 30) $red++;
            elseif ($diasRestantes < 60) $yellow++;
            elseif ($diasRestantes < 90) $green++;
        }

        $total = $red + $yellow + $green;

        require __DIR__ . '/../../views/dashboard/verificar_cuentas.php';
    }
public function cambiarRol(): void {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        return;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $nuevoRol = $_POST['nuevo_rol'] ?? '';

    if (!in_array($nuevoRol, ['admin', 'usuario'])) {
        echo json_encode(['success' => false, 'error' => 'Rol inválido']);
        return;
    }

    try {
        $db = \App\Models\Database::connect();
        $stmt = $db->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $stmt->execute([$nuevoRol, $id]);

        echo json_encode(['success' => true]);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
    }
}

    public function cambiarUsuario(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $nuevoUsuario = trim($_POST['nuevo_usuario'] ?? '');

            if ($id > 0 && $nuevoUsuario !== '') {
                $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :u AND id != :id");
                $stmtCheck->execute([':u' => $nuevoUsuario, ':id' => $id]);
                $existe = $stmtCheck->fetchColumn();

                if ($existe > 0) {
                    echo json_encode(["success" => false, "error" => "El nombre de usuario ya está en uso"]);
                    return;
                }

                $stmt = $this->db->prepare("UPDATE usuarios SET usuario = :u WHERE id = :id AND usuario != 'admin'");
                $stmt->execute([':u' => $nuevoUsuario, ':id' => $id]);

                echo json_encode(["success" => true, "nuevo_usuario" => $nuevoUsuario]);
                return;
            }
        }

        echo json_encode(["success" => false, "error" => "Datos inválidos"]);
    }

    public function cambiarContrasena(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $nueva = trim($_POST['nueva_contrasena'] ?? '');

            if (strlen($nueva) < 6) {
                echo json_encode(["success" => false, "error" => "La contraseña debe tener al menos 6 caracteres."]);
                return;
            }

            if ($id > 0 && $nueva !== '') {
                $stmt = $this->db->prepare("UPDATE usuarios SET password = :p WHERE id = :id");
                $stmt->execute([':p' => md5($nueva), ':id' => $id]);

                echo json_encode(["success" => true]);
                return;
            }
        }

        echo json_encode(["success" => false, "error" => "Datos inválidos"]);
    }

    public function eliminarCuenta(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);

            $stmt = $this->db->prepare("SELECT usuario FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetchColumn();

            if ($usuario && $usuario !== 'admin') {
                $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
                $stmt->execute([':id' => $id]);

                echo json_encode(["success" => true]);
                return;
            }
        }

        echo json_encode(["success" => false, "error" => "No se puede eliminar la cuenta o ID inválido"]);
    }

    public function showAddUsuarioForm(): void
    {
        require_once __DIR__ . '/../../views/dashboard/add_usuario.php';
    }

public function guardarUsuario(): void
{
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario  = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol      = $_POST['rol'] ?? 'usuario';

        if ($usuario !== '' && $password !== '') {
            $conn = \App\Models\Database::connect();

            // Verificar si el usuario ya existe
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
            $stmtCheck->execute([$usuario]);
            if ($stmtCheck->fetchColumn() > 0) {
                echo json_encode([
                    'success' => false,
                    'error' => 'El nombre de usuario ya existe.'
                ]);
                return;
            }

            // ✅ Aplica password_hash directamente
            $passwordHash = md5($password);


            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol) VALUES (?, ?, ?)");
            $stmt->execute([$usuario, $passwordHash, $rol]);

            echo json_encode([
                'success' => true,
                'usuario' => $usuario
            ]);
            return;
        }

        echo json_encode([
            'success' => false,
            'error' => 'Datos incompletos'
        ]);
        return;
    }

    echo json_encode([
        'success' => false,
        'error' => 'Método inválido'
    ]);
}


}