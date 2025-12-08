# Guía de Despliegue GRATUITO - VissApp v3

## 🆓 Stack 100% Gratuito

- **Vercel**: Frontend PHP (Gratis)
- **Supabase**: PostgreSQL (Gratis - 500MB)
- **PythonAnywhere**: ML API (Gratis - 512MB)

**Costo Total: $0/mes** 🎉

---

## 📋 Requisitos Previos

- ✅ Cuenta en GitHub
- ✅ Repositorio: https://github.com/HebertCG/VISSAPP-GUARDADO.git
- ⏳ Crear cuentas en:
  - [Supabase](https://supabase.com)
  - [PythonAnywhere](https://www.pythonanywhere.com)
  - [Vercel](https://vercel.com)

---

## 🗄️ PASO 1: Configurar Supabase (Base de Datos)

### 1.1 Crear Proyecto

1. Ir a https://supabase.com
2. Click en **"New Project"**
3. Configurar:
   - **Name**: `vissapp`
   - **Database Password**: (guardar este password)
   - **Region**: Closest to you
   - **Plan**: Free
4. Click **"Create new project"**
5. Esperar ~2 minutos

### 1.2 Ejecutar Schema SQL

1. En el dashboard, ir a **"SQL Editor"**
2. Click **"New query"**
3. Copiar y pegar el contenido de `database/schema.sql`
4. Click **"Run"**
5. Verificar que se crearon las tablas

### 1.3 Obtener Credenciales

1. Ir a **"Settings"** → **"Database"**
2. Copiar:
   - **Host**: `db.xxx.supabase.co`
   - **Database name**: `postgres`
   - **Port**: `5432`
   - **User**: `postgres`
   - **Password**: (el que creaste en 1.1)

**Guardar estas credenciales**, las necesitarás después.

---

## 🐍 PASO 2: Configurar PythonAnywhere (ML API)

### 2.1 Crear Cuenta

1. Ir a https://www.pythonanywhere.com
2. Click **"Pricing & signup"** → **"Create a Beginner account"**
3. Completar registro (100% gratis)

### 2.2 Subir Código ML

1. En dashboard, ir a **"Files"**
2. Crear carpeta: `vissapp-ml`
3. Subir archivos desde `/ml`:
   - `app.py`
   - `requirements.txt`
   - `models/visa_risk_classifier.pkl`
   - `models/metrics.json`

**Alternativa (más rápido):**
```bash
# En PythonAnywhere Bash console
git clone https://github.com/HebertCG/VISSAPP-GUARDADO.git
cp -r VISSAPP-GUARDADO/ml/* vissapp-ml/
```

### 2.3 Instalar Dependencias

1. Ir a **"Consoles"** → **"Bash"**
2. Ejecutar:
```bash
cd vissapp-ml
pip3.10 install --user -r requirements.txt
```
3. Esperar ~5 minutos

### 2.4 Configurar Web App

1. Ir a **"Web"** → **"Add a new web app"**
2. Configurar:
   - **Python version**: 3.10
   - **Framework**: Manual configuration
3. En **"Code"**:
   - **Source code**: `/home/username/vissapp-ml`
   - **Working directory**: `/home/username/vissapp-ml`
4. En **"WSGI configuration file"**, editar y reemplazar TODO con:

```python
import sys
path = '/home/username/vissapp-ml'  # Cambiar 'username'
if path not in sys.path:
    sys.path.append(path)

from app import app as application
```

5. Click **"Reload"** (botón verde arriba)

### 2.5 Verificar

1. Tu ML API estará en: `https://username.pythonanywhere.com`
2. Probar: `https://username.pythonanywhere.com/health`
3. Deberías ver:
```json
{
  "status": "healthy",
  "modelo_cargado": true,
  "timestamp": "..."
}
```

**Guardar esta URL**, la necesitarás para Vercel.

---

## 🌐 PASO 3: Configurar Vercel (Frontend)

### 3.1 Conectar GitHub

1. Ir a https://vercel.com
2. Click **"Sign Up"** → **"Continue with GitHub"**
3. Autorizar Vercel

### 3.2 Importar Proyecto

1. Click **"Add New..."** → **"Project"**
2. Buscar `VISSAPP-GUARDADO`
3. Click **"Import"**

### 3.3 Configurar Variables de Entorno

Antes de desplegar, agregar variables de entorno:

1. En la página de configuración, ir a **"Environment Variables"**
2. Agregar las siguientes:

```
DB_HOST=db.xxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=tu_password_de_supabase

ML_API_URL=https://username.pythonanywhere.com

MAILGUN_API_KEY=tu_mailgun_key
MAILGUN_DOMAIN=tu_mailgun_domain

TWILIO_SID=tu_twilio_sid
TWILIO_TOKEN=tu_twilio_token
TWILIO_FROM=+1234567890

APP_ENV=production
APP_DEBUG=false
```

3. Click **"Add"** para cada variable

### 3.4 Desplegar

1. Click **"Deploy"**
2. Esperar ~3-5 minutos
3. Una vez completado, obtendrás una URL: `https://vissapp-xxx.vercel.app`

### 3.5 Verificar

1. Visitar tu URL de Vercel
2. Deberías ver la página de login
3. Intentar login con:
   - Usuario: `admin`
   - Password: `admin123`

---

## ✅ Verificación Completa

### Checklist:

- [ ] **Supabase**: Base de datos creada y schema ejecutado
- [ ] **PythonAnywhere**: ML API respondiendo en `/health`
- [ ] **Vercel**: Aplicación web accesible
- [ ] **Login**: Funciona correctamente
- [ ] **CRUD**: Crear/editar/eliminar personas funciona
- [ ] **ML**: Predicciones de riesgo funcionan

### Tests:

```bash
# 1. Test ML API
curl https://username.pythonanywhere.com/health

# 2. Test Web App
curl https://vissapp-xxx.vercel.app

# 3. Test ML Prediction
curl -X POST https://username.pythonanywhere.com/predict \
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

---

## 💰 Costos y Límites

| Servicio | Plan | Límites | Costo |
|----------|------|---------|-------|
| Supabase | Free | 500MB DB, 2GB bandwidth | $0 |
| PythonAnywhere | Beginner | 512MB storage, 100s CPU/día | $0 |
| Vercel | Hobby | 100GB bandwidth, ilimitado | $0 |
| **TOTAL** | | | **$0/mes** 🎉 |

---

## ⚠️ Limitaciones Conocidas

### Supabase:
- Proyectos pausados después de 1 semana de inactividad
- **Solución**: Hacer login cada semana

### PythonAnywhere:
- Solo 100 segundos de CPU por día
- **Solución**: Limitar predicciones ML a ~50/día

### Vercel:
- Serverless functions (no sesiones persistentes)
- **Solución**: Ya configurado con cookies

---

## 🔄 Actualizaciones

Para actualizar el código:

```bash
# 1. Hacer cambios localmente
git add .
git commit -m "Actualización"
git push origin main

# 2. Vercel despliega automáticamente

# 3. PythonAnywhere: actualizar manualmente
# En Bash console:
cd vissapp-ml
git pull
# Reload web app desde dashboard
```

---

## 🐛 Troubleshooting

### Error: "Database connection failed"

1. Verificar credenciales de Supabase en Vercel
2. Verificar que proyecto de Supabase no esté pausado
3. Ir a Supabase → Settings → Database → Verificar host

### Error: "ML API not responding"

1. Verificar que web app esté "Running" en PythonAnywhere
2. Ver logs en PythonAnywhere → Web → Error log
3. Verificar que modelo esté en `/home/username/vissapp-ml/models/`

### Error: "500 Internal Server Error" en Vercel

1. Ver logs en Vercel Dashboard → Deployments → Logs
2. Verificar que todas las variables de entorno estén configuradas
3. Verificar que `vendor/` esté en el repositorio

---

## 📚 URLs Importantes

- **Supabase Dashboard**: https://app.supabase.com
- **PythonAnywhere Dashboard**: https://www.pythonanywhere.com/user/username/
- **Vercel Dashboard**: https://vercel.com/dashboard

---

## 🆘 Soporte

Si tienes problemas:

1. **Supabase**: https://supabase.com/docs
2. **PythonAnywhere**: https://help.pythonanywhere.com
3. **Vercel**: https://vercel.com/docs

---

## 🎉 ¡Listo!

Tu aplicación está desplegada 100% gratis en:

- **Web App**: `https://vissapp-xxx.vercel.app`
- **ML API**: `https://username.pythonanywhere.com`
- **Database**: Supabase (PostgreSQL)

**Total: $0/mes** 🚀
