<?php session_start();

include('db.php');

if(isset($_REQUEST['id'])){
    $id =$_REQUEST['id'];
} else {
    $id =$_POST['id'];
}

$sql = "SELECT * FROM comentarios WHERE id = $id";
$query = $conn->query($sql);
$row = $query->fetch_assoc();
$id_mantenimiento = $row['id_mantenimiento'];

mysqli_query($conn,"DELETE FROM comentarios where id ='$id' ")or die(mysqli_error());
//echo "<script type='text/javascript'>alert('Registro eliminado correctamente.');</script>";
echo "<script>document.location='../vistas/ADMIN/COMENTARIOS.php?id=$id_mantenimiento'</script>";
?>