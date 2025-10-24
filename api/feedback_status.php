<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('feedback_status_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 30, 60)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek."], 429);
}

$pdo = getDbConnection();

$device_id = sanitizeInput($_GET['device_id'] ?? '');
$feedback_id = isset($_GET['feedback_id']) ? (int)$_GET['feedback_id'] : 0;

if(empty($device_id)) {
    sendJsonResponse(["status" => "error", "message" => "device_id gereklidir."], 400);
}

if(strlen($device_id) > 100) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz device_id."], 400);
}

try {
    if($feedback_id > 0) {
        $stmt = $pdo->prepare("SELECT id, subject, message, device_id, app_package, status, created_at FROM feedbacks WHERE device_id = :device_id AND id = :fid LIMIT 1");
        $stmt->execute([':device_id' => $device_id, ':fid' => $feedback_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id, subject, message, device_id, app_package, status, created_at FROM feedbacks WHERE device_id = :device_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':device_id' => $device_id]);
    }

    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$feedback) {
        sendJsonResponse(["status" => "error", "message" => "Kayıt bulunamadı."], 404);
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

    sendJsonResponse($response);
} catch(PDOException $e) {
    error_log("Feedback status error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu."], 500);
}
