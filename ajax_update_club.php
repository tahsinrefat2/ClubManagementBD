<?php
include 'db_connect.php';

$id = $_POST['club_id'];
$name = $_POST['name'];
$address = $_POST['address'];
$contact = $_POST['contact'];
$sports = $_POST['sports'];
$reg_date = $_POST['reg_date'];

if (!empty($_FILES['logo']['name'])) {
    $logo = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    move_uploaded_file($logo_tmp, "uploads/$logo");
    $query = "UPDATE Club SET Club_name=?, Club_address=?, Club_contact_info=?, Sports_offered=?, Club_registration_date=?, Club_logo=? WHERE Club_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $name, $address, $contact, $sports, $reg_date, $logo, $id);
} else {
    $query = "UPDATE Club SET Club_name=?, Club_address=?, Club_contact_info=?, Sports_offered=?, Club_registration_date=? WHERE Club_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $name, $address, $contact, $sports, $reg_date, $id);
}

$stmt->execute();
$stmt->close();

echo "Club updated successfully";
?>