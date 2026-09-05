<?php
//session_start();
include('db.php');

$año = $_POST["año"];
$nom_instructor = $_POST['nom_instructor'];
//$id_talleres = $_POST['id_talleres'];
//$id_eventos = $_POST['id_eventos'];
//$fecha_uso = $_POST['fecha_uso'];
$estado = $_POST['estado'];

mysqli_query($conn,"INSERT INTO instructor (año, nom_instructor, estado) 
    VALUES('$año','$nom_instructor','$estado')")or die(mysqli_error($con));
    		
echo "<script>document.location='../vistas/ADMIN/INSTRUCTORES.php'</script>";

?>
