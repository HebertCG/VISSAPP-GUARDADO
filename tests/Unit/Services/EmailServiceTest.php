<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\EmailService;
use PHPUnit\Framework\TestCase;

/**
 * Test unitario para EmailService
 * 
 * Verifica la lógica del servicio de email sin enviar emails reales.
 */
class EmailServiceTest extends TestCase
{
    private EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar variables de entorno para testing
        $_ENV['MAILGUN_API_KEY'] = 'test_api_key_12345';
        $_ENV['MAILGUN_DOMAIN'] = 'sandbox.mailgun.org';
        
        $this->emailService = new EmailService();
    }

    protected function tearDown(): void
    {
        unset($_ENV['MAILGUN_API_KEY']);
        unset($_ENV['MAILGUN_DOMAIN']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(EmailService::class, $this->emailService);
    }

    /**
     * @test
     */
    public function it_has_send_method(): void
    {
        $this->assertTrue(
            method_exists($this->emailService, 'send'),
            'EmailService debe tener el método send'
        );
    }

    /**
     * @test
     */
    public function send_method_requires_three_parameters(): void
    {
        $reflection = new \ReflectionMethod(EmailService::class, 'send');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(
            3,
            $parameters,
            'El método send debe recibir exactamente 3 parámetros'
        );
        
        $this->assertEquals('to', $parameters[0]->getName());
        $this->assertEquals('subject', $parameters[1]->getName());
        $this->assertEquals('body', $parameters[2]->getName());
    }

    /**
     * @test
     */
    public function send_method_returns_void(): void
    {
        $reflection = new \ReflectionMethod(EmailService::class, 'send');
        $returnType = $reflection->getReturnType();
        
        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    /**
     * @test
     * Test de integración real - se saltará en unit tests
     */
    public function it_can_send_email(): void
    {
        $this->markTestSkipped(
            'Este test requiere credenciales válidas de Mailgun. ' .
            'Se moverá a Integration tests.'
        );
    }
}
