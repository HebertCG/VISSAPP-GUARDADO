# Guía de Despliegue en Render - VissApp v3

## 📋 Requisitos Previos

- ✅ Cuenta en [Render](https://render.com)
- ✅ Repositorio en GitHub: https://github.com/HebertCG/VISSAPP-GUARDADO.git
- ✅ Credenciales de Mailgun y Twilio

---

## 🚀 Despliegue Automático (Recomendado)

Render detectará automáticamente el archivo `render.yaml` y creará todos los servicios.

### Paso 1: Conectar Repositorio

1. Ir a [Render Dashboard](https://dashboard.render.com/)
2. Click en **"New +"** → **"Blueprint"**
3. Conectar tu repositorio de GitHub
4. Seleccionar `VISSAPP-GUARDADO`
5. Render detectará `render.yaml` automáticamente

### Paso 2: Configurar Variables de Entorno

En el dashboard de Render, configurar las siguientes variables **manualmente**:

#### Para `vissapp-web`:
```
MAILGUN_API_KEY=tu_api_key_aqui
MAILGUN_DOMAIN=tu_dominio.mailgun.org
TWILIO_SID=tu_twilio_sid
TWILIO_TOKEN=tu_twilio_token
TWILIO_FROM=+1234567890
```

### Paso 3: Desplegar

1. Click en **"Apply"**
2. Render creará automáticamente:
   - ✅ `vissapp-web` (PHP + Nginx)
   - ✅ `vissapp-ml` (FastAPI)
   - ✅ `vissapp-db` (PostgreSQL)

3. Esperar ~5-10 minutos para el primer despliegue

### Paso 4: Verificar

Una vez desplegado, tendrás 3 URLs:

- **Web App**: `https://vissapp-web.onrender.com`
- **ML API**: `https://vissapp-ml.onrender.com`
- **ML Docs**: `https://vissapp-ml.onrender.com/docs`

---

## 🔧 Despliegue Manual (Alternativa)

Si prefieres crear los servicios manualmente:

### 1. Crear Base de Datos

1. Dashboard → **"New +"** → **"PostgreSQL"**
2. Configurar:
   - **Name**: `vissapp-db`
   - **Database**: `vissapp`
   - **User**: `vissapp_user`
   - **Plan**: Starter ($7/mes)
3. Click **"Create Database"**
4. Guardar las credenciales generadas

### 2. Crear Servicio ML

1. Dashboard → **"New +"** → **"Web Service"**
2. Conectar repositorio
3. Configurar:
   - **Name**: `vissapp-ml`
   - **Environment**: Docker
   - **Dockerfile Path**: `./ml/Dockerfile.prod`
   - **Docker Context**: `./ml`
   - **Plan**: Starter ($7/mes)
4. Variables de entorno:
   ```
   PYTHONUNBUFFERED=1
   ```
5. Click **"Create Web Service"**

### 3. Crear Servicio Web

1. Dashboard → **"New +"** → **"Web Service"**
2. Conectar repositorio
3. Configurar:
   - **Name**: `vissapp-web`
   - **Environment**: Docker
   - **Dockerfile Path**: `./docker/php/Dockerfile.prod`
   - **Docker Context**: `.`
   - **Plan**: Starter ($7/mes)
4. Variables de entorno:
   ```
   APP_ENV=production
   APP_DEBUG=false
   DB_HOST=[copiar desde vissapp-db]
   DB_PORT=5432
   DB_DATABASE=vissapp
   DB_USERNAME=vissapp_user
   DB_PASSWORD=[copiar desde vissapp-db]
   ML_API_URL=https://vissapp-ml.onrender.com
   MAILGUN_API_KEY=tu_api_key
   MAILGUN_DOMAIN=tu_dominio
   TWILIO_SID=tu_sid
   TWILIO_TOKEN=tu_token
   TWILIO_FROM=+1234567890
   ```
5. Click **"Create Web Service"**

---

## 📊 Migración de Base de Datos

### Opción 1: Desde MySQL Local

```bash
# 1. Exportar datos de MySQL
mysqldump -u root vissappv3 > backup.sql

# 2. Convertir a PostgreSQL (manual)
# Editar backup.sql:
# - Cambiar AUTO_INCREMENT por SERIAL
# - Cambiar backticks ` por comillas dobles "
# - Cambiar ENGINE=InnoDB por nada

# 3. Conectar a Render PostgreSQL
psql -h [host_de_render] -U vissapp_user -d vissapp

# 4. Importar schema
\i database/schema.sql

# 5. Importar datos (si tienes)
\i backup_convertido.sql
```

### Opción 2: Usar el Schema Incluido

```bash
# Conectar a Render PostgreSQL
psql -h [host_de_render] -U vissapp_user -d vissapp

# Ejecutar schema
\i database/schema.sql
```

El schema ya incluye:
- ✅ Tablas: usuarios, personas, notifications
- ✅ Índices optimizados
- ✅ Usuarios de ejemplo (admin, soporte)

---

## ✅ Verificación Post-Despliegue

### 1. Health Checks

```bash
# Web App
curl https://vissapp-web.onrender.com/

# ML API
curl https://vissapp-ml.onrender.com/health

# Respuesta esperada:
# {"status":"healthy","modelo_cargado":true,"timestamp":"..."}
```

### 2. Test de Funcionalidad

1. **Login**:
   - Usuario: `admin`
   - Password: `admin123`

2. **ML API**:
   ```bash
   curl -X POST https://vissapp-ml.onrender.com/predict \
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

3. **CRUD de Personas**:
   - Crear nueva persona
   - Editar persona
   - Eliminar persona

---

## 💰 Costos Mensuales

| Servicio | Plan | Costo |
|----------|------|-------|
| vissapp-web | Starter | $7/mes |
| vissapp-ml | Starter | $7/mes |
| vissapp-db | Starter | $7/mes |
| **Total** | | **$21/mes** |

**Nota**: Render ofrece $5 de crédito gratis al registrarte.

---

## 🔄 Actualizaciones Automáticas

Render despliega automáticamente cuando haces push a `main`:

```bash
# Hacer cambios en el código
git add .
git commit -m "Actualización de funcionalidad"
git push origin main

# Render detecta el push y despliega automáticamente
```

---

## 🐛 Troubleshooting

### Error: "Build Failed"

1. Ver logs en Render Dashboard
2. Verificar que Dockerfiles existan
3. Verificar rutas en `render.yaml`

### Error: "Database Connection Failed"

1. Verificar variables de entorno en `vissapp-web`
2. Verificar que `vissapp-db` esté "Available"
3. Verificar credenciales de DB

### Error: "ML API Not Responding"

1. Ver logs de `vissapp-ml`
2. Verificar que modelo esté en `/ml/models/`
3. Verificar health check: `/health`

### Servicio en "Suspended"

Render suspende servicios inactivos en free tier. Solución:
- Upgrade a plan Starter
- O hacer request cada 15 minutos

---

## 📝 Logs

Ver logs en tiempo real:

1. Dashboard → Seleccionar servicio
2. Click en **"Logs"**
3. O usar Render CLI:
   ```bash
   render logs vissapp-web
   ```

---

## 🔒 Seguridad

### Variables de Entorno

- ✅ Nunca commitear `.env` con credenciales reales
- ✅ Usar "Environment Variables" en Render Dashboard
- ✅ Rotar credenciales periódicamente

### HTTPS

- ✅ Render proporciona HTTPS automático
- ✅ Certificados SSL gratuitos
- ✅ Renovación automática

---

## 🌐 Dominio Personalizado (Opcional)

1. Dashboard → `vissapp-web` → **"Settings"**
2. Scroll a **"Custom Domain"**
3. Agregar tu dominio (ej: `vissapp.com`)
4. Configurar DNS según instrucciones de Render
5. Esperar propagación DNS (~24h)

---

## 📚 Recursos

- [Render Docs](https://render.com/docs)
- [Render YAML Spec](https://render.com/docs/yaml-spec)
- [PostgreSQL en Render](https://render.com/docs/databases)
- [Docker en Render](https://render.com/docs/docker)

---

## 🆘 Soporte

Si tienes problemas:
1. Ver logs en Dashboard
2. Consultar [Render Community](https://community.render.com/)
3. Contactar soporte de Render
