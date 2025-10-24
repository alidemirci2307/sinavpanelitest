<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDbConnection();

$stmt = $pdo->query("SELECT * FROM feedbacks ORDER BY created_at DESC");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli - Talepler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .conversation-message {
            display: flex;
            flex-direction: column;
            padding: 10px 14px;
            border-radius: 20px;
            margin-bottom: 10px;
            max-width: 100%;
            word-wrap: break-word;
        }
        .user-message {
            background-color: #e2e3e5;
            align-self: flex-start;
        }
        .admin-message {
            background-color: #d1ecf1;
            align-self: flex-end;
        }
        .conversation-timestamp {
            display: block;
            font-size: 0.8em;
            color: #666;
            margin-top: 4px;
        }
        #conversationContent {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            .btn {
                font-size: 12px;
                padding: 5px 10px;
            }
        }
        @media (max-width: 576px) {
            .table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            table {
                font-size: 10px;
            }
            .btn {
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Paneli</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="index.php">Talepler</a></li>
                <li class="nav-item"><a class="nav-link" href="duyurular/index.php">Duyurular</a></li>
                <li class="nav-item"><a class="nav-link" href="istatistikler.php">İstatistikler</a></li>
            </ul>
            <div class="d-flex">
                <a class="btn btn-outline-light" href="logout.php">Çıkış Yap</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>Gelen Geri Bildirimler</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Konu</th>
                    <th>Mesaj</th>
                    <th>Device ID</th>
                    <th>App Package</th>
                    <th>Tarih</th>
                    <th>Durum</th>
                    <th>Konuşma Geçmişi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($feedbacks as $f): ?>
                <tr>
                    <td><?= $f['id'] ?></td>
                    <td><?= htmlspecialchars($f['subject'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= nl2br(htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8')) ?></td>
                    <td><?= htmlspecialchars($f['device_id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($f['app_package'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $f['created_at'] ?></td>
                    <td>
                        <?php if ($f['status'] === 'open'): ?>
                            <span class="badge bg-warning text-dark">Açık</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Kapalı</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#conversationModal" data-id="<?= $f['id'] ?>">Göster</button>
                    </td>
                    <td>
                        <form method="post" action="update_feedback.php">
                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                            <textarea name="response" class="form-control" placeholder="Yanıtınızı yazın"></textarea>
                            <button type="submit" class="btn btn-sm btn-success mt-1">Yanıtla</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($feedbacks)): ?>
                <tr>
                    <td colspan="9" class="text-center">Henüz bir geri bildirim yok.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="conversationModal" tabindex="-1" aria-labelledby="conversationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="conversationModalLabel">Konuşma Geçmişi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="conversationContent" class="p-3"></div>
                <textarea id="adminReply" class="form-control mt-3" placeholder="Yanıtınızı yazın..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" id="sendReply" class="btn btn-primary">Yanıt Gönder</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const modal = document.getElementById('conversationModal');
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const feedbackId = button.getAttribute('data-id');

        fetch(`get_conversation.php?feedback_id=${feedbackId}&t=${Date.now()}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('conversationContent').innerHTML = data;
            });

        document.getElementById('sendReply').onclick = function () {
            const reply = document.getElementById('adminReply').value;
            if (!reply.trim()) {
                alert('Yanıt boş olamaz!');
                return;
            }

            fetch('reply_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ feedback_id: feedbackId, reply: reply })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Yanıt başarıyla gönderildi!');
                    fetch(`get_conversation.php?feedback_id=${feedbackId}&t=${Date.now()}`)
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('conversationContent').innerHTML = data;
                            document.getElementById('adminReply').value = '';
                        });
                } else {
                    alert('Yanıt gönderilirken bir hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu.');
            });
        };
    });
</script>
</body>
</html>
