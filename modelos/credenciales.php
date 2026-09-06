<?php
/**
 * Credenciales reales de SMTP/API y JWT.
 * Este archivo NO debe subirse a git (ya está en .gitignore).
 */

define('EMAIL_METHOD', 'api'); // usamos la API HTTP de Brevo, no SMTP directo

define('SMTP_ENABLED', true); // se deja true por compatibilidad, pero no se usa mientras EMAIL_METHOD sea 'api'

define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER', 'PEGA-AQUI-SI-ALGUN-DIA-USAS-SMTP'); // no se usa con EMAIL_METHOD=api
define('SMTP_PASS', 'PEGA-AQUI-SI-ALGUN-DIA-USAS-SMTP'); // no se usa con EMAIL_METHOD=api

// La clave API que generaste en la pestaña "API Keys" (la de "intecapp-api")
define('BREVO_API_KEY', '');

// Debe ser EXACTAMENTE el correo que aparece como "Verificado" en
// Remitentes, dominio, IP > Remitentes (en tu caso: sicmendezr@gmail.com)
define('MAIL_FROM', 'sp.space.deveolopers@gmail.com');
define('MAIL_FROM_NAME', 'INTECAP Quiché - Sistema de Gestión');

// JWT_SECRET nuevo, generado aleatoriamente (el anterior quedó expuesto
// en el historial de GitHub y ya no debe usarse).
define('JWT_SECRET', 'f3f2dc124b44cf676f4986a7aa73e8a265008e07288151b690dc8501ee078347');