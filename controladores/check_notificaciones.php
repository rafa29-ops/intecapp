<?php
// controladores/check_notificaciones.php
// Estructura: intecapp/controladores/ y intecapp/modelos/db.php

if (session_status() === PHP_SESSION_NONE) session_start();

// Silenciar CUALQUIER output antes del JSON (warnings, notices, etc.)
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

include '../modelos/db.php';

// Limpiar cualquier output que haya generado db.php
ob_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_intecap'])) {
    echo json_encode(['total' => 0, 'notificaciones' => []]);
    exit;
}

$id_usuario = intval($_SESSION['admin_intecap']);

// Se eliminó la restricción de cargo: las notificaciones llegan a CUALQUIER usuario
$result = mysqli_query($conn,
    "SELECT id, mensaje, fecha
     FROM notificaciones
     WHERE id_destino = $id_usuario AND leida = 0
     ORDER BY fecha DESC"
);

$notificaciones = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notificaciones[] = $row;
}

echo json_encode([
    'total'          => count($notificaciones),
    'notificaciones' => $notificaciones
]);
?>