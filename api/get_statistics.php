<?php
header('Content-Type: application/json');
include '../config.php';

$query = "SELECT 
            COUNT(DISTINCT device_id) AS unique_users, 
            DATE(login_time) AS day, 
            HOUR(login_time) AS hour, 
            COUNT(*) AS total_logins 
          FROM user_statistics 
          GROUP BY day, hour";

$result = $conn->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
