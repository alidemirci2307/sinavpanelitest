<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit(json_encode(['error' => 'Yetkisiz erişim']));
}

$pdo = getDbConnection();

// JSON isteğini al ve çöz
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || !isset($input['feedback_id']) || !isset($input['reply']) || !isset($input['csrf_token'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Geçersiz istek']));
}

// CSRF kontrolü
if (!verifyCSRFToken($input['csrf_token'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Güvenlik hatası']));
}

$feedbackId = (int)$input['feedback_id'];
$reply = sanitizeInput($input['reply']);

if (empty($reply)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Yanıt boş olamaz']));
}

if (strlen($reply) > 2000) {
    http_response_code(400);
    exit(json_encode(['error' => 'Yanıt çok uzun']));
}

// Feedback'in var olduğunu kontrol et
try {
    $checkStmt = $pdo->prepare("SELECT id FROM feedbacks WHERE id = :id LIMIT 1");
    $checkStmt->execute([':id' => $feedbackId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        exit(json_encode(['error' => 'Talep bulunamadı']));
    }
} catch (PDOException $e) {
    error_log("Check feedback error: " . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['error' => 'Bir hata oluştu']));
}

// Veritabanına yanıt ekle
try {
    $stmt = $pdo->prepare("INSERT INTO feedback_conversations (feedback_id, sender, message, created_at) VALUES (:feedback_id, 'admin', :message, NOW())");
    $stmt->execute([
        ':feedback_id' => $feedbackId,
        ':message' => $reply
    ]);

    http_response_code(200);
    echo json_encode(['success' => 'Yanıt gönderildi']);
} catch (PDOException $e) {
    error_log("Reply feedback error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Yanıt gönderilemedi']);
}
