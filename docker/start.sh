#!/bin/bash
# Script de inicio para contenedor PHP + Nginx

# Iniciar PHP-FPM en background
php-fpm -D

# Iniciar Nginx en foreground
nginx -g 'daemon off;'
