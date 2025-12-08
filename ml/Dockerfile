# Dockerfile para Microservicio de Machine Learning
FROM python:3.11-slim

# Metadata
LABEL maintainer="VissApp Team"
LABEL description="Microservicio ML para clasificación de riesgo de visas"

# Variables de entorno
ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

# Directorio de trabajo
WORKDIR /app

# Copiar requirements primero (para aprovechar cache de Docker)
COPY requirements.txt .

# Instalar dependencias
RUN pip install --no-cache-dir -r requirements.txt

# Copiar código de la aplicación
COPY app.py .
COPY train.py .
COPY data_generator.py .

# Copiar modelos y datos (si existen)
COPY models/ ./models/
COPY data/ ./data/

# Exponer puerto
EXPOSE 8001

# Health check (usando wget que viene con la imagen)
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD python -c "import urllib.request; urllib.request.urlopen('http://localhost:8001/health')" || exit 1

# Comando para ejecutar la aplicación
CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8001", "--reload"]
