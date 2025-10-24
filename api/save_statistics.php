<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('save_statistics_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 20, 3600)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek."], 429);
}

$pdo = getDbConnection();

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!is_array($data)) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz veri formatı."], 400);
}

$device_id = sanitizeInput($data['deviceId'] ?? '');
$login_time = sanitizeInput($data['loginTime'] ?? '');
$app_version = sanitizeInput($data['appVersion'] ?? '');
$packageName = sanitizeInput($data['packageName'] ?? 'ehliyet.sinav.sorulari.app');
$os = sanitizeInput($data['os'] ?? '');

if (empty($device_id) || empty($login_time) || empty($app_version) || empty($os)) {
    sendJsonResponse(["status" => "error", "message" => "Eksik bilgi."], 400);
}

// Input uzunluk kontrolü
if (strlen($device_id) > 100 || strlen($app_version) > 50 || strlen($packageName) > 100 || strlen($os) > 50) {
    sendJsonResponse(["status" => "error", "message" => "Geçersiz veri uzunluğu."], 400);
}

// Sunucunun o anki zamanını al ve GMT+3'e dönüştür
try {
    $dateTime = new DateTime('now', new DateTimeZone('UTC'));
    $dateTime->setTimezone(new DateTimeZone('Europe/Istanbul'));
    $login_time_gmt3 = $dateTime->format('Y-m-d H:i:s');
} catch (Exception $e) {
    error_log("Date conversion error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Tarih işleme hatası."], 500);
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_statistics (device_id, login_time, app_version, os, packageName) 
                           VALUES (:device_id, :login_time, :app_version, :os, :packageName)");
    $stmt->execute([
        ':device_id' => $device_id,
        ':login_time' => $login_time_gmt3,
        ':app_version' => $app_version,
        ':packageName' => $packageName,
        ':os' => $os
    ]);
    
    sendJsonResponse(["status" => "success", "message" => "İstatistik kaydedildi."]);
} catch (PDOException $e) {
    error_log("Statistics save error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu."], 500);
}
