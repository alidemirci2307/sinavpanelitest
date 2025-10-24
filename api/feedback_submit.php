<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('feedback_submit_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 3600)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek. Lütfen daha sonra tekrar deneyin."], 429);
}

$pdo = getDbConnection();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!is_array($data)) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz veri formatı."], 400);
}

$subject = sanitizeInput($data['subject'] ?? '');
$message = sanitizeInput($data['message'] ?? '');
$device_id = sanitizeInput($data['device_id'] ?? '');
$app_package = sanitizeInput($data['app_package'] ?? '');

if(empty($subject) || empty($message)) {
    sendJsonResponse(["status" => "error", "message" => "Konu ve mesaj gereklidir."], 400);
}

// Mesaj uzunluk kontrolü
if (strlen($subject) > 200 || strlen($message) > 2000) {
    sendJsonResponse(["status" => "error", "message" => "Mesaj çok uzun."], 400);
}

try {
    $stmt = $pdo->prepare("INSERT INTO feedbacks (subject, message, device_id, app_package) VALUES (:subject, :message, :device_id, :app_package)");
    $stmt->execute([
        ':subject' => $subject,
        ':message' => $message,
        ':device_id' => $device_id,
        ':app_package' => $app_package
    ]);

    sendJsonResponse(["status" => "success", "message" => "Geri bildiriminiz kaydedildi."]);
} catch(PDOException $e) {
    error_log("Feedback submit error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu. Lütfen tekrar deneyin."], 500);
}