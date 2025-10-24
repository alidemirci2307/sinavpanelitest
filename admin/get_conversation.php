<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

if (isset($_GET['feedback_id'])) {
    $feedbackId = (int)$_GET['feedback_id'];

    $stmt = $pdo->prepare("SELECT sender, message, created_at FROM feedback_conversations WHERE feedback_id = :fid ORDER BY created_at ASC");
    $stmt->execute([':fid' => $feedbackId]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($conversations as $conversation) {
        $class = $conversation['sender'] === 'admin' ? 'admin-message' : 'user-message';
        echo '<div class="conversation-message ' . $class . '">';
        echo htmlspecialchars($conversation['message'], ENT_QUOTES, 'UTF-8');
        echo '<span class="conversation-timestamp">' . htmlspecialchars($conversation['created_at'], ENT_QUOTES, 'UTF-8') . '</span>';
        echo '</div>';
    }
} else {
    echo '<p>Geçersiz geri bildirim ID\'si.</p>';
}
?>
