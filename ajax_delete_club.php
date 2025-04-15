<?php
include 'db_connect.php';

$id = $_POST['club_id'];
$stmt = $conn->prepare("DELETE FROM Club WHERE Club_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo "Club deleted successfully";
?>