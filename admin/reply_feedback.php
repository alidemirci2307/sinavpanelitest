<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized access']));
}

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    exit(json_encode(['error' => 'Database connection failed']));
}

// JSON isteğini al ve çöz
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['feedback_id']) || !isset($input['reply'])) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid input']));
}

$feedbackId = (int)$input['feedback_id'];
$reply = trim($input['reply']);

if (empty($reply)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Reply cannot be empty']));
}

// Veritabanına yanıt ekle
try {
    $stmt = $pdo->prepare("INSERT INTO feedback_conversations (feedback_id, sender, message, created_at) VALUES (:feedback_id, 'admin', :message, NOW())");
    $stmt->execute([
        ':feedback_id' => $feedbackId,
        ':message' => $reply
    ]);

    http_response_code(200);
    echo json_encode(['success' => 'Reply sent successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to insert reply']);
}
