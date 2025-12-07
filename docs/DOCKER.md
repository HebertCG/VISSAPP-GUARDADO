# Docker Setup - VissApp v3

Guía completa para ejecutar VissApp con Docker.

## 📋 Requisitos

- Docker Desktop 4.0+
- Docker Compose 2.0+
- 4GB RAM mínimo
- 10GB espacio en disco

## 🚀 Inicio Rápido

### 1. Configurar variables de entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar .env con tus credenciales
```

### 2. Construir y levantar servicios

```bash
# Construir imágenes
docker-compose build

# Levantar todos los servicios
docker-compose up -d

# Ver logs
docker-compose logs -f
```

### 3. Verificar servicios

```bash
# Ver estado de contenedores
docker-compose ps

# Todos los servicios deben estar "Up" y "healthy"
```

### 4. Acceder a la aplicación

- **Aplicación Web**: http://localhost:8000
- **API ML**: http://localhost:8001
- **Documentación ML**: http://localhost:8001/docs
- **PostgreSQL**: localhost:5432

## 🐳 Servicios

### 1. nginx (Servidor Web)
- **Puerto**: 8000
- **Imagen**: nginx:alpine
- **Función**: Servidor web que sirve la aplicación PHP

### 2. php (Aplicación Principal)
- **Puerto**: 9000 (interno)
- **Imagen**: Custom (PHP 8.2-FPM)
- **Función**: Ejecuta el código PHP de VissApp

### 3. db (Base de Datos)
- **Puerto**: 5432
- **Imagen**: postgres:15-alpine
- **Función**: Base de datos PostgreSQL
- **Credenciales**: Ver .env

### 4. ml (Machine Learning API)
- **Puerto**: 8001
- **Imagen**: Custom (Python 3.11)
- **Función**: API de predicción de riesgo de visas

## 📝 Comandos Útiles

### Gestión de Contenedores

```bash
# Iniciar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Reiniciar un servicio específico
docker-compose restart php

# Ver logs de un servicio
docker-compose logs -f php

# Ejecutar comando en contenedor
docker-compose exec php bash
docker-compose exec ml python
```

### Base de Datos

```bash
# Acceder a PostgreSQL
docker-compose exec db psql -U vissapp_user -d vissapp

# Backup de base de datos
docker-compose exec db pg_dump -U vissapp_user vissapp > backup.sql

# Restaurar base de datos
docker-compose exec -T db psql -U vissapp_user vissapp < backup.sql

# Ver logs de DB
docker-compose logs -f db
```

### PHP

```bash
# Ejecutar Composer
docker-compose exec php composer install

# Ejecutar tests
docker-compose exec php composer test

# Ver logs de PHP
docker-compose logs -f php

# Limpiar cache
docker-compose exec php rm -rf vendor/
docker-compose exec php composer install
```

### Machine Learning

```bash
# Acceder al contenedor ML
docker-compose exec ml bash

# Re-entrenar modelo
docker-compose exec ml python train.py

# Generar nuevos datos
docker-compose exec ml python data_generator.py

# Ver logs de ML
docker-compose logs -f ml
```

## 🔧 Desarrollo

### Hot Reload

Los volúmenes están configurados para hot reload:
- Cambios en PHP se reflejan inmediatamente
- Cambios en Python requieren reiniciar: `docker-compose restart ml`

### Debugging

```bash
# Ver todos los logs
docker-compose logs -f

# Inspeccionar un contenedor
docker-compose exec php bash
docker inspect vissapp_php

# Ver uso de recursos
docker stats
```

## 🏗️ Construcción

### Reconstruir imágenes

```bash
# Reconstruir todo
docker-compose build --no-cache

# Reconstruir un servicio específico
docker-compose build --no-cache php
docker-compose build --no-cache ml
```

### Optimización para Producción

```bash
# Construir sin cache de desarrollo
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build

# Usar variables de producción
cp .env.production .env
```

## 🧪 Testing

```bash
# Ejecutar tests PHP
docker-compose exec php composer test

# Ejecutar tests específicos
docker-compose exec php composer test:unit
docker-compose exec php composer test:integration

# Verificar ML API
curl http://localhost:8001/health
```

## 🔒 Seguridad

### Cambiar credenciales por defecto

Editar `.env`:
```env
DB_PASSWORD=tu_password_seguro_aqui
POSTGRES_PASSWORD=tu_password_seguro_aqui
```

### Secrets en producción

Usar Docker Secrets o variables de entorno del host:
```bash
docker-compose --env-file .env.production up -d
```

## 🐛 Troubleshooting

### Contenedor no inicia

```bash
# Ver logs detallados
docker-compose logs php

# Verificar configuración
docker-compose config

# Reiniciar desde cero
docker-compose down -v
docker-compose up -d
```

### Error de conexión a DB

```bash
# Verificar que DB esté healthy
docker-compose ps

# Reiniciar DB
docker-compose restart db

# Ver logs de DB
docker-compose logs db
```

### Puerto ya en uso

```bash
# Cambiar puerto en .env
APP_PORT=8080
ML_PORT=8002

# O detener proceso que usa el puerto
# Windows
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Linux/Mac
lsof -i :8000
kill -9 <PID>
```

### ML API no responde

```bash
# Verificar que el modelo esté entrenado
docker-compose exec ml ls -la models/

# Re-entrenar si es necesario
docker-compose exec ml python data_generator.py
docker-compose exec ml python train.py

# Reiniciar servicio
docker-compose restart ml
```

## 📊 Monitoreo

### Health Checks

```bash
# Verificar salud de servicios
docker-compose ps

# Health check manual
curl http://localhost:8000/
curl http://localhost:8001/health
```

### Logs

```bash
# Todos los logs
docker-compose logs -f

# Últimas 100 líneas
docker-compose logs --tail=100

# Logs de un servicio
docker-compose logs -f nginx
```

## 🧹 Limpieza

```bash
# Detener y eliminar contenedores
docker-compose down

# Eliminar también volúmenes (¡CUIDADO! Borra la DB)
docker-compose down -v

# Limpiar imágenes no usadas
docker system prune -a

# Limpiar todo Docker
docker system prune -a --volumes
```

## 📚 Recursos

- [Docker Docs](https://docs.docker.com/)
- [Docker Compose Docs](https://docs.docker.com/compose/)
- [PostgreSQL Docker](https://hub.docker.com/_/postgres)
- [PHP Docker](https://hub.docker.com/_/php)
- [Nginx Docker](https://hub.docker.com/_/nginx)
