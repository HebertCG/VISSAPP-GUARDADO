<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Database;
use PDO;

class NotificationService
{
    /**
     * Inserta un registro en la tabla de notificaciones
     *
     * @param int    $personaId ID de la persona afectada
     * @param string $field     Nombre del campo modificado
     * @param string $oldValue  Valor anterior
     * @param string $newValue  Valor nuevo
     */
    public function logChange(int $personaId, string $field, string $oldValue, string $newValue): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO notifications
                (persona_id, field, old_value, new_value, changed_at)
            VALUES
                (:pid, :fld, :old, :new, NOW())
        ");
        $stmt->execute([
            ':pid' => $personaId,
            ':fld' => $field,
            ':old' => $oldValue,
            ':new' => $newValue,
        ]);
    }
    /**
     * Devuelve todas las notificaciones de NOMBRE, CORREO o TELÉFONO, ordenadas por fecha descendente
     *
     * @return array<int,array<string,mixed>>
     */
    public function listAll(): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
        SELECT
            id,
            persona_id,
            field,
            old_value,
            new_value,
            changed_at
        FROM notifications
        WHERE field IN ('nombre','correo','telefono')
        ORDER BY changed_at DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
