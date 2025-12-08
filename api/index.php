<?php
/**
 * Punto de entrada para Vercel Serverless
 * 
 * Este archivo actúa como router principal para todas las requests
 * en el entorno serverless de Vercel.
 */

// Cargar autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Iniciar sesión (usando cookies en lugar de sesiones de servidor)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el index.php original
require_once __DIR__ . '/../public/index.php';
