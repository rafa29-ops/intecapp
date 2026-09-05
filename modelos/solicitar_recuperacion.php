<?php
/**
 * Paso 1 de la recuperación de contraseña.
 *
 * Reglas (sin cambios respecto al flujo anterior):
 *  - Si el usuario no existe -> error.
 *  - Si el usuario existe pero el correo NO coincide con el correo
 *    registrado en la cuenta -> error para quien lo solicitó, y además
 *    se manda una alerta de seguridad al correo REAL del dueño de la
 *    cuenta avisando que alguien intentó recuperar su contraseña con
 *    un correo distinto.
 *  - Si el usuario existe y el correo SÍ coincide -> se genera un
 *    token de un solo uso (RECUPERACION_MINUTOS_VALIDEZ minutos de
 *    validez) y se envía un correo con el enlace para cambiar la
 *    contraseña.
 *
 * CAMBIO respecto a la versión anterior:
 *  Antes el token era aleatorio y se guardaba en la tabla
 *  `recuperacion_password` para poder validarlo después.
 *  Ahora el token es un JWT (HS256) que se autovalida con firma +
 *  expiración: ya no se inserta nada en la base de datos. El "single
 *  use" se logra incluyendo un fragmento del hash de la contraseña
 *  actual como claim (`phv`): en cuanto el usuario cambia su
 *  contraseña, cualquier link viejo deja de servir automáticamente,
 *  aunque no haya expirado.
 */

include('db.php');
include('mailer.php');
include('jwt_helper.php');

$volver = '../vistas/LOGIN/recuperacion de contraseña.php';

$nom_usuario = trim($_POST['nom_usuario'] ?? '');
$correo      = trim($_POST['correo'] ?? '');

if ($nom_usuario === '' || $correo === '') {
    echo "<script>alert('Debes ingresar tu usuario y tu correo.');</script>";
    echo "<script>document.location='$volver'</script>";
    exit;
}

$stmt = $conn->prepare("SELECT id, nombre, correo, contraseña FROM usuario WHERE nom_usuario = ?");
$stmt->bind_param("s", $nom_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    $stmt->close();
    echo "<script>alert('No se encontró ningún usuario con ese nombre de usuario.');</script>";
    echo "<script>document.location='$volver'</script>";
    exit;
}

$usuario = $resultado->fetch_assoc();
$stmt->close();

$correoReal = $usuario['correo'];

// ── Caso: el usuario aún no tiene correo cargado en el sistema ──────
if (empty($correoReal)) {
    echo "<script>alert('Esta cuenta no tiene un correo asociado todavía. Contacta a un administrador para poder recuperar tu contraseña.');</script>";
    echo "<script>document.location='$volver'</script>";
    exit;
}

// ── Caso: el correo ingresado NO coincide con el de la cuenta ──────
if (strcasecmp($correo, $correoReal) !== 0) {

    // Aviso de seguridad al dueño real de la cuenta. (Sin cambios.)
    $cuerpo = plantillaCorreo(
        'Intento de recuperación de contraseña',
        '<p>Hola <strong>' . htmlspecialchars($usuario['nombre']) . '</strong>,</p>
         <p>Alguien intentó solicitar un cambio de contraseña para tu cuenta
         (<strong>' . htmlspecialchars($nom_usuario) . '</strong>) usando un correo
         electrónico distinto al que tienes registrado.</p>
         <p><strong>No se realizó ningún cambio en tu cuenta.</strong></p>
         <p>Si fuiste tú y solo te equivocaste de correo, vuelve a intentar la
         recuperación usando el correo con el que te registraste.</p>
         <p>Si no fuiste tú, no necesitas hacer nada, pero te recomendamos
         avisar a un administrador del sistema.</p>'
    );
    enviarCorreo($correoReal, 'Alerta de seguridad - Intento de recuperación de contraseña', $cuerpo);

    echo "<script>alert('El correo ingresado no coincide con el correo registrado para este usuario.');</script>";
    echo "<script>document.location='$volver'</script>";
    exit;
}

// ── Caso correcto: generar token JWT y enviar enlace de recuperación ────

// Fragmento del hash de la contraseña actual. Es lo que hace que el
// token deje de servir en cuanto el usuario cambie su contraseña,
// sin necesitar tabla ni columna "usado".
$fragmentoHash = substr($usuario['contraseña'] ?? '', 0, 12);

$token = generarJWT(
    [
        'uid' => (int) $usuario['id'],
        'phv' => $fragmentoHash,
    ],
    JWT_SECRET,
    RECUPERACION_MINUTOS_VALIDEZ * 60
);

$enlace = BASE_URL . '/vistas/LOGIN/restablecer_contrasena.php?token=' . urlencode($token);

$cuerpo = plantillaCorreo(
    'Solicitud de cambio de contraseña',
    '<p>Hola <strong>' . htmlspecialchars($usuario['nombre']) . '</strong>,</p>
     <p>Recibimos una solicitud para cambiar la contraseña de tu cuenta
     (<strong>' . htmlspecialchars($nom_usuario) . '</strong>).</p>
     <p style="text-align:center;margin:26px 0;">
        <a href="' . htmlspecialchars($enlace) . '"
           style="background:#1565c0;color:#fff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:600;display:inline-block;">
           Cambiar mi contraseña
        </a>
     </p>
     <p>Este enlace es válido por ' . RECUPERACION_MINUTOS_VALIDEZ . ' minutos y solo puede usarse una vez.</p>
     <p><strong>Si tú fuiste quien solicitó este cambio</strong>, haz clic en el
     botón de arriba.</p>
     <p><strong>Si tú NO solicitaste este cambio</strong>, ignora este correo:
     tu contraseña seguirá siendo la misma y no es necesario que hagas nada más.</p>'
);
enviarCorreo($correoReal, 'Recuperación de contraseña - INTECAP Quiché', $cuerpo);

echo "<script>alert('Te enviamos un correo con el enlace para cambiar tu contraseña. Revisa tu bandeja de entrada (y spam).');</script>";
echo "<script>document.location='../index.php'</script>";