<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../security.php';

secureSessionStart();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = getDbConnection();

// JSON veri al
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'CSRF token geçersiz']);
    exit;
}

$title = isset($input['title']) ? trim($input['title']) : '';
$content = isset($input['content']) ? trim($input['content']) : '';
$type = isset($input['type']) ? $input['type'] : 'text';
$priority = isset($input['priority']) ? (int)$input['priority'] : 5;
$url = isset($input['url']) ? trim($input['url']) : null;
$status = isset($input['status']) ? $input['status'] : 'active';

if (empty($title) || empty($content)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Başlık ve içerik zorunludur']);
    exit;
}

try {
    // Eğer birden fazla paket seçildiyse, her biri için ayrı kayıt oluştur
    if (isset($input['app_packages']) && is_array($input['app_packages'])) {
        $stmt = $pdo->prepare("INSERT INTO duyurular (title, content, type, priority, url, app_package, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($input['app_packages'] as $package) {
            $stmt->execute([$title, $content, $type, $priority, $url, $package, $status]);
        }
    } else {
        // Tek paket için kayıt
        $app_package = isset($input['app_package']) ? trim($input['app_package']) : 'all';
        $stmt = $pdo->prepare("INSERT INTO duyurular (title, content, type, priority, url, app_package, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $type, $priority, $url, $app_package, $status]);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
