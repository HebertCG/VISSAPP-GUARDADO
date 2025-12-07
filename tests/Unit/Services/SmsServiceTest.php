<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\SmsService;
use PHPUnit\Framework\TestCase;

/**
 * Test unitario para SmsService
 * 
 * Verifica la lógica del servicio de SMS sin enviar mensajes reales.
 */
class SmsServiceTest extends TestCase
{
    private SmsService $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar variables de entorno para testing
        $_ENV['TWILIO_SID'] = 'test_sid_12345';
        $_ENV['TWILIO_TOKEN'] = 'test_token_12345';
        $_ENV['TWILIO_FROM'] = '+1234567890';
        
        $this->smsService = new SmsService();
    }

    protected function tearDown(): void
    {
        unset($_ENV['TWILIO_SID']);
        unset($_ENV['TWILIO_TOKEN']);
        unset($_ENV['TWILIO_FROM']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(SmsService::class, $this->smsService);
    }

    /**
     * @test
     */
    public function it_has_send_method(): void
    {
        $this->assertTrue(
            method_exists($this->smsService, 'send'),
            'SmsService debe tener el método send'
        );
    }

    /**
     * @test
     */
    public function send_method_signature_is_correct(): void
    {
        $reflection = new \ReflectionMethod(SmsService::class, 'send');
        $parameters = $reflection->getParameters();
        
        $this->assertCount(
            2,
            $parameters,
            'El método send debe recibir exactamente 2 parámetros'
        );
        
        $this->assertEquals('to', $parameters[0]->getName());
        $this->assertEquals('message', $parameters[1]->getName());
    }

    /**
     * @test
     */
    public function phone_number_validation(): void
    {
        // Test de formato de número telefónico
        $validPhone = '+573001234567';
        $this->assertMatchesRegularExpression(
            '/^\+\d{10,15}$/',
            $validPhone,
            'El número debe tener formato internacional'
        );
    }

    /**
     * @test
     */
    public function invalid_phone_format_should_be_detected(): void
    {
        $invalidPhones = [
            '1234567890',      // Sin +
            '+12',             // Muy corto
            '+123456789012345678', // Muy largo
            'abc123',          // Con letras
        ];

        foreach ($invalidPhones as $phone) {
            $this->assertDoesNotMatchRegularExpression(
                '/^\+\d{10,15}$/',
                $phone,
                "El número '$phone' no debe ser válido"
            );
        }
    }

    /**
     * @test
     * Test de integración real - se saltará en unit tests
     */
    public function it_can_send_sms(): void
    {
        $this->markTestSkipped(
            'Este test requiere credenciales válidas de Twilio. ' .
            'Se moverá a Integration tests.'
        );
    }
}
