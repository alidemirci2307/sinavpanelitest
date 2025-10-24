<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Rate limiting
if (!checkRateLimit('get_statistics_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 20, 60)) {
    sendJsonResponse(["status" => "error", "message" => "Çok fazla istek."], 429);
}

$pdo = getDbConnection();

try {
    $query = "SELECT 
                COUNT(DISTINCT device_id) AS unique_users, 
                DATE(login_time) AS day, 
                HOUR(login_time) AS hour, 
                COUNT(*) AS total_logins 
              FROM user_statistics 
              GROUP BY day, hour
              ORDER BY day DESC, hour DESC
              LIMIT 1000";

    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJsonResponse(["status" => "success", "data" => $data]);
} catch(PDOException $e) {
    error_log("Get statistics error: " . $e->getMessage());
    sendJsonResponse(["status" => "error", "message" => "Bir hata oluştu."], 500);
}
