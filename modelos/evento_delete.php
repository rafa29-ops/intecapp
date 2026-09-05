<?php session_start();

include('db.php');

if(isset($_REQUEST['id_eventos'])){
    $id_eventos =$_REQUEST['id_eventos'];
} else {
    $id_eventos =$_POST['id_eventos'];
}

// TODO: Refactorizar esta validación. 
// Actualmente solo valida la tabla 'instructor'. 
// Pendiente: Agregar validación para la tabla 'asistencia' para evitar registros huérfanos.
// asistencia -> instructor
$sqlD = "SELECT * FROM instructor WHERE id_eventos = '$id_eventos'";
$queryD = $conn->query($sqlD);

if($queryD->num_rows < 1){
	mysqli_query($conn,"DELETE FROM eventos where id_eventos ='$id_eventos' ")or die(mysqli_error());
    //echo "<script type='text/javascript'>alert('Registro eliminado correctamente.');</script>";
    echo "<script>document.location='../vistas/ADMIN/EVENTOS.php'</script>";  
}
else{
    echo "<script type='text/javascript'>alert('No se pudo eliminar, Hay registros Realacionados a este elemento.');</script>";
    echo "<script>document.location='../vistas/ADMIN/EVENTOS.php'</script>";  
}

?>