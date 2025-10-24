<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('list_feedbacks_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 30, 60)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek."], 429);
}

$pdo = getDbConnection();

$device_id = sanitizeInput($_GET['device_id'] ?? '');

if(empty($device_id)) {
    sendJsonResponse(["status" => "error", "message" => "device_id gereklidir."], 400);
}

// Device ID uzunluk kontrolü
if (strlen($device_id) > 100) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz device_id."], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id, subject, status FROM feedbacks WHERE device_id = :device_id ORDER BY created_at DESC LIMIT 50");
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

    sendJsonResponse(["status" => "success", "data" => $data]);
} catch(PDOException $e) {
    error_log("List feedbacks error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu."], 500);
}
