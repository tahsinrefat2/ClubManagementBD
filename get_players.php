<?php
include 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['club_id'])) {
    $club_id = (int)$_GET['club_id'];
    $result = $conn->query("SELECT Player_id, Player_name FROM Player WHERE Club_id = $club_id ORDER BY Player_name");
    
    $players = [];
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
    
    echo json_encode($players);
} else {
    echo json_encode([]);
}