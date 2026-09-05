<?php session_start();

include('db.php');

if(isset($_REQUEST['id'])){
    $id =$_REQUEST['id'];
} else {
    $id =$_POST['id'];
}

$sqlDA = "SELECT * FROM asistencia WHERE id_usuario = '$id'";
$queryDA = $conn->query($sqlDA);

$sqlDE = "SELECT * FROM eventos WHERE id_instructor = '$id'";
$queryDE = $conn->query($sqlDE);

$sqlDM = "SELECT * FROM mantenimiento WHERE id_encargado = '$id'";
$queryDM = $conn->query($sqlDM);

if(($queryDA->num_rows < 1) OR ($queryDE->num_rows < 1) OR ($queryDM->num_rows < 1)){
    mysqli_query($conn,"DELETE FROM usuario where id ='$id' ")or die(mysqli_error());
    //echo "<script type='text/javascript'>alert('Registro eliminado correctamente.');</script>";
    echo "<script>document.location='../vistas/ADMIN/USUARIO.php'</script>";  
}
else{
    echo "<script type='text/javascript'>alert('No se pudo eliminar, Hay registros Realacionados a este elemento.');</script>";
    echo "<script>document.location='../vistas/ADMIN/EVENTOS.php'</script>";  
}

?>