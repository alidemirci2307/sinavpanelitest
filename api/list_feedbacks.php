<?php
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}

$device_id = isset($_GET['device_id']) ? $_GET['device_id'] : '';

if(empty($device_id)) {
    echo json_encode(["status" => "error", "message" => "device_id eksik."]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, subject, status FROM feedbacks WHERE device_id = :device_id ORDER BY created_at DESC");
$stmt->execute([':device_id' => $device_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach($feedbacks as $f) {
    $stmtConv = $pdo->prepare("SELECT sender, message FROM feedback_conversations WHERE feedback_id = :fid ORDER BY created_at DESC LIMIT 1");
    $stmtConv->execute([':fid' => $f['id']]);
    $lastConv = $stmtConv->fetch(PDO::FETCH_ASSOC);

    $data[] = [
        "feedback_id" => $f['id'],
        "subject" => $f['subject'],
        "status" => $f['status'],
        "last_response" => $lastConv ? $lastConv['message'] : null
    ];
}

echo json_encode(["status" => "success", "data" => $data]);
