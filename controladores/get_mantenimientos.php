<?php
// controladores/get_mantenimientos.php
// Estructura: intecapp/controladores/ y intecapp/modelos/db.php

if (session_status() === PHP_SESSION_NONE) session_start();

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

include '../modelos/db.php';

ob_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_intecap'])) {
    echo json_encode(['filas' => []]);
    exit;
}

$id_sesion = intval($_SESSION['admin_intecap']);
$user      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM usuario WHERE id = $id_sesion"));
$estado    = isset($_GET['estado']) ? $_GET['estado'] : 'General';

if ($estado == 'Pendiente') {
    $sql = "SELECT m.*, m.id as id_mantenimiento, m.estado as estado_m,
                   t.nombre_taller, u.nombre, u.cargo, u2.nombre AS nombre_reporta
            FROM mantenimiento m
            INNER JOIN talleres t ON m.id_taller = t.id
            INNER JOIN usuario  u ON u.id = m.id_encargado
            LEFT JOIN usuario u2 ON u2.id = m.id_solicitante
            WHERE m.estado = 'no realizado'
            ORDER BY m.f_reporte DESC, m.id DESC";

} elseif ($estado == 'Realizados') {
    $sql = "SELECT m.*, m.id as id_mantenimiento, m.estado as estado_m,
                   t.nombre_taller, u.nombre, u.cargo, u2.nombre AS nombre_reporta
            FROM mantenimiento m
            INNER JOIN talleres t ON m.id_taller = t.id
            INNER JOIN usuario  u ON u.id = m.id_encargado
            LEFT JOIN usuario u2 ON u2.id = m.id_solicitante
            WHERE m.estado = 'Realizada'
            ORDER BY m.f_reporte DESC, m.id DESC";

} elseif ($estado == 'Mes') {
    $mes = date("m");
    $ano = date("Y");
    $sql = "SELECT m.*, m.id as id_mantenimiento, m.estado as estado_m,
                   t.nombre_taller, u.nombre, u.cargo, u2.nombre AS nombre_reporta
            FROM mantenimiento m
            INNER JOIN talleres t ON m.id_taller = t.id
            INNER JOIN usuario  u ON u.id = m.id_encargado
            LEFT JOIN usuario u2 ON u2.id = m.id_solicitante
            WHERE MONTH(f_reporte) = $mes AND YEAR(f_reporte) = $ano
            ORDER BY m.f_reporte DESC, m.id DESC";

} else {
    $sql = "SELECT m.*, m.id as id_mantenimiento, m.estado as estado_m,
                   t.nombre_taller, u.nombre, u.cargo, u2.nombre AS nombre_reporta
            FROM mantenimiento m
            INNER JOIN talleres t ON m.id_taller = t.id
            INNER JOIN usuario  u ON u.id = m.id_encargado
            LEFT JOIN usuario u2 ON u2.id = m.id_solicitante
            ORDER BY m.f_reporte DESC, m.id DESC";
}

$result = mysqli_query($conn, $sql);
$filas  = [];

while ($row = mysqli_fetch_assoc($result)) {
    $id          = $row['id_mantenimiento'];
    $anio        = date("Y", strtotime($row['f_reporte']));
    $f_reporte   = date("d/m/Y", strtotime($row['f_reporte']));
    $f_realizado = ($row['estado_m'] == 'Realizada' && !empty($row['f_realizado']))
                   ? date("d/m/Y", strtotime($row['f_realizado'])) : '--';

    $estadoRow = isset($row['estado_m']) ? trim((string)$row['estado_m']) : '';
    if ($estadoRow === '' || $estadoRow === '0') {
        $estadoRow = 'no realizado';
    }
    $url_cambio = "/intecapp/modelos/cambiar_estado.php?id_mantenimiento=$id&estado=" . urlencode($estado);
    $celda_estado = ($estadoRow === 'no realizado')
        ? "<a href='$url_cambio' onclick=\"return confirm('¿Cambiar estado a Realizada?');\">No realizado</a>"
        : htmlspecialchars($estadoRow);

    $acciones = '';
    if ($user['cargo'] == 'Admin' || $user['cargo'] == 'Instructor' || $user['cargo'] == 'Mantenimiento') {
        $acciones .= "<button class='btn btn-primary btn-sm' onclick='comentario($id)'><i class='fas fa-comments'></i></button> ";
    }
    if ($user['cargo'] == 'Admin') {
        $acciones .= "<button class='btn btn-warning btn-sm' onclick='editar($id)'><i class='fas fa-edit'></i></button> ";
        $acciones .= "<button class='btn btn-danger btn-sm' onclick='eliminar($id)'><i class='fas fa-trash'></i></button>";
    }

    $filas[] = [
        'anio'           => $anio,
        'nombre'         => htmlspecialchars($row['nombre']),
        'nombre_reporta' => htmlspecialchars($row['nombre_reporta']),
        'taller'         => htmlspecialchars($row['nombre_taller']),
        'f_reporte'      => $f_reporte,
        'f_realizado'    => $f_realizado,
        'descripcion'    => htmlspecialchars($row['descripcion']),
        'estado'         => $celda_estado,
        'acciones'      => $acciones,
    ];
}

echo json_encode(['filas' => $filas]);
?>