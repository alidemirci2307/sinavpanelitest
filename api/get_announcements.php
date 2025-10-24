<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('get_announcements_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 30, 60)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek."], 429);
}

$pdo = getDbConnection();

// app_package parametresi kontrolü
$app_package = sanitizeInput($_GET['app_package'] ?? '');

if(empty($app_package)) {
    sendJsonResponse(["status" => "error", "message" => "app_package gereklidir."], 400);
}

// Package name formatı kontrolü
if (!preg_match('/^[a-z][a-z0-9_]*(\.[a-z0-9_]+)+[0-9a-z_]$/i', $app_package)) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz app_package formatı."], 400);
}

try {
    // Sadece aktif duyuruları ve belirtilen app_package'e sahip olanları çek
    $stmt = $pdo->prepare("SELECT id, type, title, content, url, priority, created_at FROM duyurular WHERE status = 'active' AND app_package = :app_package ORDER BY priority DESC, created_at DESC");
    $stmt->execute([':app_package' => $app_package]);
    $duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(["status" => "success", "data" => $duyurular]);
} catch(PDOException $e) {
    error_log("Get announcements error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu."], 500);
}
