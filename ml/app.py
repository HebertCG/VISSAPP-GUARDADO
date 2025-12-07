"""
API FastAPI para Predicción de Riesgo de Vencimiento de Visas

Microservicio que expone el modelo de ML a través de una API REST.
"""

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field, validator
from typing import Dict, List
import joblib
import numpy as np
from datetime import datetime
import os

# Inicializar FastAPI
app = FastAPI(
    title="Visa Risk Prediction API",
    description="API para predecir el riesgo de vencimiento de visas",
    version="1.0.0"
)

# Configurar CORS para permitir requests desde PHP
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # En producción, especificar dominios permitidos
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Cargar modelo al iniciar la aplicación
MODEL_PATH = 'models/visa_risk_classifier.pkl'
modelo_cargado = None


def cargar_modelo():
    """Carga el modelo entrenado."""
    global modelo_cargado
    
    if not os.path.exists(MODEL_PATH):
        raise FileNotFoundError(
            f"Modelo no encontrado en {MODEL_PATH}. "
            "Ejecuta 'python train.py' primero."
        )
    
    modelo_cargado = joblib.load(MODEL_PATH)
    print(f"✅ Modelo cargado desde {MODEL_PATH}")


# Modelos Pydantic para validación
class PersonaInput(BaseModel):
    """Datos de entrada para predicción."""
    
    edad: int = Field(..., ge=18, le=100, description="Edad de la persona")
    pais: str = Field(..., min_length=2, description="País de origen")
    tipo_visa: str = Field(..., description="Tipo de visa")
    renovaciones_previas: int = Field(..., ge=0, le=10, description="Número de renovaciones previas")
    dias_restantes: int = Field(..., ge=0, description="Días hasta vencimiento")
    dias_desde_inicio: int = Field(..., ge=0, description="Días desde inicio de visa")
    porcentaje_transcurrido: float = Field(..., ge=0, le=100, description="Porcentaje de tiempo transcurrido")
    en_ultimos_3_meses: int = Field(..., ge=0, le=1, description="1 si vence en menos de 90 días, 0 si no")
    
    @validator('tipo_visa')
    def validar_tipo_visa(cls, v):
        """Valida que el tipo de visa sea válido."""
        tipos_validos = [
            'Turista', 'Estudiante', 'Trabajo', 'Negocios',
            'Residencia Temporal', 'Residencia Permanente'
        ]
        if v not in tipos_validos:
            raise ValueError(f'Tipo de visa debe ser uno de: {", ".join(tipos_validos)}')
        return v
    
    class Config:
        json_schema_extra = {
            "example": {
                "edad": 28,
                "pais": "Colombia",
                "tipo_visa": "Estudiante",
                "renovaciones_previas": 1,
                "dias_restantes": 45,
                "dias_desde_inicio": 320,
                "porcentaje_transcurrido": 87.67,
                "en_ultimos_3_meses": 1
            }
        }


class PrediccionOutput(BaseModel):
    """Respuesta de la predicción."""
    
    riesgo: str = Field(..., description="Categoría de riesgo predicha")
    probabilidades: Dict[str, float] = Field(..., description="Probabilidades por categoría")
    dias_restantes: int = Field(..., description="Días hasta vencimiento")
    recomendacion: str = Field(..., description="Recomendación basada en el riesgo")
    
    class Config:
        json_schema_extra = {
            "example": {
                "riesgo": "medio_riesgo",
                "probabilidades": {
                    "alto_riesgo": 0.15,
                    "medio_riesgo": 0.70,
                    "bajo_riesgo": 0.15
                },
                "dias_restantes": 45,
                "recomendacion": "Considere iniciar el proceso de renovación pronto"
            }
        }


class HealthResponse(BaseModel):
    """Respuesta del endpoint de salud."""
    status: str
    modelo_cargado: bool
    timestamp: str


# Endpoints
@app.on_event("startup")
async def startup_event():
    """Carga el modelo al iniciar la aplicación."""
    try:
        cargar_modelo()
    except Exception as e:
        print(f"⚠️ Error al cargar modelo: {e}")


@app.get("/", response_model=Dict[str, str])
async def root():
    """Endpoint raíz con información de la API."""
    return {
        "mensaje": "API de Predicción de Riesgo de Visas",
        "version": "1.0.0",
        "docs": "/docs",
        "health": "/health"
    }


@app.get("/health", response_model=HealthResponse)
async def health_check():
    """Verifica el estado de la API y el modelo."""
    return {
        "status": "healthy" if modelo_cargado is not None else "unhealthy",
        "modelo_cargado": modelo_cargado is not None,
        "timestamp": datetime.now().isoformat()
    }


@app.post("/predict", response_model=PrediccionOutput)
async def predecir_riesgo(persona: PersonaInput):
    """
    Predice el riesgo de vencimiento de visa para una persona.
    
    Args:
        persona: Datos de la persona
        
    Returns:
        Predicción de riesgo con probabilidades
    """
    if modelo_cargado is None:
        raise HTTPException(
            status_code=503,
            detail="Modelo no disponible. El servicio está iniciando."
        )
    
    try:
        # Preparar features
        pais_encoded = -1
        tipo_visa_encoded = -1
        
        # Encodear país
        if persona.pais in modelo_cargado['pais_encoder'].classes_:
            pais_encoded = modelo_cargado['pais_encoder'].transform([persona.pais])[0]
        
        # Encodear tipo de visa
        if persona.tipo_visa in modelo_cargado['tipo_visa_encoder'].classes_:
            tipo_visa_encoded = modelo_cargado['tipo_visa_encoder'].transform([persona.tipo_visa])[0]
        
        # Crear array de features
        features = np.array([[
            persona.edad,
            pais_encoded,
            tipo_visa_encoded,
            persona.renovaciones_previas,
            persona.dias_restantes,
            persona.dias_desde_inicio,
            persona.porcentaje_transcurrido,
            persona.en_ultimos_3_meses
        ]])
        
        # Realizar predicción
        prediccion = modelo_cargado['model'].predict(features)[0]
        probabilidades = modelo_cargado['model'].predict_proba(features)[0]
        
        # Mapear probabilidades a clases
        clases = modelo_cargado['model'].classes_
        prob_dict = {
            clase: float(prob) 
            for clase, prob in zip(clases, probabilidades)
        }
        
        # Generar recomendación
        if prediccion == 'alto_riesgo':
            recomendacion = "⚠️ URGENTE: Inicie el proceso de renovación inmediatamente"
        elif prediccion == 'medio_riesgo':
            recomendacion = "⚡ Considere iniciar el proceso de renovación pronto"
        else:
            recomendacion = "✅ Aún tiene tiempo suficiente, pero manténgase atento"
        
        return {
            "riesgo": prediccion,
            "probabilidades": prob_dict,
            "dias_restantes": persona.dias_restantes,
            "recomendacion": recomendacion
        }
        
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error al realizar predicción: {str(e)}"
        )


@app.post("/predict/batch", response_model=List[PrediccionOutput])
async def predecir_riesgo_batch(personas: List[PersonaInput]):
    """
    Predice el riesgo para múltiples personas.
    
    Args:
        personas: Lista de personas
        
    Returns:
        Lista de predicciones
    """
    resultados = []
    
    for persona in personas:
        try:
            resultado = await predecir_riesgo(persona)
            resultados.append(resultado)
        except Exception as e:
            # En caso de error, agregar predicción con error
            resultados.append({
                "riesgo": "error",
                "probabilidades": {},
                "dias_restantes": persona.dias_restantes,
                "recomendacion": f"Error: {str(e)}"
            })
    
    return resultados


if __name__ == "__main__":
    import uvicorn
    
    print("🚀 Iniciando API de Predicción de Riesgo de Visas...")
    print("📖 Documentación disponible en: http://localhost:8001/docs")
    
    uvicorn.run(
        "app:app",
        host="0.0.0.0",
        port=8001,
        reload=True,
        log_level="info"
    )
