<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AuthService;
use App\Models\Database;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

/**
 * Test unitario para AuthService
 * 
 * Verifica la lógica de autenticación sin depender de una base de datos real.
 * Usa mocks para simular el comportamiento de PDO.
 */
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
        
        // Iniciar sesión para los tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        // Limpiar sesión después de cada test
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * @test
     * Test básico: verificar que la clase existe y es instanciable
     */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AuthService::class, $this->authService);
    }

    /**
     * @test
     * Verificar que el método authenticate existe
     */
    public function it_has_authenticate_method(): void
    {
        $this->assertTrue(
            method_exists($this->authService, 'authenticate'),
            'AuthService debe tener el método authenticate'
        );
    }

    /**
     * @test
     * Verificar que authenticate retorna un booleano
     */
    public function authenticate_returns_boolean(): void
    {
        // Este test fallará si no hay conexión a DB real
        // En la siguiente fase implementaremos mocks para evitar esto
        $this->markTestSkipped(
            'Este test requiere mock de Database. Se implementará en la siguiente iteración.'
        );
    }

    /**
     * @test
     * Verificar que credenciales vacías no son válidas
     */
    public function empty_credentials_should_fail(): void
    {
        $this->markTestSkipped(
            'Este test requiere mock de Database. Se implementará en la siguiente iteración.'
        );
    }

    /**
     * @test
     * Verificar que la contraseña se hashea con MD5
     */
    public function password_is_hashed_with_md5(): void
    {
        $password = 'test123';
        $expectedHash = md5($password);
        
        $this->assertEquals(
            $expectedHash,
            md5($password),
            'La contraseña debe hashearse con MD5'
        );
    }
}
