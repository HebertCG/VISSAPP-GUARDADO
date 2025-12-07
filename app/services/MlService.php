<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Servicio para integración con la API de Machine Learning
 * 
 * Permite realizar predicciones de riesgo de vencimiento de visas
 * consumiendo el microservicio Python/FastAPI.
 */
class MlService
{
    private Client $client;
    private string $apiUrl;
    private int $timeout;

    public function __construct()
    {
        // Obtener configuración desde variables de entorno
        $this->apiUrl = getenv('ML_API_URL') ?: 'http://localhost:8001';
        $this->timeout = (int) (getenv('ML_API_TIMEOUT') ?: 5);

        // Inicializar cliente HTTP
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'timeout'  => $this->timeout,
            'headers'  => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);
    }

    /**
     * Verifica si la API de ML está disponible.
     *
     * @return bool True si la API está disponible, false si no
     */
    public function isAvailable(): bool
    {
        try {
            $response = $this->client->get('/health');
            $data = json_decode($response->getBody()->getContents(), true);
            
            return $data['status'] === 'healthy' && $data['modelo_cargado'] === true;
        } catch (GuzzleException $e) {
            error_log("ML API no disponible: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Predice el riesgo de vencimiento para una persona.
     *
     * @param int $personaId ID de la persona en la base de datos
     * @return array|null Array con la predicción o null si hay error
     */
    public function predictRisk(int $personaId): ?array
    {
        try {
            // 1. Obtener datos de la persona desde la base de datos
            $personaData = $this->getPersonaData($personaId);
            
            if (!$personaData) {
                throw new \RuntimeException("Persona con ID {$personaId} no encontrada");
            }

            // 2. Preparar datos para la API
            $requestData = $this->prepareRequestData($personaData);

            // 3. Llamar a la API de ML
            $response = $this->client->post('/predict', [
                'json' => $requestData,
            ]);

            // 4. Procesar respuesta
            $prediction = json_decode($response->getBody()->getContents(), true);

            return [
                'success'         => true,
                'persona_id'      => $personaId,
                'riesgo'          => $prediction['riesgo'],
                'probabilidades'  => $prediction['probabilidades'],
                'dias_restantes'  => $prediction['dias_restantes'],
                'recomendacion'   => $prediction['recomendacion'],
            ];

        } catch (GuzzleException $e) {
            error_log("Error al llamar ML API: " . $e->getMessage());
            
            return [
                'success' => false,
                'error'   => 'Error de conexión con el servicio de ML',
                'details' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            error_log("Error en predictRisk: " . $e->getMessage());
            
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Obtiene los datos de una persona desde la base de datos.
     *
     * @param int $personaId ID de la persona
     * @return array|null Datos de la persona o null si no existe
     */
    private function getPersonaData(int $personaId): ?array
    {
        $db = \App\Models\Database::connect();

        $stmt = $db->prepare('
            SELECT 
                id,
                edad,
                pais,
                tipoVisa,
                fechaInicio,
                fechaFinal
            FROM personas 
            WHERE id = :id
        ');

        $stmt->execute([':id' => $personaId]);
        $persona = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $persona ?: null;
    }

    /**
     * Prepara los datos para enviar a la API de ML.
     *
     * @param array $personaData Datos de la persona desde la DB
     * @return array Datos formateados para la API
     */
    private function prepareRequestData(array $personaData): array
    {
        $hoy = new \DateTime();
        $fechaInicio = new \DateTime($personaData['fechaInicio']);
        $fechaFinal = new \DateTime($personaData['fechaFinal']);

        // Calcular días restantes
        $diasRestantes = $hoy->diff($fechaFinal)->days;
        if ($fechaFinal < $hoy) {
            $diasRestantes = 0; // Ya venció
        }

        // Calcular días desde inicio
        $diasDesdeInicio = $fechaInicio->diff($hoy)->days;

        // Calcular porcentaje transcurrido
        $duracionTotal = $fechaInicio->diff($fechaFinal)->days;
        $porcentajeTranscurrido = $duracionTotal > 0 
            ? ($diasDesdeInicio / $duracionTotal * 100) 
            : 0;

        // Flag si está en los últimos 3 meses
        $enUltimos3Meses = $diasRestantes <= 90 ? 1 : 0;

        // Simular renovaciones previas (en producción, esto vendría de la DB)
        $renovacionesPrevias = 0;

        return [
            'edad'                    => (int) $personaData['edad'],
            'pais'                    => $personaData['pais'],
            'tipo_visa'               => $personaData['tipoVisa'],
            'renovaciones_previas'    => $renovacionesPrevias,
            'dias_restantes'          => $diasRestantes,
            'dias_desde_inicio'       => $diasDesdeInicio,
            'porcentaje_transcurrido' => round($porcentajeTranscurrido, 2),
            'en_ultimos_3_meses'      => $enUltimos3Meses,
        ];
    }

    /**
     * Predice el riesgo para múltiples personas.
     *
     * @param array $personaIds Array de IDs de personas
     * @return array Array de predicciones
     */
    public function predictRiskBatch(array $personaIds): array
    {
        $predictions = [];

        foreach ($personaIds as $personaId) {
            $predictions[] = $this->predictRisk($personaId);
        }

        return $predictions;
    }

    /**
     * Obtiene estadísticas de riesgo para todas las personas activas.
     *
     * @return array Estadísticas agrupadas por nivel de riesgo
     */
    public function getRiskStatistics(): array
    {
        $db = \App\Models\Database::connect();

        // Obtener todas las personas activas
        $stmt = $db->query('SELECT id FROM personas');
        $personaIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($personaIds)) {
            return [
                'total'        => 0,
                'alto_riesgo'  => 0,
                'medio_riesgo' => 0,
                'bajo_riesgo'  => 0,
            ];
        }

        // Obtener predicciones
        $predictions = $this->predictRiskBatch($personaIds);

        // Agrupar por nivel de riesgo
        $stats = [
            'total'        => count($predictions),
            'alto_riesgo'  => 0,
            'medio_riesgo' => 0,
            'bajo_riesgo'  => 0,
            'errores'      => 0,
        ];

        foreach ($predictions as $prediction) {
            if (!$prediction['success']) {
                $stats['errores']++;
                continue;
            }

            $riesgo = $prediction['riesgo'];
            if (isset($stats[$riesgo])) {
                $stats[$riesgo]++;
            }
        }

        return $stats;
    }
}
