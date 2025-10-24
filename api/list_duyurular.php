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

// app_package parametresi kontrolü
$app_package = isset($_GET['app_package']) ? trim($_GET['app_package']) : '';

if(empty($app_package)) {
    echo json_encode(["status" => "error", "message" => "app_package eksik."]);
    exit;
}

// Sadece aktif duyuruları ve belirtilen app_package'e sahip olanları çek
$stmt = $pdo->prepare("SELECT * FROM duyurular WHERE status = 'active' AND app_package = :app_package ORDER BY priority DESC, created_at DESC");
$stmt->execute([':app_package' => $app_package]);
$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["status" => "success", "data" => $duyurular]);
?>
