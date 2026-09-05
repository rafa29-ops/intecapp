<?php
//session_start();
include('db.php');

if(isset($_REQUEST['id'])){
    $id =$_REQUEST['id'];
} else {
    $id =$_POST['id'];
}

$sql = "SELECT * FROM instructor WHERE id = $id";
$query = $conn->query($sql);
$row = $query->fetch_assoc();

?>