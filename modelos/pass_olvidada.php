<?php
/**
 * Paso 2 de la recuperación de contraseña.
 *
 * CAMBIO respecto a la versión anterior:
 *  Antes se validaba el token contra la tabla `recuperacion_password`
 *  (columnas token / expira / usado). Ahora el token es un JWT que se
 *  autovalida (firma + expiración) y el "single use" se logra
 *  comparando un fragmento del hash de la contraseña actual guardado
 *  dentro del propio token: si ya cambiaste la contraseña con ese
 *  enlace (o de cualquier otra forma), el token deja de ser válido
 *  aunque todavía no haya expirado.
 *
 * CAMBIO DE SEGURIDAD:
 *  Al confirmar el cambio de contraseña se destruye cualquier sesión
 *  activa (por si el navegador conservaba una sesión iniciada) y se
 *  fuerza no-cache, para que sí o sí sea obligatorio volver a iniciar
 *  sesión con la contraseña nueva.
 */

session_start();

// Evita que el navegador cachee esta respuesta o la reenvíe con el botón
// "atrás" (bfcache), lo que podía dar la sensación de "burlar" el cambio.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

include('db.php');
include('config.php');
include('password_helper.php');
include('jwt_helper.php');

$volverError = function ($mensaje) {
    echo "<script type='text/javascript'>alert('" . addslashes($mensaje) . "');</script>";
    echo "<script>document.location='../index.php'</script>";
    exit;
};

$token       = $_POST['token']        ?? '';
$contraseña  = $_POST['contraseña']   ?? '';
$contraseña1 = $_POST['contraseña1']  ?? '';

if ($token === '') {
    $volverError('Enlace inválido.');
}

if ($contraseña === '' || $contraseña !== $contraseña1) {
    echo "<script type='text/javascript'>alert('Las contraseñas no coinciden.');</script>";
    echo "<script>document.location='../vistas/LOGIN/restablecer_contrasena.php?token=" . urlencode($token) . "'</script>";
    exit;
}

$payload = verificarJWT($token, JWT_SECRET);

if ($payload === null || !isset($payload['uid'], $payload['phv'])) {
    $volverError('El enlace de recuperación no es válido o ya expiró. Solicita uno nuevo.');
}

$idUsuario = (int) $payload['uid'];

// Confirmar que la contraseña no haya cambiado desde que se generó el link
// (esto reemplaza la columna "usado" de la tabla vieja).
$stmt = $conn->prepare("SELECT contraseña FROM usuario WHERE id = ?");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fila || substr($fila['contraseña'], 0, 12) !== $payload['phv']) {
    $volverError('Este enlace ya fue utilizado o ya no es válido. Solicita uno nuevo.');
}

// Encriptar la nueva contraseña con hash seguro (bcrypt)
$pass = hashPasswordSeguro($contraseña);

$stmtUpdate = $conn->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
$stmtUpdate->bind_param("si", $pass, $idUsuario);
$stmtUpdate->execute();
$stmtUpdate->close();

// Por seguridad: si por cualquier motivo había una sesión activa en este
// navegador, la matamos aquí mismo. Así, aunque alguien intente "seguir
// usando" la sesión vieja después de cambiar la contraseña, no podrá:
// tendrá que iniciar sesión de nuevo con la contraseña nueva sí o sí.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametrosCookie = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametrosCookie['path'],
        $parametrosCookie['domain'],
        $parametrosCookie['secure'],
        $parametrosCookie['httponly']
    );
}
session_destroy();

// IMPORTANTE: usamos un nombre de archivo sin ñ ni acentos a propósito.
// Con ñ/acentos, la URL que arma el navegador debe llevar la "ñ" codificada
// en UTF-8 (%C3%B1); si el .php quedó guardado con otro encoding (algo muy
// común en Windows/XAMPP), esa codificación no coincide con el nombre real
// del archivo en disco y Apache responde 404 "Not Found" aunque el archivo
// exista. Evitamos todo eso usando solo caracteres ASCII en el nombre.
//
// El nombre debe coincidir EXACTAMENTE (mayúsculas/minúsculas incluidas)
// con el archivo que subas al servidor.
echo "<script>document.location='../vistas/LOGIN/cambio_password_exitoso.html'</script>";
exit;