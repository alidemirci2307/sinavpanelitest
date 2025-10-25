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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM duyurular WHERE id = ?");
        $stmt->execute([$id]);
        $duyuru = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($duyuru) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $duyuru]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Duyuru bulunamadı']);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Geçersiz ID']);
}
exit;
