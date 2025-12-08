# Guía de Despliegue con Ngrok - VissApp v3

## 🚀 Despliegue GRATIS en 5 Minutos

**Ngrok** crea un túnel público a tu Docker local. 100% gratis, sin limitaciones.

---

## ✅ Ventajas de Ngrok

- ✅ **100% Gratis** (plan gratuito suficiente)
- ✅ **Sin limitaciones** de espacio, CPU, o memoria
- ✅ **Ya tienes Docker funcionando** localmente
- ✅ **Setup en 5 minutos**
- ✅ **No requiere cambios** en el código
- ✅ **HTTPS automático** (SSL gratis)

## ⚠️ Desventajas

- ⚠️ Tu PC debe estar encendida 24/7
- ⚠️ URL cambia cada vez que reinicias (en plan gratis)
- ⚠️ No es tan "profesional" como hosting en la nube

---

## 📋 Paso 1: Instalar Ngrok

### Opción A: Con Chocolatey (Recomendado)

```powershell
# Instalar Chocolatey si no lo tienes
# https://chocolatey.org/install

# Instalar Ngrok
choco install ngrok
```

### Opción B: Descarga Manual

1. Ir a https://ngrok.com/download
2. Descargar para Windows
3. Extraer `ngrok.exe` a `C:\Windows\System32\`

---

## 📋 Paso 2: Crear Cuenta en Ngrok (Gratis)

1. Ir a https://dashboard.ngrok.com/signup
2. Crear cuenta gratuita (con GitHub o email)
3. Copiar tu **Authtoken**

---

## 📋 Paso 3: Configurar Authtoken

```powershell
# Configurar tu authtoken (solo una vez)
ngrok config add-authtoken TU_AUTHTOKEN_AQUI
```

---

## 📋 Paso 4: Iniciar Docker

```powershell
# Navegar a tu proyecto
cd C:\xampp\htdocs\VissApp_v3

# Iniciar Docker Compose
docker-compose up -d

# Verificar que esté corriendo
docker-compose ps
```

Deberías ver:
- ✅ vissapp_nginx (healthy)
- ✅ vissapp_php (running)
- ✅ vissapp_db (healthy)
- ✅ vissapp_ml (healthy)

---

## 📋 Paso 5: Exponer con Ngrok

### Para la Aplicación Web (Puerto 8000):

```powershell
ngrok http 8000
```

Verás algo como:

```
Session Status                online
Account                       tu_email@gmail.com
Version                       3.x.x
Region                        United States (us)
Latency                       -
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123.ngrok.io -> http://localhost:8000

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

**Tu URL pública es:** `https://abc123.ngrok.io` ✅

---

## 📋 Paso 6: Exponer ML API (Opcional)

Si quieres exponer también la ML API por separado:

```powershell
# En otra terminal
ngrok http 8001
```

Obtendrás otra URL para la ML API.

---

## ✅ Verificar que Funciona

### 1. Aplicación Web:

Abre en tu navegador:
```
https://abc123.ngrok.io
```

Deberías ver la página de login de VissApp.

### 2. ML API:

```bash
curl https://abc123.ngrok.io:8001/health
```

O si expusiste ML por separado:
```bash
curl https://xyz456.ngrok.io/health
```

---

## 🔧 Configuración Avanzada (Opcional)

### Usar un Subdominio Personalizado (Plan Gratis)

Crear archivo `ngrok.yml`:

```yaml
version: "2"
authtoken: TU_AUTHTOKEN_AQUI
tunnels:
  web:
    proto: http
    addr: 8000
  ml:
    proto: http
    addr: 8001
```

Luego ejecutar:
```powershell
ngrok start --all
```

---

## 🌐 URLs Finales

Después de ejecutar Ngrok, tendrás:

- **Aplicación Web**: `https://abc123.ngrok.io`
- **ML API**: `https://abc123.ngrok.io` (puerto 8001)
- **ML Docs**: `https://abc123.ngrok.io/docs` (puerto 8001)
- **Ngrok Dashboard**: `http://localhost:4040` (para ver requests)

---

## 💡 Tips

### Mantener Ngrok Corriendo

Ngrok se cierra si cierras la terminal. Para mantenerlo corriendo:

**Opción 1: Usar `nohup` (Linux/Mac)**
```bash
nohup ngrok http 8000 &
```

**Opción 2: Crear un Servicio de Windows**
Usar `nssm` (Non-Sucking Service Manager):
```powershell
choco install nssm
nssm install ngrok "C:\Windows\System32\ngrok.exe" "http 8000"
nssm start ngrok
```

**Opción 3: Dejar la terminal abierta**
La más simple: solo deja la terminal abierta.

---

## 🔄 Reiniciar Ngrok

Si reinicias Ngrok, la URL cambiará. Para mantener la misma URL:

**Upgrade a Ngrok Pro** ($8/mes):
- URL fija personalizada
- Más conexiones simultáneas
- Sin limitaciones

O usar el plan gratuito y actualizar la URL cada vez.

---

## 📊 Monitoreo

Ngrok incluye un dashboard web en:
```
http://localhost:4040
```

Ahí puedes ver:
- Todas las requests HTTP
- Respuestas
- Tiempos de respuesta
- Errores

---

## 🐛 Troubleshooting

### Error: "command not found: ngrok"

Solución:
```powershell
# Verificar instalación
where ngrok

# Si no está, reinstalar
choco install ngrok
```

### Error: "authentication failed"

Solución:
```powershell
# Reconfigurar authtoken
ngrok config add-authtoken TU_AUTHTOKEN_AQUI
```

### Docker no está corriendo

Solución:
```powershell
# Verificar Docker
docker-compose ps

# Si no está corriendo
docker-compose up -d
```

---

## 💰 Costos

**Plan Gratuito:**
- ✅ 1 proceso de Ngrok
- ✅ 40 conexiones/minuto
- ✅ HTTPS automático
- ⚠️ URL cambia al reiniciar

**Costo: $0/mes** 🎉

**Plan Pro ($8/mes):**
- ✅ URLs fijas personalizadas
- ✅ Múltiples túneles simultáneos
- ✅ Sin límites de conexiones

---

## 🎯 Resumen

1. ✅ Instalar Ngrok: `choco install ngrok`
2. ✅ Crear cuenta en ngrok.com
3. ✅ Configurar authtoken
4. ✅ Iniciar Docker: `docker-compose up -d`
5. ✅ Exponer con Ngrok: `ngrok http 8000`
6. ✅ Compartir URL pública: `https://abc123.ngrok.io`

**¡Listo! Tu aplicación está en línea.** 🚀

---

## 📚 Recursos

- [Ngrok Docs](https://ngrok.com/docs)
- [Ngrok Dashboard](https://dashboard.ngrok.com)
- [Ngrok Pricing](https://ngrok.com/pricing)
