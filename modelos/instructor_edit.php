<?php
//session_start();
include('db.php');

$id = $_POST['id'];
$año = $_POST["año"];
$nom_instructor = $_POST['nom_instructor'];
//$id_talleres = $_POST['id_talleres'];
//$id_eventos = $_POST['id_eventos'];
//$fecha_uso = $_POST['fecha_uso'];
$estado = $_POST['estado'];


mysqli_query($conn,"UPDATE instructor SET año = '$año', nom_instructor = '$nom_instructor', estado = '$estado' WHERE id = '$id' ")or die(mysqli_error($con));

echo "<script type='text/javascript'>alert('Registro actualizado correctamente.');</script>";
echo "<script>document.location='../vistas/ADMIN/INSTRUCTORES.php'</script>";

?>