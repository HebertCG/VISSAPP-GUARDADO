<?php
namespace App\Models;

use PDO;
use PDOException;

class Database
{
    /**
     * Instancia singleton de PDO
     * @var PDO|null
     */
    private static $instance = null;

    /**
     * Conectar a la base de datos y devolver PDO
     * @return PDO
     */
    public static function connect(): PDO
    {
        if (self::$instance === null) {
            // Carga configuración
            $cfg = require __DIR__ . '/../../config/database.php';

            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $cfg['host'],
                $cfg['port'] ?? '5432',
                $cfg['dbname']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $cfg['username'],
                    $cfg['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                // En producción podrías loguear en vez de die()
                die('Error de conexión: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
