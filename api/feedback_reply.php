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
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$feedback_id = isset($data['feedback_id']) ? (int)$data['feedback_id'] : 0;
$message = isset($data['message']) ? $data['message'] : '';

if($feedback_id <= 0 || empty($message)) {
    echo json_encode(["status" => "error", "message" => "Eksik bilgi."]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM feedbacks WHERE id = :id");
$stmt->execute([':id' => $feedback_id]);
$feedback = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$feedback) {
    echo json_encode(["status" => "error", "message" => "Feedback bulunamadı."]);
    exit;
}

$stmtReply = $pdo->prepare("INSERT INTO feedback_conversations (feedback_id, sender, message) VALUES (:fid, 'user', :message)");
$stmtReply->execute([':fid' => $feedback_id, ':message' => $message]);

echo json_encode(["status" => "success", "message" => "Yanıt kaydedildi."]);
