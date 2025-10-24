<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Yanıt gönderildiyse
if(isset($_POST['response']) && $_POST['response'] !== '') {
    $response = $_POST['response'];
    // admin yanıtını conversation tablosuna ekle
    $stmt = $pdo->prepare("INSERT INTO feedback_conversations (feedback_id, sender, message) VALUES (:fid, 'admin', :message)");
    $stmt->execute([':fid' => $id, ':message' => $response]);
}

// Kapatma işlemi istenmişse
if(isset($_POST['close']) && $_POST['close'] == 1) {
    $stmt = $pdo->prepare("UPDATE feedbacks SET status = 'closed' WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

header('Location: index.php');
exit;
