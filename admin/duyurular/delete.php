<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../security.php';

secureSessionStart();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDbConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0) {
    die("Geçersiz duyuru ID.");
}

try {
    $stmt = $pdo->prepare("DELETE FROM duyurular WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header('Location: index.php');
    exit;
} catch(PDOException $e) {
    die("Duyuru silinirken bir hata oluştu: " . $e->getMessage());
}
?>
