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

// Debug için
error_log("Session CSRF Token: " . (isset($_SESSION[CSRF_TOKEN_NAME]) ? $_SESSION[CSRF_TOKEN_NAME] : 'NOT SET'));
error_log("Received CSRF Token: " . (isset($input['csrf_token']) ? $input['csrf_token'] : 'NOT SET'));

if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    header('Content-Type: application/json');
    $errorMsg = 'CSRF token geçersiz';
    if (!isset($input['csrf_token'])) {
        $errorMsg = 'CSRF token gönderilmedi';
    } elseif (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $errorMsg = 'Session\'da CSRF token bulunamadı';
    }
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    exit;
}

$id = isset($input['id']) ? (int)$input['id'] : 0;
$title = isset($input['title']) ? trim($input['title']) : '';
$content = isset($input['content']) ? trim($input['content']) : '';
$type = isset($input['type']) ? $input['type'] : 'text';
$priority = isset($input['priority']) ? (int)$input['priority'] : 5;
$url = isset($input['url']) ? trim($input['url']) : null;
$status = isset($input['status']) ? $input['status'] : 'active';

if ($id <= 0 || empty($title) || empty($content)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID, başlık ve içerik zorunludur']);
    exit;
}

// Tür kontrolü
$validTypes = ['url', 'text', 'dialog', 'info', 'five_stars'];
if (!in_array($type, $validTypes)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Geçersiz tür']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE duyurular SET title = ?, content = ?, type = ?, priority = ?, url = ?, status = ? WHERE id = ?");
    $stmt->execute([$title, $content, $type, $priority, $url, $status, $id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
