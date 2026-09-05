<?php
// controladores/marcar_leidas.php

if (session_status() === PHP_SESSION_NONE) session_start();

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

include '../modelos/db.php';

ob_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_intecap'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$id_usuario = intval($_SESSION['admin_intecap']);
mysqli_query($conn, "UPDATE notificaciones SET leida = 1 WHERE id_destino = $id_usuario AND leida = 0");

echo json_encode(['ok' => true]);
?>