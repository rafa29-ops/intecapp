<?php
/**
 * Envío de correos del sistema.
 *
 * Prioridad:
 *   1) Si SMTP_ENABLED está activo en config.php y PHPMailer está
 *      instalado, se envía por SMTP real (Gmail, tu hosting, etc).
 *      Esto SÍ entrega correos reales, incluso en localhost/XAMPP.
 *   2) Si no, cae a la función mail() nativa de PHP (rara vez funciona
 *      en XAMPP/local porque no hay servidor de correo instalado).
 *
 * Todos los errores de envío quedan registrados en el log de errores
 * de PHP (busca "MAILER:" en el error log de Apache/XAMPP) para poder
 * diagnosticar por qué un correo no llegó.
 */

require_once __DIR__ . '/config.php';

// Intenta cargar PHPMailer (vía Composer o instalación manual).
$__phpmailer_disponible = false;

if (SMTP_ENABLED) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        // Instalado con: composer require phpmailer/phpmailer
        require_once __DIR__ . '/vendor/autoload.php';
        $__phpmailer_disponible = class_exists('PHPMailer\PHPMailer\PHPMailer');
    } elseif (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
        // Instalación manual: modelos/PHPMailer/src/{PHPMailer,SMTP,Exception}.php
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        $__phpmailer_disponible = class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
}

/**
 * Envía un correo en formato HTML.
 *
 * @param string $destino    Correo del destinatario.
 * @param string $asunto     Asunto del correo.
 * @param string $cuerpoHtml Cuerpo en HTML.
 * @return bool true si el sistema aceptó/envió el correo, false si falló.
 */
function enviarCorreo(string $destino, string $asunto, string $cuerpoHtml): bool
{
    global $__phpmailer_disponible;

    // Método por API HTTP (Brevo): evita por completo los puertos SMTP
    // (25/465/587), que muchos routers/ISP bloquean. Usa HTTPS (443),
    // el mismo puerto que cualquier página web.
    if (defined('EMAIL_METHOD') && EMAIL_METHOD === 'api') {
        return enviarCorreoBrevoAPI($destino, $asunto, $cuerpoHtml);
    }

    if (SMTP_ENABLED && $__phpmailer_disponible) {
        return enviarCorreoSMTP($destino, $asunto, $cuerpoHtml);
    }

    if (SMTP_ENABLED && !$__phpmailer_disponible) {
        error_log('MAILER: SMTP_ENABLED está en true pero PHPMailer no está instalado. '
            . 'Instálalo con "composer require phpmailer/phpmailer" dentro de la carpeta modelos/, '
            . 'o copia la carpeta src/ de PHPMailer a modelos/PHPMailer/src/. '
            . 'Usando mail() nativa como respaldo (probablemente no entregue el correo).');
    }

    return enviarCorreoNativo($destino, $asunto, $cuerpoHtml);
}

/**
 * Envío vía API HTTP de Brevo (https://api.brevo.com), usando cURL sobre
 * HTTPS (puerto 443). No requiere abrir ni depender de los puertos SMTP,
 * que routers/ISP suelen bloquear.
 *
 * Requiere en credenciales.php:
 *   define('EMAIL_METHOD', 'api');
 *   define('BREVO_API_KEY', 'xkeysib-...');
 */
function enviarCorreoBrevoAPI(string $destino, string $asunto, string $cuerpoHtml): bool
{
    if (!defined('BREVO_API_KEY') || BREVO_API_KEY === '') {
        error_log('MAILER: EMAIL_METHOD=api pero falta definir BREVO_API_KEY en credenciales.php');
        return false;
    }

    $payload = json_encode([
        'sender'      => ['name' => MAIL_FROM_NAME, 'email' => MAIL_FROM],
        'to'          => [['email' => $destino]],
        'subject'     => $asunto,
        'htmlContent' => $cuerpoHtml,
    ]);

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . BREVO_API_KEY,
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $respuesta  = curl_exec($ch);
    $codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errorCurl  = curl_error($ch);
    curl_close($ch);

    if ($errorCurl !== '') {
        error_log('MAILER: Error de cURL llamando a la API de Brevo: ' . $errorCurl);
        return false;
    }

    // Brevo responde 201 Created cuando acepta el correo para envío.
    if ($codigoHttp >= 200 && $codigoHttp < 300) {
        return true;
    }

    error_log('MAILER: Brevo API respondió código ' . $codigoHttp . ': ' . $respuesta);
    return false;
}

/**
 * Envío real vía SMTP usando PHPMailer.
 */
function enviarCorreoSMTP(string $destino, string $asunto, string $cuerpoHtml): bool
{
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE === 'ssl'
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($destino);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('MAILER: Error enviando correo por SMTP a ' . $destino . ': ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Respaldo con la función mail() nativa de PHP.
 */
function enviarCorreoNativo(string $destino, string $asunto, string $cuerpoHtml): bool
{
    $cabeceras   = [];
    $cabeceras[] = 'MIME-Version: 1.0';
    $cabeceras[] = 'Content-Type: text/html; charset=UTF-8';
    $cabeceras[] = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>';
    $cabeceras[] = 'Reply-To: ' . MAIL_FROM;
    $cabeceras[] = 'X-Mailer: PHP/' . phpversion();

    $asuntoCodificado = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
    $enviado = @mail($destino, $asuntoCodificado, $cuerpoHtml, implode("\r\n", $cabeceras));

    if (!$enviado) {
        $error = error_get_last();
        error_log('MAILER: mail() nativa falló al enviar a ' . $destino . '. '
            . 'Detalle: ' . ($error['message'] ?? 'sin detalle. '
            . 'En XAMPP/local esto es normal: no hay servidor de correo configurado. '
            . 'Activa SMTP_ENABLED en config.php para enviar de verdad.'));
    }

    return $enviado;
}

/**
 * Plantilla base para los correos de recuperación / avisos de seguridad.
 */
function plantillaCorreo(string $titulo, string $mensajeHtml): string
{
    return '
    <div style="font-family: Poppins, Arial, sans-serif; background:#f2f5fa; padding:32px;">
        <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 10px 30px rgba(10,61,143,.15);">
            <div style="background:linear-gradient(135deg,#0a3d8f,#1e88e5);padding:22px 28px;">
                <h2 style="margin:0;color:#fff;font-size:18px;">INTECAP Quiché</h2>
                <p style="margin:4px 0 0;color:rgba(255,255,255,.85);font-size:12px;">Sistema de Gestión</p>
            </div>
            <div style="padding:28px;color:#222;font-size:14px;line-height:1.6;">
                <h3 style="margin-top:0;color:#0a3d8f;">' . htmlspecialchars($titulo) . '</h3>
                ' . $mensajeHtml . '
            </div>
            <div style="padding:16px 28px;background:#f7f9fc;color:#8a94a6;font-size:11px;">
                Este es un mensaje automático, por favor no respondas a este correo.
            </div>
        </div>
    </div>';
}