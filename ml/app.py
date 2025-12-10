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
import pytesseract
from PIL import Image
import io
import re
from fastapi import File, UploadFile

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


@app.post("/extract-data")
async def extract_data_from_image(file: UploadFile = File(...)):
    """
    Extrae texto de una imagen y busca patrones de pasaporte/visa.
    """
    try:
        # 1. Leer imagen
        contents = await file.read()
        image = Image.open(io.BytesIO(contents))
        
        # 2. OCR con Tesseract
        text = pytesseract.image_to_string(image)
        print(f"📄 Texto extraído:\n{text}")
        
        # 3. Procesamiento de texto (Heurística básica)
        # Esto es un ejemplo y debería mejorarse con más patrones
        data = {
            "nombre": "",
            "apellido": "",
            "numeroVisa": "",
            "raw_text": text
        }
        
        # Buscar patrón de Pasaporte/Visa (ejemplo simplificado)
        # P<COLAPELLIDO<<NOMBRE<<<<<<<<<<<<<<<<<<<<<<
        mrz_pattern = r"P<([A-Z]{3})([A-Z]+)<<([A-Z]+)"
        match = re.search(mrz_pattern, text)
        if match:
            data["pais"] = match.group(1)
            data["apellido"] = match.group(2).replace('<', ' ').strip()
            data["nombre"] = match.group(3).replace('<', ' ').strip()
            
        # Buscar algo que parezca número de visa (Patrón genérico)
        # Generalmente 8-10 caracteres alfanuméricos
        visa_pattern = r"\b([A-Z0-9]{8,10})\b"
        # Filtramos palabras comunes
        words = re.findall(visa_pattern, text)
        for w in words:
            if not w.isalpha() and not w.isdigit(): # Mezcla letras y numeros
                data["numeroVisa"] = w
                break

        return data
        
    except Exception as e:
        print(f"Error OCR: {e}")
        raise HTTPException(status_code=500, detail=str(e))



from pdf2image import convert_from_bytes

@app.post("/extract-pdf")
async def extract_data_from_pdf(file: UploadFile = File(...)):
    """
    Extrae texto de un archivo PDF completo (todas las páginas) usando OCR.
    """
    try:
        # 1. Leer PDF en memoria
        content = await file.read()
        
        # 2. Convertir PDF a imágenes - OPTIMIZADO
        # DPI reducido de 300 a 150 para velocidad (suficiente para texto)
        # Solo primeras 2 páginas (datos personales están en página 1)
        images = convert_from_bytes(
            content, 
            dpi=150,  # Reducido para velocidad
            first_page=1,
            last_page=2  # Solo primeras 2 páginas
        )
        
        full_text = ""
        
        # 3. Procesar cada página
        for i, image in enumerate(images):
            # OCR en español e inglés
            text = pytesseract.image_to_string(image, lang='eng+spa')
            full_text += f"\n--- Page {i+1} ---\n{text}"
            
        print(f"📄 Texto PDF extraído ({len(full_text)} caracteres)")
        
        # DEBUG: Guardar texto extraído para análisis
        with open('/app/debug_ocr_output.txt', 'w', encoding='utf-8') as f:
            f.write(full_text)
        print("💾 Texto guardado en /app/debug_ocr_output.txt")
        
        # 4. Extracción de datos con Regex (Migrado desde PHP para mayor inteligencia)
        data = {
            "nombre": "",
            "apellido": "",
            "numeroVisa": "",
            "fechaInicio": "",
            "fechaFinal": "",
            "referenciaTransaccion": "",
            "tipoVisa": "Student (subclass 500)",
            "pais": "",
            "edad": 0,
            "raw_text": full_text
        }
        
        # 1. Nombre completo - MEJORADO
        # Busca líneas que contengan solo nombres (palabras capitalizadas)
        # Formato típico: "Gabriel Esteban VARGAS MORENO" o similar
        lines = full_text.split('\n')
        
        # Limpiar espacios extra que el OCR introduce (ej: "J honatan" -> "Jhonatan")
        cleaned_lines = []
        for line in lines:
            # Eliminar espacios entre letra mayúscula y minúscula
            cleaned = re.sub(r'([A-Z])\s+([a-z])', r'\1\2', line)
            cleaned_lines.append(cleaned)
        
        for i, line in enumerate(cleaned_lines):
            if 'Name' in line:
                # Primero intentar extraer de la misma línea
                # Formato: "Name Gabriel Esteban VARGAS MORENO"
                m = re.search(r'Name\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s+([A-Z]+(?:\s+[A-Z]+)*)', line)
                if m:
                    data["nombre"] = m.group(1).strip()
                    data["apellido"] = m.group(2).strip()
                    break
                # Si no está en la misma línea, buscar en la siguiente
                elif i + 1 < len(cleaned_lines):
                    next_line = cleaned_lines[i + 1].strip()
                    m = re.match(r'^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s+([A-Z]+(?:\s+[A-Z]+)*)$', next_line)
                    if m:
                        data["nombre"] = m.group(1).strip()
                        data["apellido"] = m.group(2).strip()
                        break
        
        # 2. Fecha de nacimiento y cálculo de edad - MEJORADO
        for i, line in enumerate(cleaned_lines):
            if 'Date of birth' in line or 'birth' in line.lower():
                # Buscar en la misma línea o siguiente
                combined = line + ' ' + (cleaned_lines[i+1] if i+1 < len(cleaned_lines) else '')
                # Limpiar espacios en meses (ej: "J uly" -> "July")
                combined = re.sub(r'([A-Z])\s+([a-z]{2,})', r'\1\2', combined)
                m_dob = re.search(r'(\d{1,2})\s*([A-Za-z]+)\s+(\d{4})', combined)
                if m_dob:
                    dob_str = f"{m_dob.group(1)} {m_dob.group(2)} {m_dob.group(3)}"
                    try:
                        from datetime import datetime
                        dob = datetime.strptime(dob_str, "%d %B %Y")
                        today = datetime.now()
                        age = today.year - dob.year - ((today.month, today.day) < (dob.month, dob.day))
                        data["edad"] = age
                        break
                    except:
                        pass
        
        # 3. Visa Grant Number - MEJORADO
        for i, line in enumerate(cleaned_lines):
            if 'grant number' in line.lower() or 'Visa grant' in line:
                combined = line + ' ' + (cleaned_lines[i+1] if i+1 < len(cleaned_lines) else '')
                m = re.search(r'(\d{10,15})', combined)
                if m:
                    data["numeroVisa"] = m.group(1)
                    break
            
        # 4. Fechas de visa - MEJORADO
        for i, line in enumerate(cleaned_lines):
            if 'Date of grant' in line:
                combined = line + ' ' + (cleaned_lines[i+1] if i+1 < len(cleaned_lines) else '')
                combined = re.sub(r'([A-Z])\s+([a-z]{2,})', r'\1\2', combined)
                m = re.search(r'(\d{1,2})\s*([A-Za-z]+)\s+(\d{4})', combined)
                if m:
                    data["fechaInicio"] = f"{m.group(1)} {m.group(2)} {m.group(3)}"
                    break
        
        for i, line in enumerate(cleaned_lines):
            if 'not arrive after' in line or 'Must not arrive' in line:
                combined = line + ' ' + (cleaned_lines[i+1] if i+1 < len(cleaned_lines) else '')
                combined = re.sub(r'([A-Z])\s+([a-z]{2,})', r'\1\2', combined)
                m = re.search(r'(\d{1,2})\s*([A-Za-z]+)\s+(\d{4})', combined)
                if m:
                    data["fechaFinal"] = f"{m.group(1)} {m.group(2)} {m.group(3)}"
                    break
            
        # 5. Transaction Reference Number - MEJORADO
        for i, line in enumerate(cleaned_lines):
            if 'Transaction' in line or 'reference number' in line.lower():
                combined = line + ' ' + (cleaned_lines[i+1] if i+1 < len(cleaned_lines) else '')
                m = re.search(r'([A-Z]{2,}[0-9]{2,}[A-Z0-9]+)', combined)
                if m:
                    data["referenciaTransaccion"] = m.group(1)
                    break
            
        # 6. País - MEJORADO
        for i, line in enumerate(cleaned_lines):
            if 'Passport' in line and 'travel' in line:
                # El país puede estar en la misma línea o en las siguientes
                # Formato común: "Passport (or other travel COLOMBIA"
                # O: "document) country" en una línea y "COLOMBIA" en la siguiente
                
                # Intentar extraer de la misma línea primero
                m = re.search(r'travel\s+([A-Z]{4,})', line)
                if m:
                    data["pais"] = m.group(1).strip()
                    break
                
                # Si no, buscar en las siguientes 3 líneas
                for j in range(1, 4):
                    if i + j < len(cleaned_lines):
                        candidate = cleaned_lines[i+j].strip()
                        # País suele estar en mayúsculas y ser solo letras
                        if candidate.isupper() and len(candidate) > 3 and candidate.isalpha():
                            data["pais"] = candidate
                            break
                if data["pais"]:
                    break
        
        # 7. Tipo de Visa
        m_visa_type = re.search(r'Student \(subclass \d+\)', full_text, re.IGNORECASE)
        if m_visa_type:
            data["tipoVisa"] = m_visa_type.group(0)
        
        print(f"✅ Datos extraídos: Nombre={data['nombre']}, Apellido={data['apellido']}, País={data['pais']}, Edad={data['edad']}, Visa={data['numeroVisa']}")
        
        return data

    except Exception as e:
        print(f"❌ Error procesando PDF: {str(e)}")
        import traceback
        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))


if __name__ == "__main__":
    import uvicorn
    
    print("🚀 Iniciando API de Predicción de Riesgo de Visas...")
    print("📖 Documentación disponible en: http://localhost:8001")
    
    uvicorn.run(
        "app:app",
        host="0.0.0.0",
        port=8001,
        reload=True,
        log_level="info"
    )
