<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

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
