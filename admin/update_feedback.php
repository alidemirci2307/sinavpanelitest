<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

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

$feedbackId = isset($input['feedback_id']) ? (int)$input['feedback_id'] : 0;
$status = isset($input['status']) ? $input['status'] : '';

if ($feedbackId > 0 && in_array($status, ['open', 'closed'])) {
    try {
        $stmt = $pdo->prepare("UPDATE feedbacks SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $feedbackId]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Geçersiz parametreler']);
}
exit;
