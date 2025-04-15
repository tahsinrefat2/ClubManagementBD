<?php
include 'db_connect.php';

$member_name = $_POST['member_name'];
$member_contact = $_POST['member_contact'];
$sports_preferences = $_POST['sports_preferences'];
$club_id = $_POST['club_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// Insert into Member table
$stmt = $conn->prepare("INSERT INTO Member (Member_name, member_contact_info, Sports_Preferences) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $member_name, $member_contact, $sports_preferences);
if ($stmt->execute()) {
    $member_id = $stmt->insert_id;
    $stmt->close();

    // Insert into Club_member (with dates)
    $stmt2 = $conn->prepare("INSERT INTO Club_member (Club_id, Member_id, Membership_start_date, Membership_end_date) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiss", $club_id, $member_id, $start_date, $end_date);
    if ($stmt2->execute()) {
        echo "Member added successfully";
    } else {
        http_response_code(500);
        echo "Failed to link member to club";
    }
    $stmt2->close();
} else {
    http_response_code(500);
    echo "Failed to add member";
}
?>