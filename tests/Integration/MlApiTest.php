<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\MlService;
use PHPUnit\Framework\TestCase;

/**
 * Test de integración para MlService
 * 
 * Verifica la integración con la API de Machine Learning.
 * NOTA: Requiere que la API de ML esté corriendo en http://localhost:8001
 */
class MlApiTest extends TestCase
{
    private MlService $mlService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar URL de la API para testing
        putenv('ML_API_URL=http://localhost:8001');
        putenv('ML_API_TIMEOUT=10');
        
        $this->mlService = new MlService();
    }

    protected function tearDown(): void
    {
        putenv('ML_API_URL');
        putenv('ML_API_TIMEOUT');
        parent::tearDown();
    }

    /**
     * @test
     */
    public function ml_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(
            MlService::class,
            $this->mlService,
            'MlService debe poder instanciarse'
        );
    }

    /**
     * @test
     */
    public function has_required_methods(): void
    {
        $requiredMethods = [
            'isAvailable',
            'predictRisk',
            'predictRiskBatch',
            'getRiskStatistics'
        ];

        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                method_exists($this->mlService, $method),
                "MlService debe tener el método {$method}"
            );
        }
    }

    /**
     * @test
     */
    public function can_check_api_availability(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la API de ML esté corriendo. ' .
            'Ejecutar: cd ml && python app.py'
        );

        $isAvailable = $this->mlService->isAvailable();
        
        $this->assertIsBool(
            $isAvailable,
            'isAvailable() debe retornar un booleano'
        );
    }

    /**
     * @test
     */
    public function can_predict_risk_for_persona(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la API de ML esté corriendo y ' .
            'que exista una persona en la base de datos.'
        );

        // Asumiendo que existe una persona con ID 1
        $prediction = $this->mlService->predictRisk(1);

        $this->assertIsArray($prediction);
        $this->assertArrayHasKey('success', $prediction);
        
        if ($prediction['success']) {
            $this->assertArrayHasKey('riesgo', $prediction);
            $this->assertArrayHasKey('probabilidades', $prediction);
            $this->assertArrayHasKey('recomendacion', $prediction);
            
            $this->assertContains(
                $prediction['riesgo'],
                ['alto_riesgo', 'medio_riesgo', 'bajo_riesgo']
            );
        }
    }

    /**
     * @test
     */
    public function handles_invalid_persona_id_gracefully(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la API de ML esté corriendo.'
        );

        $prediction = $this->mlService->predictRisk(999999);

        $this->assertIsArray($prediction);
        $this->assertArrayHasKey('success', $prediction);
        $this->assertFalse($prediction['success']);
        $this->assertArrayHasKey('error', $prediction);
    }

    /**
     * @test
     */
    public function can_predict_batch(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la API de ML esté corriendo y ' .
            'que existan personas en la base de datos.'
        );

        $personaIds = [1, 2, 3];
        $predictions = $this->mlService->predictRiskBatch($personaIds);

        $this->assertIsArray($predictions);
        $this->assertCount(3, $predictions);

        foreach ($predictions as $prediction) {
            $this->assertIsArray($prediction);
            $this->assertArrayHasKey('success', $prediction);
        }
    }

    /**
     * @test
     */
    public function can_get_risk_statistics(): void
    {
        $this->markTestSkipped(
            'Este test requiere que la API de ML esté corriendo y ' .
            'que existan personas en la base de datos.'
        );

        $stats = $this->mlService->getRiskStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('alto_riesgo', $stats);
        $this->assertArrayHasKey('medio_riesgo', $stats);
        $this->assertArrayHasKey('bajo_riesgo', $stats);

        $this->assertIsInt($stats['total']);
        $this->assertGreaterThanOrEqual(0, $stats['total']);
    }
}
