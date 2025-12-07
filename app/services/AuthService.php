<?php

namespace App\Services;


use App\Models\Database;
use PDO;


class AuthService
{
    /**
     * Verifica usuario y contraseña y guarda datos en sesión.
     *
     * @param string $user  Nombre de usuario ingresado
     * @param string $pass  Contraseña ingresada
     * @return bool         Retorna true si las credenciales son válidas, false si no lo son
     */
    public function authenticate(string $user, string $pass): bool
    {
      
        $db = Database::connect();

        $stmt = $db->prepare(
            'SELECT id, usuario, rol FROM usuarios WHERE usuario = :u AND password = :p'
        );

       
        $stmt->execute([
            ':u' => $user,
            ':p' => md5($pass)
        ]);

        // Obtiene los datos del usuario si la consulta fue exitosa
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si se encontró un usuario válido...
        if ($userData) {
            // Guarda el nombre del usuario en la sesión para futuras validaciones de acceso
            $_SESSION['user'] = $userData['usuario'];
            // También se podría guardar el rol si deseas usarlo en todas las vistas:
            // $_SESSION['rol'] = $userData['rol'];

            return true;
        }

        return false;
    }
}