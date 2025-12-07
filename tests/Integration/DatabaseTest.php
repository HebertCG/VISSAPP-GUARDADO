<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Database;
use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Test de integración para Database
 * 
 * Verifica que la conexión a la base de datos funciona correctamente.
 * NOTA: Estos tests requieren una base de datos real configurada.
 */
class DatabaseTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Intentar conectar a la base de datos
        try {
            $this->db = Database::connect();
        } catch (\Exception $e) {
            $this->markTestSkipped(
                'No se pudo conectar a la base de datos: ' . $e->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function database_connection_is_successful(): void
    {
        $this->assertInstanceOf(
            PDO::class,
            $this->db,
            'Database::connect() debe retornar una instancia de PDO'
        );
    }

    /**
     * @test
     */
    public function database_connection_is_singleton(): void
    {
        $db1 = Database::connect();
        $db2 = Database::connect();
        
        $this->assertSame(
            $db1,
            $db2,
            'Database::connect() debe retornar siempre la misma instancia (Singleton)'
        );
    }

    /**
     * @test
     */
    public function can_execute_simple_query(): void
    {
        $result = $this->db->query('SELECT 1 as test');
        $row = $result->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(
            1,
            $row['test'],
            'Debe poder ejecutar queries simples'
        );
    }

    /**
     * @test
     */
    public function usuarios_table_exists(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'usuarios'");
        $result = $stmt->fetch();
        
        $this->assertNotFalse(
            $result,
            'La tabla usuarios debe existir en la base de datos'
        );
    }

    /**
     * @test
     */
    public function personas_table_exists(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'personas'");
        $result = $stmt->fetch();
        
        $this->assertNotFalse(
            $result,
            'La tabla personas debe existir en la base de datos'
        );
    }

    /**
     * @test
     */
    public function notifications_table_exists(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'notifications'");
        $result = $stmt->fetch();
        
        $this->assertNotFalse(
            $result,
            'La tabla notifications debe existir en la base de datos'
        );
    }

    /**
     * @test
     */
    public function usuarios_table_has_required_columns(): void
    {
        $stmt = $this->db->query("DESCRIBE usuarios");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'usuario', 'password', 'correo', 'rol'];
        
        foreach ($requiredColumns as $column) {
            $this->assertContains(
                $column,
                $columns,
                "La tabla usuarios debe tener la columna '$column'"
            );
        }
    }

    /**
     * @test
     */
    public function personas_table_has_required_columns(): void
    {
        $stmt = $this->db->query("DESCRIBE personas");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = [
            'id', 'nombre', 'apellido', 'pais', 'correo', 
            'telefono', 'edad', 'numeroVisa', 'tipoVisa', 
            'fechaInicio', 'fechaFinal'
        ];
        
        foreach ($requiredColumns as $column) {
            $this->assertContains(
                $column,
                $columns,
                "La tabla personas debe tener la columna '$column'"
            );
        }
    }

    /**
     * @test
     */
    public function can_use_prepared_statements(): void
    {
        $stmt = $this->db->prepare('SELECT ? as test');
        $stmt->execute([42]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals(
            42,
            $row['test'],
            'Debe poder usar prepared statements'
        );
    }
}
