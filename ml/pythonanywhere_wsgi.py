# WSGI configuration file for PythonAnywhere
# 
# Coloca este archivo en: /var/www/username_pythonanywhere_com_wsgi.py
# (reemplaza 'username' con tu nombre de usuario de PythonAnywhere)

import sys
import os

# Agregar el directorio de la aplicación al path
path = '/home/username/vissapp-ml'  # Cambiar 'username' por tu usuario
if path not in sys.path:
    sys.path.append(path)

# Configurar variables de entorno
os.environ['PYTHONUNBUFFERED'] = '1'

# Importar la aplicación FastAPI
from app import app as application

# Para FastAPI necesitamos usar ASGI, no WSGI
# PythonAnywhere soporta ASGI con uvicorn
# Configurar en el dashboard de PythonAnywhere:
# - Python version: 3.10
# - Source code: /home/username/vissapp-ml
# - Working directory: /home/username/vissapp-ml
# - WSGI configuration file: este archivo
