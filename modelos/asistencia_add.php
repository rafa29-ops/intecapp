<?php
session_start();
include('db.php');

$fecha = $_POST["fecha"];

$id_usuario = $_POST['id_usuario'];

$id_evento = $_POST["id_evento"];
$hora_entrada = $_POST['hora_entrada'];
$hora_salida = $_POST['hora_salida'];

mysqli_query($conn,"INSERT INTO asistencia (fecha, id_usuario, id_evento, hora_entrada, hora_salida) 
    VALUES('$fecha','$id_usuario','$id_evento','$hora_entrada', '$hora_salida')")or die(mysqli_error($con));
 
echo "<script type='text/javascript'>alert('Asistencia registrada correctamente.');</script>";
echo "<script>document.location='../vistas/ADMIN/principal.php'</script>";

?>
