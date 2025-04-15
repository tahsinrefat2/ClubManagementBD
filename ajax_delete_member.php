<?php
include 'db_connect.php';

$member_id = $_POST['member_id'];

// First delete from Club_member (foreign key constraint)
$stmt = $conn->prepare("DELETE FROM Club_member WHERE Member_id=?");
$stmt->bind_param("i", $member_id);

if ($stmt->execute()) {
    $stmt->close();
    
    // Then delete from Member
    $stmt2 = $conn->prepare("DELETE FROM Member WHERE Member_id=?");
    $stmt2->bind_param("i", $member_id);
    
    if ($stmt2->execute()) {
        echo "Member deleted successfully";
    } else {
        http_response_code(500);
        echo "Failed to delete member";
    }
    $stmt2->close();
} else {
    http_response_code(500);
    echo "Failed to delete club membership";
}
?>