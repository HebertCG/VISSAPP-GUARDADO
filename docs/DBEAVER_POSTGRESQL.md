# Conectar DBeaver a PostgreSQL de Docker

## 📋 Credenciales de PostgreSQL (Docker)

```
Host: localhost
Port: 5432
Database: vissapp
Username: vissapp_user
Password: vissapp_password
```

## 🔧 Pasos en DBeaver:

### 1. Nueva Conexión
1. Click en **"Nueva Conexión"** (ícono de enchufe con +)
2. Seleccionar **"PostgreSQL"**
3. Click **"Siguiente"**

### 2. Configurar Conexión
```
Host: localhost
Port: 5432
Database: vissapp
Usuario: vissapp_user
Contraseña: vissapp_password
```

### 3. Test de Conexión
1. Click en **"Test Connection"**
2. Si es la primera vez, DBeaver descargará el driver de PostgreSQL
3. Deberías ver: **"Connected"** ✅

### 4. Finalizar
1. Click **"Finalizar"**
2. Verás la conexión en el panel izquierdo

### 5. Ver Tablas
1. Expandir: **vissapp** → **Schemas** → **public** → **Tables**
2. Verás las 3 tablas:
   - `usuarios` (2 registros)
   - `personas` (0 registros)
   - `notifications` (0 registros)

---

## 📊 Datos Actuales:

### Tabla `usuarios`:
- **admin** / admin123
- **soporte** / soporte123

### Tabla `personas`:
- Vacía (puedes agregar personas desde la aplicación)

### Tabla `notifications`:
- Vacía (se llenará automáticamente cuando cambien datos)

---

## ✅ Verificación

Una vez conectado, ejecuta esta query en DBeaver:

```sql
SELECT * FROM usuarios;
```

Deberías ver los 2 usuarios.
