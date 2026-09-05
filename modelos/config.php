<?php
/**
 * Configuración general del sitio.
 * Ajusta BASE_URL según dónde esté publicado el proyecto
 * (en tu XAMPP local normalmente es http://localhost/intecapp).
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/intecapp');
}

// Minutos de validez del enlace de recuperación de contraseña.
if (!defined('RECUPERACION_MINUTOS_VALIDEZ')) {
    define('RECUPERACION_MINUTOS_VALIDEZ', 30);
}

/**
 * ── Credenciales de correo (SMTP) y secreto JWT ────────────────────────
 * IMPORTANTE: las credenciales reales YA NO están en este archivo.
 * Están en modelos/credenciales.php, que está en .gitignore y por lo
 * tanto NUNCA se sube a GitHub. Si ese archivo no existe, cópialo desde
 * modelos/credenciales.example.php y llena tus datos ahí.
 */
$credenciales = __DIR__ . '/credenciales.php';

if (file_exists($credenciales)) {
    require_once $credenciales;
} else {
    // Si falta el archivo de credenciales, se deshabilita el envío real
    // por SMTP para no romper el sitio, y queda registrado en el log.
    error_log('CONFIG: No se encontró modelos/credenciales.php. '
        . 'Copia modelos/credenciales.example.php a modelos/credenciales.php '
        . 'y llena tus datos reales de SMTP y tu propio JWT_SECRET.');
    if (!defined('SMTP_ENABLED')) {
        define('SMTP_ENABLED', false);
    }
    if (!defined('MAIL_FROM')) {
        define('MAIL_FROM', 'no-reply@example.com');
    }
    if (!defined('MAIL_FROM_NAME')) {
        define('MAIL_FROM_NAME', 'INTECAP Quiché - Sistema de Gestión');
    }
}

// Si por alguna razón credenciales.php no definió JWT_SECRET (archivo
// viejo sin actualizar, por ejemplo), se genera uno de emergencia por
// petición. OJO: esto invalida los enlaces de recuperación ya enviados
// cada vez que se recargue sin JWT_SECRET fijo; agrega la constante en
// tu credenciales.php lo antes posible.
if (!defined('JWT_SECRET')) {
    error_log('CONFIG: JWT_SECRET no está definido en credenciales.php. '
        . 'Agrega define(\'JWT_SECRET\', \'...\'); con una cadena aleatoria propia.');
    define('JWT_SECRET', bin2hex(random_bytes(32)));
}