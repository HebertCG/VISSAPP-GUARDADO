"""
Generador de Datos Sintéticos para Clasificación de Riesgo de Vencimiento de Visas

Este script genera datos sintéticos realistas para entrenar el modelo de ML.
Los datos incluyen información de personas con visas y su clasificación de riesgo.
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

# Configuración de semilla para reproducibilidad
np.random.seed(42)
random.seed(42)

# Catálogos de datos
PAISES = [
    'Colombia', 'Venezuela', 'Perú', 'Ecuador', 'Bolivia',
    'Argentina', 'Chile', 'Brasil', 'México', 'Cuba',
    'República Dominicana', 'Honduras', 'Nicaragua', 'Haití'
]

TIPOS_VISA = [
    'Turista', 'Estudiante', 'Trabajo', 'Negocios', 
    'Residencia Temporal', 'Residencia Permanente'
]

# Pesos para distribución realista (algunos países/visas son más comunes)
PESOS_PAISES = [0.15, 0.12, 0.10, 0.08, 0.06, 0.08, 0.07, 0.09, 0.08, 0.05, 0.04, 0.03, 0.03, 0.02]
PESOS_VISAS = [0.30, 0.25, 0.20, 0.10, 0.10, 0.05]


def calcular_riesgo(dias_restantes: int) -> str:
    """
    Clasifica el riesgo basado en días restantes.
    
    Args:
        dias_restantes: Días hasta el vencimiento de la visa
        
    Returns:
        Categoría de riesgo: 'alto_riesgo', 'medio_riesgo', 'bajo_riesgo'
    """
    if dias_restantes < 30:
        return 'alto_riesgo'
    elif dias_restantes < 90:
        return 'medio_riesgo'
    else:
        return 'bajo_riesgo'


def generar_fecha_visa(riesgo_deseado: str = None) -> tuple:
    """
    Genera fechas de inicio y fin de visa.
    
    Args:
        riesgo_deseado: Si se especifica, genera fechas que resulten en ese riesgo
        
    Returns:
        Tupla (fecha_inicio, fecha_final, dias_restantes, dias_desde_inicio)
    """
    hoy = datetime.now()
    
    if riesgo_deseado == 'alto_riesgo':
        # Vence en menos de 30 días
        dias_restantes = random.randint(1, 29)
    elif riesgo_deseado == 'medio_riesgo':
        # Vence entre 30 y 90 días
        dias_restantes = random.randint(30, 89)
    elif riesgo_deseado == 'bajo_riesgo':
        # Vence en más de 90 días
        dias_restantes = random.randint(90, 365)
    else:
        # Aleatorio
        dias_restantes = random.randint(1, 365)
    
    fecha_final = hoy + timedelta(days=dias_restantes)
    
    # Duración típica de visas (90 días a 5 años)
    duracion_visa = random.choice([90, 180, 365, 730, 1095, 1825])
    fecha_inicio = fecha_final - timedelta(days=duracion_visa)
    
    dias_desde_inicio = (hoy - fecha_inicio).days
    
    return fecha_inicio, fecha_final, dias_restantes, dias_desde_inicio


def generar_registro(id_persona: int, riesgo_deseado: str = None) -> dict:
    """
    Genera un registro sintético de una persona con visa.
    
    Args:
        id_persona: ID único de la persona
        riesgo_deseado: Categoría de riesgo deseada (opcional)
        
    Returns:
        Diccionario con todos los datos de la persona
    """
    # Datos demográficos
    edad = random.randint(18, 75)
    pais = np.random.choice(PAISES, p=PESOS_PAISES)
    tipo_visa = np.random.choice(TIPOS_VISA, p=PESOS_VISAS)
    
    # Historial de renovaciones (más probable en visas de trabajo/estudio)
    if tipo_visa in ['Trabajo', 'Estudiante']:
        renovaciones_previas = random.choices([0, 1, 2, 3, 4], weights=[0.3, 0.3, 0.2, 0.15, 0.05])[0]
    else:
        renovaciones_previas = random.choices([0, 1, 2], weights=[0.7, 0.2, 0.1])[0]
    
    # Generar fechas
    fecha_inicio, fecha_final, dias_restantes, dias_desde_inicio = generar_fecha_visa(riesgo_deseado)
    
    # Calcular riesgo
    riesgo = calcular_riesgo(dias_restantes)
    
    # Características adicionales
    # Porcentaje de tiempo transcurrido de la visa
    duracion_total = (fecha_final - fecha_inicio).days
    porcentaje_transcurrido = (dias_desde_inicio / duracion_total * 100) if duracion_total > 0 else 0
    
    # Flag si está en los últimos 3 meses
    en_ultimos_3_meses = 1 if dias_restantes <= 90 else 0
    
    return {
        'id': id_persona,
        'edad': edad,
        'pais': pais,
        'tipo_visa': tipo_visa,
        'renovaciones_previas': renovaciones_previas,
        'fecha_inicio': fecha_inicio.strftime('%Y-%m-%d'),
        'fecha_final': fecha_final.strftime('%Y-%m-%d'),
        'dias_restantes': dias_restantes,
        'dias_desde_inicio': dias_desde_inicio,
        'porcentaje_transcurrido': round(porcentaje_transcurrido, 2),
        'en_ultimos_3_meses': en_ultimos_3_meses,
        'riesgo': riesgo
    }


def generar_dataset(n_registros: int = 1500, balanceado: bool = True) -> pd.DataFrame:
    """
    Genera un dataset completo de registros sintéticos.
    
    Args:
        n_registros: Número total de registros a generar
        balanceado: Si True, balancea las clases de riesgo
        
    Returns:
        DataFrame de pandas con todos los registros
    """
    registros = []
    
    if balanceado:
        # Distribuir equitativamente entre las 3 clases
        n_por_clase = n_registros // 3
        
        for i in range(n_por_clase):
            registros.append(generar_registro(i, 'alto_riesgo'))
        
        for i in range(n_por_clase, n_por_clase * 2):
            registros.append(generar_registro(i, 'medio_riesgo'))
        
        for i in range(n_por_clase * 2, n_registros):
            registros.append(generar_registro(i, 'bajo_riesgo'))
    else:
        # Distribución natural (más bajo riesgo que alto)
        for i in range(n_registros):
            riesgo = random.choices(
                ['alto_riesgo', 'medio_riesgo', 'bajo_riesgo'],
                weights=[0.2, 0.3, 0.5]
            )[0]
            registros.append(generar_registro(i, riesgo))
    
    # Crear DataFrame
    df = pd.DataFrame(registros)
    
    # Shuffle para mezclar los datos
    df = df.sample(frac=1, random_state=42).reset_index(drop=True)
    
    return df


def main():
    """Función principal para generar y guardar el dataset."""
    print("[*] Generando dataset sintetico para clasificacion de riesgo de visas...")
    
    # Generar dataset balanceado para entrenamiento
    df_train = generar_dataset(n_registros=1500, balanceado=True)
    
    # Generar dataset con distribución natural para testing
    df_test = generar_dataset(n_registros=300, balanceado=False)
    
    # Guardar archivos
    df_train.to_csv('data/visa_data_train.csv', index=False)
    df_test.to_csv('data/visa_data_test.csv', index=False)
    
    print(f"[OK] Dataset de entrenamiento generado: {len(df_train)} registros")
    print(f"[OK] Dataset de prueba generado: {len(df_test)} registros")
    
    # Mostrar distribución de clases
    print("\n[INFO] Distribucion de clases (entrenamiento):")
    print(df_train['riesgo'].value_counts())
    
    print("\n[INFO] Distribucion de clases (prueba):")
    print(df_test['riesgo'].value_counts())
    
    # Mostrar estadísticas
    print("\n[INFO] Estadisticas del dataset:")
    print(df_train.describe())
    
    print("\n[OK] Datos sinteticos generados exitosamente!")


if __name__ == '__main__':
    main()
