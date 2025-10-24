<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../security.php';

secureSessionStart();

// Session kontrolü
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDbConnection();

// Sayfa ayarları
$page_title = "Duyurular Yönetimi";

// Toplu güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action']) && isset($_POST['csrf_token'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        die("Güvenlik hatası!");
    }
    
    $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);
    $action = $_POST['action'];

    if($action === 'activate') {
        $status = 'active';
    } elseif($action === 'deactivate') {
        $status = 'inactive';
    } else {
        $status = null;
    }

    if($status && !empty($duyuru_ids)) {
        try {
            $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");
            $params = array_merge([$status], $duyuru_ids);
            $stmt->execute($params);
            header('Location: index.php?success=1');
            exit;
        } catch(PDOException $e) {
            error_log("Bulk update error: " . $e->getMessage());
            $error = "Toplu güncelleme sırasında bir hata oluştu.";
        }
    }
}

// İstatistikler
$stmt_stats = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
FROM duyurular");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Önceliğe göre sıralama
$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");
$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header
include __DIR__ . '/../includes/header.php';
?>

<!-- Başarı Mesajı -->
<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>İşlem başarıyla tamamlandı!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if(isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= escapeHtml($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- İstatistik Kartları -->
<div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card">
            <div class="stat-card-title">
                <i class="bi bi-megaphone-fill me-2"></i>Toplam Duyuru
            </div>
            <div class="stat-card-value"><?= number_format($stats['total']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-list-ul"></i> Tüm duyurular
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card" style="border-left-color: var(--success-color);">
            <div class="stat-card-title">
                <i class="bi bi-check-circle-fill me-2"></i>Aktif Duyuru
            </div>
            <div class="stat-card-value text-success"><?= number_format($stats['active']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-eye-fill"></i> Yayında
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card" style="border-left-color: var(--secondary-color);">
            <div class="stat-card-title">
                <i class="bi bi-x-circle-fill me-2"></i>Pasif Duyuru
            </div>
            <div class="stat-card-value text-secondary"><?= number_format($stats['inactive']) ?></div>
            <div class="stat-card-change text-muted">
                <i class="bi bi-eye-slash-fill"></i> Devre dışı
            </div>
        </div>
    </div>
</div>

<!-- Sayfa Başlığı ve Butonlar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title">
        <i class="bi bi-megaphone-fill"></i>
        <span>
            Duyurular
            <small class="page-subtitle d-block">Uygulama duyurularınızı yönetin</small>
        </span>
    </h1>
    <div>
        <a href="add.php" class="btn btn-success">
            <i class="bi bi-plus-circle-fill"></i> Yeni Duyuru Ekle
        </a>
    </div>
</div>

<!-- Toplu İşlem Formu -->
<form method="post" id="bulkForm">
    <input type="hidden" name="csrf_token" value="<?= escapeHtml(generateCSRFToken()); ?>">
    
    <!-- Toplu İşlem Butonları -->
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <button type="submit" name="action" value="activate" class="btn btn-success btn-sm" onclick="return confirmBulkAction('aktif')">
            <i class="bi bi-check-circle"></i> Seçilenleri Aktif Et
        </button>
        <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm" onclick="return confirmBulkAction('pasif')">
            <i class="bi bi-x-circle"></i> Seçilenleri Pasif Et
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Yenile
        </button>
    </div>

    <!-- Duyurular Tablosu -->
    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 100px;">Tip</th>
                        <th>Başlık</th>
                        <th>İçerik</th>
                        <th style="width: 150px;">URL</th>
                        <th style="width: 90px;">Durum</th>
                        <th style="width: 80px;">Öncelik</th>
                        <th style="width: 180px;">App Package</th>
                        <th style="width: 150px;">Oluşturma</th>
                        <th style="width: 200px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($duyurular as $d): ?>
                    <?php foreach($duyurular as $d): ?>
                    <tr class="<?= ($d['priority'] >= 50) ? 'high-priority' : '' ?>">
                        <td>
                            <input type="checkbox" name="duyuru_ids[]" value="<?= $d['id'] ?>" class="form-check-input">
                        </td>
                        <td><span class="badge bg-secondary">#<?= $d['id'] ?></span></td>
                        <td>
                            <span class="badge badge-info">
                                <?php
                                    $typeIcons = [
                                        'url' => 'link-45deg',
                                        'text' => 'file-text',
                                        'dialog' => 'chat-square-text',
                                        'info' => 'info-circle',
                                        'five_stars' => 'star'
                                    ];
                                    $icon = $typeIcons[$d['type']] ?? 'megaphone';
                                ?>
                                <i class="bi bi-<?= $icon ?>"></i> <?= escapeHtml(ucfirst($d['type'])) ?>
                            </span>
                        </td>
                        <td><strong><?= escapeHtml($d['title']) ?></strong></td>
                        <td>
                            <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= escapeHtml(substr($d['content'], 0, 80)) ?><?= strlen($d['content']) > 80 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($d['type'] === 'url' && !empty($d['url'])): ?>
                                <a href="<?= escapeHtml($d['url']) ?>" target="_blank" class="text-primary" data-bs-toggle="tooltip" title="<?= escapeHtml($d['url']) ?>">
                                    <i class="bi bi-box-arrow-up-right"></i> Link
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['status'] === 'active'): ?>
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Pasif
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($d['priority'] >= 50): ?>
                                <span class="badge badge-warning">
                                    <i class="bi bi-exclamation-triangle"></i> <?= $d['priority'] ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark"><?= $d['priority'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= escapeHtml($d['app_package']) ?></small></td>
                        <td><small><?= date('d.m.Y H:i', strtotime($d['created_at'])) ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-primary" data-bs-toggle="tooltip" title="Düzenle">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="delete.php?id=<?= $d['id'] ?>" class="btn btn-danger" 
                                   onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');"
                                   data-bs-toggle="tooltip" title="Sil">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($duyurular)): ?>
                    <tr>
                        <td colspan="11" class="empty-state">
                            <div class="empty-state-icon">📢</div>
                            <div class="empty-state-title">Henüz Duyuru Yok</div>
                            <div class="empty-state-text">
                                İlk duyurunuzu eklemek için "Yeni Duyuru Ekle" butonuna tıklayın.
                            </div>
                            <a href="add.php" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle"></i> İlk Duyuruyu Ekle
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php
$extra_js = <<<'EOD'
<script>
    // Tümünü seç
    document.getElementById('selectAll').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('input[name="duyuru_ids[]"]');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    });
    
    // Toplu işlem onayı
    function confirmBulkAction(action) {
        const selected = document.querySelectorAll('input[name="duyuru_ids[]"]:checked');
        if(selected.length === 0) {
            alert('Lütfen en az bir duyuru seçin.');
            return false;
        }
        return confirm(`Seçili ${selected.length} duyuruyu ${action} yapmak istediğinize emin misiniz?`);
    }
</script>
EOD;

include __DIR__ . '/../includes/footer.php';
?>

