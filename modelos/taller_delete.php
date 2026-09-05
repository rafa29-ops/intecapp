<?php session_start();

include('db.php');

if(isset($_REQUEST['id'])){
    $id =$_REQUEST['id'];
} else {
    $id =$_POST['id'];
}

$sqlDE = "SELECT * FROM eventos WHERE id_talleres = '$id'";
$queryDE = $conn->query($sqlDE);

$sqlDM = "SELECT * FROM eventos WHERE id_talleres = '$id'"; //id_taller a id_talleres
$queryDM = $conn->query($sqlDM);

if(($queryDE->num_rows < 1) AND ($queryDM->num_rows < 1)){ //OR a AND
    mysqli_query($conn,"DELETE FROM talleres where id='$id' ")or die(mysqli_error());
    //echo "<script type='text/javascript'>alert('Registro eliminado correctamente.');</script>";
    echo "<script>document.location='../vistas/ADMIN/TALLERES.php'</script>";  
}
else{
    echo "<script type='text/javascript'>alert('No se pudo eliminar, Hay registros Realacionados a este elemento.');</script>";
    echo "<script>document.location='../vistas/ADMIN/EVENTOS.php'</script>";  
}

?>