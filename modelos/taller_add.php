<?php
//session_start();
include('db.php');

$anio = $_POST["anio"];
$nombre_taller = $_POST['nombre_taller'];
$participantes = $_POST['participantes'];
$condicion = $_POST['condicion'];
$estado = 'Disponible'; // Estado automático - siempre inicia disponible


mysqli_query($conn,"INSERT INTO talleres (anio, nombre_taller, participantes, condicion, estado) 
    VALUES('$anio','$nombre_taller','$participantes','$condicion','$estado')")or die(mysqli_error($con));
    		
echo "<script>document.location='../vistas/ADMIN/TALLERES.php'</script>";

?>
