<?php
//session_start();
include('db.php');

$id = $_POST["id"];
$anio = $_POST["anio"];
$nombre_taller = $_POST['nombre_taller'];
$participantes = $_POST['participantes'];
$condicion = $_POST['condicion'];

mysqli_query($conn,"UPDATE talleres SET anio = '$anio', nombre_taller = '$nombre_taller', participantes = '$participantes', condicion = '$condicion' WHERE id = '$id' ")or die(mysqli_error($con));

echo "<script type='text/javascript'>alert('Registro actualizado correctamente.');</script>";
echo "<script>document.location='../vistas/ADMIN/TALLERES.php'</script>";
?>
