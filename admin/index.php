<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

$pdo = getDbConnection();

// Sayfa ayarları
$page_title = "Geri Bildirim Talepler";

// İstatistikler
$stmt_stats = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
    COUNT(DISTINCT device_id) as unique_devices
FROM feedbacks");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Talepler
$stmt = $pdo->query("SELECT * FROM feedbacks ORDER BY created_at DESC LIMIT 100");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header
include __DIR__ . '/includes/header.php';
?>

<!-- İstatistik Kartları -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-card-title">
                <i class="bi bi-inbox-fill me-2"></i>Toplam Talep
            </div>
            <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-graph-up"></i> Tüm zamanlar
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-left-color: var(--warning-color);">
            <div class="stat-card-title">
                <i class="bi bi-hourglass-split me-2"></i>Açık Talepler
            </div>
            <div class="stat-card-value text-warning"><?= number_format($stats['open']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-clock"></i> Bekliyor
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-left-color: var(--success-color);">
            <div class="stat-card-title">
                <i class="bi bi-check-circle-fill me-2"></i>Kapalı Talepler
            </div>
            <div class="stat-card-value text-success"><?= number_format($stats['closed']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-check-all"></i> Tamamlandı
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card" style="border-left-color: var(--info-color);">
            <div class="stat-card-title">
                <i class="bi bi-people-fill me-2"></i>Cihaz Sayısı
            </div>
            <div class="stat-card-value text-info"><?= number_format($stats['unique_devices']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-phone"></i> Benzersiz
            </div>
        </div>
    </div>
</div>

<!-- Sayfa Başlığı -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">
        <i class="bi bi-inbox-fill"></i>
        <span>
            Gelen Geri Bildirimler
            <small class="page-subtitle d-block">Kullanıcı taleplerinizi yönetin</small>
        </span>
    </h1>
    <div>
        <button class="btn btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Yenile
        </button>
    </div>
</div>

<!-- Talepler Tablosu -->
<div class="table-wrapper">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Konu</th>
                    <th>Mesaj</th>
                    <th style="width: 150px;">Device ID</th>
                    <th style="width: 180px;">App Package</th>
                    <th style="width: 150px;">Tarih</th>
                    <th style="width: 100px;">Durum</th>
                    <th style="width: 180px;">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($feedbacks as $f): ?>
                <tr>
                    <td><span class="badge bg-secondary">#<?= $f['id'] ?></span></td>
                    <td><strong><?= escapeHtml($f['subject']) ?></strong></td>
                    <td>
                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= escapeHtml(substr($f['message'], 0, 100)) ?><?= strlen($f['message']) > 100 ? '...' : '' ?>
                        </div>
                    </td>
                    <td><small class="text-muted"><?= escapeHtml(substr($f['device_id'], 0, 15)) ?>...</small></td>
                    <td><small class="text-muted"><?= escapeHtml($f['app_package']) ?></small></td>
                    <td><small><?= date('d.m.Y H:i', strtotime($f['created_at'])) ?></small></td>
                    <td>
                        <?php if ($f['status'] === 'open'): ?>
                            <span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> Açık</span>
                        <?php else: ?>
                            <span class="badge badge-success"><i class="bi bi-check-circle"></i> Kapalı</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#conversationModal" 
                                data-id="<?= $f['id'] ?>" data-bs-toggle="tooltip" title="Konuşma geçmişini görüntüle">
                            <i class="bi bi-chat-dots"></i> Görüntüle
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($feedbacks)): ?>
                <tr>
                    <td colspan="8" class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <div class="empty-state-title">Henüz Talep Yok</div>
                        <div class="empty-state-text">Kullanıcılardan geri bildirim geldiğinde burada görünecek.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Konuşma Modal -->
<div class="modal fade" id="conversationModal" tabindex="-1" aria-labelledby="conversationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="conversationModalLabel">
                    <i class="bi bi-chat-dots me-2"></i>Konuşma Geçmişi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="conversationContent"></div>
                <div class="mt-4">
                    <label for="adminReply" class="form-label fw-bold">
                        <i class="bi bi-reply-fill me-2"></i>Yanıtınız
                    </label>
                    <textarea id="adminReply" class="form-control" rows="4" 
                              placeholder="Yanıtınızı buraya yazın..."></textarea>
                    <input type="hidden" id="csrfToken" value="<?= escapeHtml(generateCSRFToken()); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Kapat
                </button>
                <button type="button" id="sendReply" class="btn btn-primary">
                    <i class="bi bi-send-fill"></i> Yanıt Gönder
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = <<<'EOD'
<script>
    const modal = document.getElementById('conversationModal');
    let currentFeedbackId = null;

    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentFeedbackId = button.getAttribute('data-id');

        // Konuşmayı yükle
        loadConversation(currentFeedbackId);
    });

    function loadConversation(feedbackId) {
        const loader = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div></div>';
        document.getElementById('conversationContent').innerHTML = loader;

        fetch(`get_conversation.php?feedback_id=${feedbackId}&t=${Date.now()}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('conversationContent').innerHTML = data;
                // Scroll to bottom
                const content = document.getElementById('conversationContent');
                content.scrollTop = content.scrollHeight;
            })
            .catch(error => {
                document.getElementById('conversationContent').innerHTML = 
                    '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Konuşma yüklenirken hata oluştu.</div>';
            });
    }

    document.getElementById('sendReply').onclick = function () {
        const reply = document.getElementById('adminReply').value;
        const csrfToken = document.getElementById('csrfToken').value;
        
        if (!reply.trim()) {
            alert('Yanıt boş olamaz!');
            return;
        }

        // Butonu disable et
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Gönderiliyor...';

        fetch('reply_feedback.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                feedback_id: currentFeedbackId, 
                reply: reply,
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Başarı mesajı göster
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success alert-dismissible fade show';
                successMsg.innerHTML = '<i class="bi bi-check-circle"></i> Yanıt başarıyla gönderildi!';
                document.querySelector('.modal-body').insertBefore(successMsg, document.querySelector('.modal-body').firstChild);
                
                // Konuşmayı yeniden yükle
                loadConversation(currentFeedbackId);
                document.getElementById('adminReply').value = '';
                
                // Sayfayı yenile
                setTimeout(() => location.reload(), 2000);
            } else {
                alert('Yanıt gönderilirken bir hata oluştu: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill"></i> Yanıt Gönder';
        });
    };
</script>
EOD;

include __DIR__ . '/includes/footer.php';
?>
