<?php
/**
 * Helper de contraseñas seguras.
 *
 * El sistema usaba antes: salt fijo (escrito en el código) + md5(contraseña).
 * Eso es inseguro: md5 es rápido de romper por fuerza bruta y el salt es
 * el mismo para todos los usuarios (un salt fijo no protege nada).
 *
 * Ahora usamos password_hash()/password_verify() de PHP (bcrypt), que:
 *  - genera un salt distinto y aleatorio por cada contraseña,
 *  - es intencionalmente lento (dificulta ataques de fuerza bruta),
 *  - es el estándar recomendado por PHP para almacenar contraseñas.
 *
 * Como no es posible "revertir" los hashes md5 ya guardados en la base de
 * datos para volver a calcularlos con bcrypt, este helper migra cada cuenta
 * de forma automática y silenciosa la próxima vez que su dueño inicie sesión
 * correctamente (es el único momento en que sabemos la contraseña en texto
 * plano). Las cuentas que nunca vuelvan a iniciar sesión mantendrán el hash
 * viejo hasta que lo hagan, pero siguen siendo verificadas correctamente.
 */

define('LEGACY_SALT', 'a1Bz20ydqelm8m1wql');

/**
 * Genera un hash seguro (bcrypt) listo para guardar en la columna `contraseña`.
 */
function hashPasswordSeguro(string $contraseñaPlano): string
{
    return password_hash($contraseñaPlano, PASSWORD_BCRYPT);
}

/**
 * Verifica una contraseña en texto plano contra el valor guardado en la BD.
 * Si el valor guardado todavía es del esquema viejo y coincide, lo migra
 * a bcrypt automáticamente (requiere la conexión mysqli y el id del usuario
 * para poder actualizar la fila).
 */
function verificarPasswordSeguro(string $contraseñaPlano, string $hashGuardado, mysqli $conn, int $idUsuario): bool
{
    // ¿Ya es un hash bcrypt (formato nuevo)? password_get_info lo detecta.
    $info = password_get_info($hashGuardado);
    if ($info['algo'] !== null && $info['algo'] !== 0) {
        return password_verify($contraseñaPlano, $hashGuardado);
    }

    // Si no, es el hash legado: salt fijo + md5(contraseña).
    $hashLegado = LEGACY_SALT . md5($contraseñaPlano);

    // hash_equals() compara en tiempo constante (evita "timing attacks").
    if (hash_equals($hashGuardado, $hashLegado)) {
        // Coincide: migramos esta cuenta a bcrypt de una vez.
        $nuevoHash = hashPasswordSeguro($contraseñaPlano);
        $stmt = $conn->prepare("UPDATE usuario SET contraseña = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevoHash, $idUsuario);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    return false;
}