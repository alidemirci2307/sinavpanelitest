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
$feedback_id = isset($_GET['feedback_id']) ? (int)$_GET['feedback_id'] : 0;

if(empty($device_id)) {
    echo json_encode(["status" => "error", "message" => "device_id eksik."]);
    exit;
}

if($feedback_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM feedbacks WHERE device_id = :device_id AND id = :fid LIMIT 1");
    $stmt->execute([':device_id' => $device_id, ':fid' => $feedback_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM feedbacks WHERE device_id = :device_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([':device_id' => $device_id]);
}

$feedback = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$feedback) {
    echo json_encode(["status" => "error", "message" => "Kayıt bulunamadı"]);
    exit;
}

$stmtConv = $pdo->prepare("SELECT sender, message, created_at FROM feedback_conversations WHERE feedback_id = :fid ORDER BY created_at ASC");
$stmtConv->execute([':fid' => $feedback['id']]);
$conversations = $stmtConv->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "status" => "success",
    "feedback_id" => $feedback['id'],
    "subject" => $feedback['subject'],
    "message" => $feedback['message'],
    "device_id" => $feedback['device_id'],
    "app_package" => $feedback['app_package'],
    "feedback_status" => $feedback['status'],
    "conversation" => $conversations
];

echo json_encode($response);
