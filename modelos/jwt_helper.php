<?php
/**
 * JWT minimalista (HS256) sin dependencias externas (sin Composer).
 *
 * Sustituye la tabla `recuperacion_password`: el token de recuperación
 * ya no se guarda en la base de datos, se autovalida con firma HMAC +
 * expiración incluida en el propio token.
 *
 * No lo uses para nada que requiera más seguridad que esto (solo es para
 * el flujo de recuperación de contraseña). Si algún día necesitas JWT
 * "de verdad" con más claims/algoritmos, usa la librería firebase/php-jwt
 * vía Composer. Para este caso, con HS256 + hash_hmac es suficiente y
 * evita instalar dependencias extra.
 */

function jwtBase64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function jwtBase64UrlDecode(string $data): string
{
    $resto = strlen($data) % 4;
    if ($resto) {
        $data .= str_repeat('=', 4 - $resto);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Genera un JWT firmado con HS256.
 *
 * @param array  $payload         Datos a incluir (ej. ['uid' => 5, 'phv' => 'abc123']).
 * @param string $secret          JWT_SECRET definido en credenciales.php.
 * @param int    $segundosValidez Segundos hasta que expire el token.
 */
function generarJWT(array $payload, string $secret, int $segundosValidez): string
{
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $payload['exp'] = time() + $segundosValidez;
    $payload['iat'] = time();

    $headerCod  = jwtBase64UrlEncode(json_encode($header));
    $payloadCod = jwtBase64UrlEncode(json_encode($payload));

    $firma    = hash_hmac('sha256', "$headerCod.$payloadCod", $secret, true);
    $firmaCod = jwtBase64UrlEncode($firma);

    return "$headerCod.$payloadCod.$firmaCod";
}

/**
 * Verifica un JWT. Retorna el payload (array) si es válido y no ha
 * expirado, o null si la firma no coincide, el formato es inválido,
 * o ya venció.
 */
function verificarJWT(string $token, string $secret): ?array
{
    $partes = explode('.', $token);
    if (count($partes) !== 3) {
        return null;
    }

    [$headerCod, $payloadCod, $firmaCod] = $partes;

    $firmaEsperada = jwtBase64UrlEncode(
        hash_hmac('sha256', "$headerCod.$payloadCod", $secret, true)
    );

    // Comparación en tiempo constante para evitar timing attacks.
    if (!hash_equals($firmaEsperada, $firmaCod)) {
        return null;
    }

    $payload = json_decode(jwtBase64UrlDecode($payloadCod), true);

    if (!is_array($payload) || !isset($payload['exp'])) {
        return null;
    }

    if (time() > (int) $payload['exp']) {
        return null;
    }

    return $payload;
}