<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('db.php');
include('config.php');

// Validar que el usuario esté autenticado
if(!isset($_SESSION['admin_intecap']) || trim($_SESSION['admin_intecap']) == ''){
    header('location: ' . BASE_URL . '/index.php');
    exit();
}

// Obtener datos del usuario (consulta preparada)
$stmtUser = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
$stmtUser->bind_param("i", $_SESSION['admin_intecap']);
$stmtUser->execute();
$user = $stmtUser->get_result()->fetch_assoc();
$stmtUser->close();

// Validar que solo Admin y Mantenimiento puedan cambiar el estado
$cargo = trim($user['cargo']);
if ($cargo != "Admin" && $cargo != "Mantenimiento") {
    echo "<script type='text/javascript'>alert('No tienes permiso para cambiar el estado.');</script>";
    echo "<script>history.back();</script>";
    exit();
}

$id_mantenimiento = isset($_REQUEST['id_mantenimiento']) ? (int) $_REQUEST['id_mantenimiento'] : (int) ($_POST['id_mantenimiento'] ?? 0);

// Obtener el estado actual del filtro para mantenerlo después del cambio
$estado_filtro = isset($_REQUEST['estado']) ? htmlspecialchars($_REQUEST['estado']) : 'General';

// Datos del mantenimiento ANTES de marcarlo como realizado (para saber a
// quién avisar después).
$stmtMant = $conn->prepare("SELECT descripcion, id_solicitante FROM mantenimiento WHERE id = ?");
$stmtMant->bind_param("i", $id_mantenimiento);
$stmtMant->execute();
$mantenimiento = $stmtMant->get_result()->fetch_assoc();
$stmtMant->close();

// Registrar la fecha actual cuando se marca como realizado
$f_realizado = date('Y-m-d');

$stmtUpdate = $conn->prepare("UPDATE mantenimiento SET estado = 'Realizada', f_realizado = ? WHERE id = ?");
$stmtUpdate->bind_param("si", $f_realizado, $id_mantenimiento);
$stmtUpdate->execute();
$stmtUpdate->close();

// Notificar que el mantenimiento ya se realizó a:
//   - quien lo solicitó originalmente (Instructor, Mantenimiento o Admin), y
//   - TODOS los usuarios con cargo 'Admin', siempre, para que el Admin
//     esté al tanto de todo lo que pasa (lo haya pedido él o cualquier
//     otro usuario).
// La condición "id = ? OR cargo = 'Admin'" en SQL ya evita duplicados:
// si el propio solicitante ES Admin, solo aparece una vez en el
// resultado (no recibe el aviso dos veces).
if ($mantenimiento) {
    $mensaje = "Mantenimiento realizado: " . $mantenimiento['descripcion'];
    $idSolicitante = !empty($mantenimiento['id_solicitante']) ? (int) $mantenimiento['id_solicitante'] : 0;

    $stmtDestinos = $conn->prepare(
        "SELECT id FROM usuario WHERE estado = 'activo' AND (id = ? OR cargo = 'Admin')"
    );
    $stmtDestinos->bind_param("i", $idSolicitante);
    $stmtDestinos->execute();
    $destinos = $stmtDestinos->get_result();

    $stmtNoti = $conn->prepare("INSERT INTO notificaciones (mensaje, id_destino, leida) VALUES (?, ?, 0)");
    while ($u = $destinos->fetch_assoc()) {
        $idDestino = (int) $u['id'];
        $stmtNoti->bind_param("si", $mensaje, $idDestino);
        $stmtNoti->execute();
    }
    $stmtNoti->close();
    $stmtDestinos->close();
}

echo "<script type='text/javascript'>alert('Se cambió de estado correctamente!');</script>";
// Redirigir manteniendo el filtro activo
echo "<script>document.location='../vistas/ADMIN/MANTENIMIENTO.php?estado=" . $estado_filtro . "'</script>";

?>