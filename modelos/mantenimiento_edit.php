<?php
//session_start();
include('db.php');
include ('../controladores/mantenimiento.php');

$id = $_POST["id"];
$id_taller = $_POST["id_taller"];
$id_encargado = $_POST["id_encargado"];
$f_reporte = $_POST['f_reporte'];
$f_realizado = $_POST["f_realizado"];
$descripcion = $_POST["descripcion"];
$estado = isset($_POST['estado']) && in_array($_POST['estado'], ['Realizada', 'no realizado'], true)
    ? $_POST['estado']
    : 'no realizado';

mysqli_query($conn,"UPDATE mantenimiento SET id_taller = '$id_taller', id_encargado = '$id_encargado', f_reporte = '$f_reporte', f_realizado = '$f_realizado', descripcion = '$descripcion', estado = '$estado' WHERE id = '$id' ")or die(mysqli_error($conn));

echo "<script type='text/javascript'>alert('Registro actualizado correctamente.');</script>";
echo "<script type='text/javascript'>push();</script>";

echo "<script>document.location='../vistas/ADMIN/MANTENIMIENTO.php'</script>";

?>
