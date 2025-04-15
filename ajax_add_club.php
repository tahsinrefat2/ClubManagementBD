<?php
include 'db_connect.php';

$name = $_POST['name'];
$address = $_POST['address'];
$contact = $_POST['contact'];
$sports = $_POST['sports'];
$reg_date = $_POST['reg_date'];

// File upload
$logo = $_FILES['logo']['name'];
$logo_tmp = $_FILES['logo']['tmp_name'];
move_uploaded_file($logo_tmp, "uploads/$logo");

$stmt = $conn->prepare("INSERT INTO Club (Club_name, Club_address, Club_contact_info, Sports_offered, Club_registration_date, Club_logo) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $address, $contact, $sports, $reg_date, $logo);
$stmt->execute();
$stmt->close();

echo "Club added successfully";
?>