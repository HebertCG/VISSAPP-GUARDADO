<?php

declare(strict_types=1);

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * Test funcional/E2E para el flujo de login
 * 
 * Verifica el flujo completo de autenticación desde la perspectiva del usuario.
 * NOTA: Estos tests requieren que la aplicación esté corriendo.
 */
class LoginFlowTest extends TestCase
{
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = getenv('APP_URL') ?: 'http://localhost:8000';
    }

    /**
     * @test
     */
    public function login_page_is_accessible(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la aplicación esté corriendo. ' .
            'Se implementará con herramientas de E2E testing en fase posterior.'
        );
    }

    /**
     * @test
     */
    public function can_login_with_valid_credentials(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la aplicación esté corriendo. ' .
            'Se implementará con herramientas de E2E testing en fase posterior.'
        );
    }

    /**
     * @test
     */
    public function cannot_login_with_invalid_credentials(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la aplicación esté corriendo. ' .
            'Se implementará con herramientas de E2E testing en fase posterior.'
        );
    }

    /**
     * @test
     */
    public function redirects_to_dashboard_after_successful_login(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la aplicación esté corriendo. ' .
            'Se implementará con herramientas de E2E testing en fase posterior.'
        );
    }

    /**
     * @test
     */
    public function logout_clears_session(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la aplicación esté corriendo. ' .
            'Se implementará con herramientas de E2E testing en fase posterior.'
        );
    }

    /**
     * Helper method para simular login
     */
    private function login(string $username, string $password): array
    {
        // Esta función se implementará cuando tengamos un cliente HTTP
        // para hacer requests a la aplicación
        return [];
    }
}
