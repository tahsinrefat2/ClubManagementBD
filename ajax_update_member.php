<?php
include 'db_connect.php';

$member_id = $_POST['member_id'];
$member_name = $_POST['member_name'];
$member_contact = $_POST['member_contact'];
$sports_preferences = $_POST['sports_preferences'];
$club_id = $_POST['club_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// Update Member table
$stmt = $conn->prepare("UPDATE Member SET Member_name=?, member_contact_info=?, Sports_Preferences=? WHERE Member_id=?");
$stmt->bind_param("sssi", $member_name, $member_contact, $sports_preferences, $member_id);

if ($stmt->execute()) {
    $stmt->close();

    // Update Club_member relationship
    $stmt2 = $conn->prepare("UPDATE Club_member SET Club_id=?, Membership_start_date=?, Membership_end_date=? WHERE Member_id=?");
    $stmt2->bind_param("issi", $club_id, $start_date, $end_date, $member_id);
    
    if ($stmt2->execute()) {
        echo "Member updated successfully";
    } else {
        http_response_code(500);
        echo "Failed to update club membership";
    }
    $stmt2->close();
} else {
    http_response_code(500);
    echo "Failed to update member";
}
?>