<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('feedback_reply_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 3600)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek."], 429);
}

$pdo = getDbConnection();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!is_array($data)) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz veri formatı."], 400);
}

$feedback_id = isset($data['feedback_id']) ? (int)$data['feedback_id'] : 0;
$message = sanitizeInput($data['message'] ?? '');

if($feedback_id <= 0 || empty($message)) {
    sendJsonResponse(["status" => "error", "message" => "Eksik bilgi."], 400);
}

if(strlen($message) > 2000) {
    sendJsonResponse(["status" => "error", "message" => "Mesaj çok uzun."], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id FROM feedbacks WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $feedback_id]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$feedback) {
        sendJsonResponse(["status" => "error", "message" => "Talep bulunamadı."], 404);
    }

    $stmtReply = $pdo->prepare("INSERT INTO feedback_conversations (feedback_id, sender, message, created_at) VALUES (:fid, 'user', :message, NOW())");
    $stmtReply->execute([':fid' => $feedback_id, ':message' => $message]);

    sendJsonResponse(["status" => "success", "message" => "Yanıt kaydedildi."]);
} catch(PDOException $e) {
    error_log("Feedback reply error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu."], 500);
}
