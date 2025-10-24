<?php
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Veritabanı bağlantısı başarısız: " . $e->getMessage()]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$device_id = isset($data['deviceId']) ? $data['deviceId'] : null;
$login_time = isset($data['loginTime']) ? $data['loginTime'] : null;
$app_version = isset($data['appVersion']) ? $data['appVersion'] : null;
$packageName = isset($data['packageName']) ? $data['packageName'] : "ehliyet.sinav.sorulari.app";
$os = isset($data['os']) ? $data['os'] : null;

if (!$device_id || !$login_time || !$app_version || !$os) {
    echo json_encode(["status" => "error", "message" => "Eksik bilgi."]);
    exit;
}

// Sunucunun o anki zamanını al ve GMT+3'e dönüştür
try {
    $dateTime = new DateTime('now', new DateTimeZone('UTC')); // UTC zaman diliminde şu anki zaman
    $dateTime->setTimezone(new DateTimeZone('Europe/Istanbul')); // GMT+3 saat dilimine dönüştür
    $login_time_gmt3 = $dateTime->format('Y-m-d H:i:s'); // MySQL için uygun format
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Tarih dönüştürme hatası: " . $e->getMessage()]);
    exit;
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
    echo json_encode(["status" => "success", "message" => "İstatistik başarıyla kaydedildi."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "İstatistik kaydedilirken hata oluştu: " . $e->getMessage()]);
}
?>
