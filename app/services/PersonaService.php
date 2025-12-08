<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Database;
use PDO;

class PersonaService
{
    /**
     * Devuelve todas las personas (incluyendo apellido y tipoVisa)
     *
     * @return array<int,array<string,mixed>>
     */
    public function getAll(): array
    {
        $db   = Database::connect();
        $sql  = "
            SELECT 
                id,
                nombre,
                apellido,
                tipoVisa as \"tipoVisa\",
                pais,
                correo,
                telefono,
                edad,
                numeroVisa as \"numeroVisa\",
                referenciaTransaccion as \"referenciaTransaccion\",
                fechaInicio as \"fechaInicio\",
                fechaFinal as \"fechaFinal\"
            FROM personas
            ORDER BY id
        ";
        $stmt = $db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}