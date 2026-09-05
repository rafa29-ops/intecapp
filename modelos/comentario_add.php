<?php
//session_start();
include('db.php');

$id_mantenimiento = $_POST["id_mantenimiento"];
$Comentario = $_POST["Comentario"];

mysqli_query($conn,"INSERT INTO comentarios (id_mantenimiento, Comentario) 
    VALUES('$id_mantenimiento','$Comentario')")or die(mysqli_error($con));
    		
echo "<script>document.location='../vistas/ADMIN/COMENTARIOS.php?id=$id_mantenimiento'</script>";

?>
