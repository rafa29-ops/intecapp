<?php
//session_start();
include('db.php');

if(isset($_REQUEST['id_eventos'])){
    $id_eventos =$_REQUEST['id_eventos'];
} else {
    $id_eventos =$_POST['id_eventos'];
}

$sql = "SELECT * FROM eventos WHERE id_eventos = $id_eventos";
$query = $conn->query($sql);
$row = $query->fetch_assoc();

?>