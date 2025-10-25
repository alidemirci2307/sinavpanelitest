<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized');
}

$pdo = getDbConnection();

if (isset($_GET['feedback_id'])) {
    $feedbackId = (int)$_GET['feedback_id'];

    // İlk mesajı feedbacks tablosundan çek
    $feedbackStmt = $pdo->prepare("SELECT subject, message, created_at FROM feedbacks WHERE id = :fid");
    $feedbackStmt->execute([':fid' => $feedbackId]);
    $feedback = $feedbackStmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        // İlk mesajı göster (kullanıcıdan gelen)
        echo '<div class="conversation-message user-message">';
        echo '<div class="mb-2"><strong>' . escapeHtml($feedback['subject']) . '</strong></div>';
        echo '<div>' . nl2br(escapeHtml($feedback['message'])) . '</div>';
        echo '<div class="conversation-timestamp">' . date('d.m.Y H:i', strtotime($feedback['created_at'])) . '</div>';
        echo '</div>';
    }

    // Konuşma geçmişini çek
    $stmt = $pdo->prepare("SELECT sender, message, created_at FROM feedback_conversations WHERE feedback_id = :fid ORDER BY created_at ASC");
    $stmt->execute([':fid' => $feedbackId]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($conversations as $conversation) {
        $class = $conversation['sender'] === 'admin' ? 'admin-message' : 'user-message';
        echo '<div class="conversation-message ' . $class . '">';
        echo '<div>' . nl2br(escapeHtml($conversation['message'])) . '</div>';
        echo '<div class="conversation-timestamp">' . date('d.m.Y H:i', strtotime($conversation['created_at'])) . '</div>';
        echo '</div>';
    }

    if (!$feedback && empty($conversations)) {
        echo '<div class="alert alert-info"><i class="bi bi-info-circle"></i> Henüz konuşma geçmişi yok.</div>';
    }
} else {
    echo '<p>Geçersiz geri bildirim ID\'si.</p>';
}
?>
