<?php session_start();

include('db.php');

if(isset($_REQUEST['id'])){
    $id =$_REQUEST['id'];
} else {
    $id =$_POST['id'];
}

$sqlD = "SELECT * FROM eventos WHERE id_instructor = '$id'";
$queryD = $conn->query($sqlD);

if($queryD->num_rows < 1){
    mysqli_query($conn,"DELETE FROM instructor where id ='$id' ")or die(mysqli_error());
    //echo "<script type='text/javascript'>alert('Registro eliminado correctamente.');</script>";
    echo "<script>document.location='../vistas/ADMIN/INSTRUCTORES.php'</script>"; 
}
else{
    echo "<script type='text/javascript'>alert('No se pudo eliminar, Hay registros Realacionados a este elemento.');</script>";
    echo "<script>document.location='../vistas/ADMIN/EVENTOS.php'</script>";  
}

?>