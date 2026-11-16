<?php
/**
 * Configuración global del sistema
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'boxeo');

// URL base: ajustar según despliegue
define('BASE_URL', 'http://localhost/boxeocambio/boxeo/');

// Zona horaria
date_default_timezone_set('America/Caracas');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
