<?php
// includes/config.php

// Configuración de la Base de Datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Cambiar en producción
define('DB_PASS', '');     // Cambiar en producción
define('DB_NAME', 'licitaciones_tjaech');

// Configuración de la Aplicación
define('APP_URL', 'http://localhost/licitaciones_TJAECH'); // Cambiar en producción a la URL pública final
define('APP_NAME', 'Portal de Licitaciones TJAECH');

// Directorio de subidas (asegúrate que tenga permisos de escritura por Apache)
define('UPLOAD_DIR', __DIR__ . '/../uploads/pdfs/');

// Zonas horarias y configuraciones regionales
date_default_timezone_set('America/Mexico_City');
setlocale(LC_TIME, 'es_MX.UTF-8', 'es_MX', 'esp');

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
