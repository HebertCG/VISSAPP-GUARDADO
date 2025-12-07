# Microservicio de Machine Learning - Clasificación de Riesgo de Visas

Microservicio Python con FastAPI que predice el riesgo de vencimiento de visas usando Random Forest.

## 🎯 Objetivo

Clasificar el riesgo de vencimiento de visas en 3 categorías:
- 🔴 **Alto Riesgo**: Vence en < 30 días
- 🟡 **Medio Riesgo**: Vence en 30-90 días
- 🟢 **Bajo Riesgo**: Vence en > 90 días

## 📋 Requisitos

- Python 3.9+
- pip

## 🚀 Instalación

### 1. Crear entorno virtual

```bash
cd ml
python -m venv venv

# Windows
venv\Scripts\activate

# Linux/Mac
source venv/bin/activate
```

### 2. Instalar dependencias

```bash
pip install -r requirements.txt
```

## 📊 Generar Datos y Entrenar Modelo

### 1. Generar datos sintéticos

```bash
python data_generator.py
```

Esto creará:
- `data/visa_data_train.csv` (1500 registros)
- `data/visa_data_test.csv` (300 registros)

### 2. Entrenar el modelo

```bash
python train.py
```

Esto creará:
- `models/visa_risk_classifier.pkl` (modelo entrenado)
- `models/metrics.json` (métricas de evaluación)

**Métricas esperadas:**
- Accuracy: > 85%
- Precision: > 0.85
- Recall: > 0.85
- F1-Score: > 0.85

## 🌐 Ejecutar API

```bash
python app.py
```

La API estará disponible en:
- **URL**: http://localhost:8001
- **Documentación interactiva**: http://localhost:8001/docs
- **Documentación alternativa**: http://localhost:8001/redoc

## 📡 Endpoints

### GET /
Información de la API

### GET /health
Verificar estado del servicio

**Respuesta:**
```json
{
  "status": "healthy",
  "modelo_cargado": true,
  "timestamp": "2025-12-07T16:00:00"
}
```

### POST /predict
Predecir riesgo para una persona

**Request:**
```json
{
  "edad": 28,
  "pais": "Colombia",
  "tipo_visa": "Estudiante",
  "renovaciones_previas": 1,
  "dias_restantes": 45,
  "dias_desde_inicio": 320,
  "porcentaje_transcurrido": 87.67,
  "en_ultimos_3_meses": 1
}
```

**Response:**
```json
{
  "riesgo": "medio_riesgo",
  "probabilidades": {
    "alto_riesgo": 0.15,
    "medio_riesgo": 0.70,
    "bajo_riesgo": 0.15
  },
  "dias_restantes": 45,
  "recomendacion": "⚡ Considere iniciar el proceso de renovación pronto"
}
```

### POST /predict/batch
Predecir riesgo para múltiples personas

**Request:**
```json
[
  {
    "edad": 28,
    "pais": "Colombia",
    "tipo_visa": "Estudiante",
    ...
  },
  {
    "edad": 35,
    "pais": "Venezuela",
    "tipo_visa": "Trabajo",
    ...
  }
]
```

## 🧪 Probar la API

### Con curl

```bash
# Health check
curl http://localhost:8001/health

# Predicción
curl -X POST http://localhost:8001/predict \
  -H "Content-Type: application/json" \
  -d '{
    "edad": 28,
    "pais": "Colombia",
    "tipo_visa": "Estudiante",
    "renovaciones_previas": 1,
    "dias_restantes": 45,
    "dias_desde_inicio": 320,
    "porcentaje_transcurrido": 87.67,
    "en_ultimos_3_meses": 1
  }'
```

### Con Python

```python
import requests

url = "http://localhost:8001/predict"
data = {
    "edad": 28,
    "pais": "Colombia",
    "tipo_visa": "Estudiante",
    "renovaciones_previas": 1,
    "dias_restantes": 45,
    "dias_desde_inicio": 320,
    "porcentaje_transcurrido": 87.67,
    "en_ultimos_3_meses": 1
}

response = requests.post(url, json=data)
print(response.json())
```

## 🔧 Integración con PHP

El servicio `MlService.php` en la aplicación principal consume esta API:

```php
use App\Services\MlService;

$mlService = new MlService();

// Verificar disponibilidad
if ($mlService->isAvailable()) {
    // Predecir riesgo
    $prediction = $mlService->predictRisk($personaId);
    
    echo "Riesgo: " . $prediction['riesgo'];
    echo "Recomendación: " . $prediction['recomendacion'];
}
```

## 📁 Estructura de Archivos

```
ml/
├── app.py                  # API FastAPI
├── train.py                # Script de entrenamiento
├── data_generator.py       # Generador de datos sintéticos
├── requirements.txt        # Dependencias Python
├── data/                   # Datos generados
│   ├── visa_data_train.csv
│   └── visa_data_test.csv
└── models/                 # Modelos entrenados
    ├── visa_risk_classifier.pkl
    └── metrics.json
```

## 🐳 Docker (Opcional)

```dockerfile
FROM python:3.11-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8001"]
```

**Construir y ejecutar:**

```bash
docker build -t vissapp-ml .
docker run -p 8001:8001 vissapp-ml
```

## 📊 Features del Modelo

El modelo utiliza las siguientes características:

1. **edad**: Edad de la persona (18-100)
2. **pais_encoded**: País de origen (codificado)
3. **tipo_visa_encoded**: Tipo de visa (codificado)
4. **renovaciones_previas**: Número de renovaciones anteriores (0-10)
5. **dias_restantes**: Días hasta vencimiento
6. **dias_desde_inicio**: Días desde inicio de visa
7. **porcentaje_transcurrido**: % de tiempo transcurrido (0-100)
8. **en_ultimos_3_meses**: Flag binario (1 si vence en <90 días)

## 🎯 Métricas del Modelo

Después del entrenamiento, revisa `models/metrics.json`:

```json
{
  "accuracy": 0.95,
  "precision": 0.94,
  "recall": 0.95,
  "f1_score": 0.94,
  "cv_accuracy_mean": 0.94,
  "cv_accuracy_std": 0.02
}
```

## 🔍 Troubleshooting

### Error: "Modelo no encontrado"
```bash
# Ejecutar entrenamiento primero
python data_generator.py
python train.py
```

### Error: "ModuleNotFoundError"
```bash
# Verificar que el entorno virtual esté activado
pip install -r requirements.txt
```

### API no responde
```bash
# Verificar que el puerto 8001 esté libre
netstat -ano | findstr :8001

# Cambiar puerto si es necesario
uvicorn app:app --port 8002
```

## 📝 Licencia

MIT
