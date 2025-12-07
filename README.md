# VissApp v3 - Sistema de Gestión de Visas

Sistema web para gestión de personas con visas, notificaciones automatizadas y clasificación de riesgo de vencimiento mediante Machine Learning.

## 🚀 Características

- **Gestión de Personas**: CRUD completo con extracción automática de datos desde PDFs
- **Notificaciones**: Envío automatizado de SMS (Twilio) y Email (Mailgun)
- **Machine Learning**: Clasificación de riesgo de vencimiento de visas
- **Testing**: Pruebas unitarias, integración y funcionales
- **Seguridad**: Análisis con OWASP ZAP
- **Containerización**: Docker para desarrollo y producción

## 📋 Requisitos

- PHP 8.0+
- MySQL 5.7+ / PostgreSQL 13+
- Composer
- Docker & Docker Compose (opcional)
- Python 3.9+ (para microservicio ML)

## 🛠️ Instalación Local

```bash
# Clonar repositorio
git clone https://github.com/HebertCG/VISSAPP-GUARDADO.git
cd VissApp_v3

# Instalar dependencias PHP
composer install

# Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales

# Importar base de datos
mysql -u root -p < database/schema.sql

# Iniciar servidor local
php -S localhost:8000 -t public
```

## 🐳 Docker

```bash
docker-compose up -d
```

## 🧪 Testing

```bash
# Ejecutar todas las pruebas
composer test

# Pruebas unitarias
composer test:unit

# Pruebas de integración
composer test:integration

# Coverage
composer test:coverage
```

## 📊 Machine Learning

El microservicio de clasificación de riesgo está en `/ml`:

```bash
cd ml
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install -r requirements.txt
python app.py
```

## 🔒 Seguridad

Análisis con OWASP ZAP:
```bash
docker run -t owasp/zap2docker-stable zap-baseline.py -t http://localhost:8000
```

## 📁 Estructura del Proyecto

```
VissApp_v3/
├── app/
│   ├── controllers/    # Controladores MVC
│   ├── models/         # Modelos de datos
│   └── services/       # Lógica de negocio
├── config/             # Configuraciones
├── public/             # Punto de entrada
├── views/              # Plantillas HTML/PHP
├── tests/              # Pruebas automatizadas
├── ml/                 # Microservicio ML (Python)
└── docker/             # Configuración Docker
```

## 🌐 Deploy

- **Railway**: [Instrucciones](docs/deploy-railway.md)
- **Render**: [Instrucciones](docs/deploy-render.md)

## 📝 Licencia

MIT

## 👥 Autor

Hebert CG
