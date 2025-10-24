<?php<?php

require_once __DIR__ . '/../../config.php';require_once __DIR__ . '/../../config.php';

require_once __DIR__ . '/../../db.php';require_once __DIR__ . '/../../db.php';

require_once __DIR__ . '/../../security.php';require_once __DIR__ . '/../../security.php';



secureSessionStart();secureSessionStart();



// Session kontrolü// Session kontrolü

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {

    header('Location: ../login.php');    header('Location: ../login.php');

    exit;    exit;

}}



$pdo = getDbConnection();$pdo = getDbConnection();

$page_title = "Duyurular Yönetimi";

$current_page = "duyurular";// Sayfa ayarları

$page_title = "Duyurular Yönetimi";

// Toplu güncelleme işlemi

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action']) && isset($_POST['csrf_token'])) {// Toplu güncelleme işlemi

    if (!verifyCSRFToken($_POST['csrf_token'])) {if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action']) && isset($_POST['csrf_token'])) {

        $error = "Güvenlik hatası!";    if (!verifyCSRFToken($_POST['csrf_token'])) {

    } else {        die("Güvenlik hatası!");

        $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);    }

        $action = $_POST['action'];    

    $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);

        if($action === 'activate') {    $action = $_POST['action'];

            $status = 'active';

        } elseif($action === 'deactivate') {    if($action === 'activate') {

            $status = 'inactive';        $status = 'active';

        } else {    } elseif($action === 'deactivate') {

            $status = null;        $status = 'inactive';

        }    } else {

        $status = null;

        if($status && !empty($duyuru_ids)) {    }

            try {

                $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';    if($status && !empty($duyuru_ids)) {

                $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");        try {

                $params = array_merge([$status], $duyuru_ids);            $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';

                $stmt->execute($params);            $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");

                header('Location: index.php?success=1');            $params = array_merge([$status], $duyuru_ids);

                exit;            $stmt->execute($params);

            } catch(PDOException $e) {            header('Location: index.php?success=1');

                error_log("Bulk update error: " . $e->getMessage());            exit;

                $error = "Toplu güncelleme sırasında bir hata oluştu.";        } catch(PDOException $e) {

            }            error_log("Bulk update error: " . $e->getMessage());

        }            $error = "Toplu güncelleme sırasında bir hata oluştu.";

    }        }

}    }

}

// İstatistikler

$stmt_stats = $pdo->query("SELECT // İstatistikler

    COUNT(*) as total,$stmt_stats = $pdo->query("SELECT 

    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,    COUNT(*) as total,

    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,

FROM duyurular");    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive

$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);FROM duyurular");

$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Duyuruları getir

$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");// Önceliğe göre sıralama

$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");

$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

?>// Header

<!DOCTYPE html>include __DIR__ . '/../includes/header.php';

<html lang="tr">?>

<head>

    <meta charset="UTF-8"><!-- Başarı Mesajı -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0"><?php if(isset($_GET['success'])): ?>

    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title><div class="alert alert-success alert-dismissible fade show">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    <i class="bi bi-check-circle-fill me-2"></i>İşlem başarıyla tamamlandı!

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"></div>

    <link rel="stylesheet" href="../assets/css/admin-style.css"><?php endif; ?>

</head>

<body><?php if(isset($error)): ?>

    <!-- Navbar --><div class="alert alert-danger alert-dismissible fade show">

    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= escapeHtml($error) ?>

        <div class="container-fluid">    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            <a class="navbar-brand" href="../index.php"></div>

                <i class="bi bi-shield-check"></i> Admin Panel<?php endif; ?>

            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><!-- İstatistik Kartları -->

                <span class="navbar-toggler-icon"></span><div class="row mb-4">

            </button>    <div class="col-md-4 col-sm-6 mb-3">

            <div class="collapse navbar-collapse" id="navbarNav">        <div class="stat-card">

                <ul class="navbar-nav me-auto">            <div class="stat-card-title">

                    <li class="nav-item">                <i class="bi bi-megaphone-fill me-2"></i>Toplam Duyuru

                        <a class="nav-link" href="../index.php">            </div>

                            <i class="bi bi-speedometer2"></i> Dashboard            <div class="stat-card-value"><?= number_format($stats['total']) ?></div>

                        </a>            <div class="stat-card-change text-muted">

                    </li>                <i class="bi bi-list-ul"></i> Tüm duyurular

                    <li class="nav-item">            </div>

                        <a class="nav-link active" href="index.php">        </div>

                            <i class="bi bi-megaphone"></i> Duyurular    </div>

                        </a>    <div class="col-md-4 col-sm-6 mb-3">

                    </li>        <div class="stat-card" style="border-left-color: var(--success-color);">

                    <li class="nav-item">            <div class="stat-card-title">

                        <a class="nav-link" href="../istatistikler.php">                <i class="bi bi-check-circle-fill me-2"></i>Aktif Duyuru

                            <i class="bi bi-graph-up"></i> İstatistikler            </div>

                        </a>            <div class="stat-card-value text-success"><?= number_format($stats['active']) ?></div>

                    </li>            <div class="stat-card-change text-muted">

                </ul>                <i class="bi bi-eye-fill"></i> Yayında

                <div class="d-flex align-items-center">            </div>

                    <span class="text-white me-3">        </div>

                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?>    </div>

                    </span>    <div class="col-md-4 col-sm-6 mb-3">

                    <a href="../logout.php" class="btn btn-outline-light btn-sm">        <div class="stat-card" style="border-left-color: var(--secondary-color);">

                        <i class="bi bi-box-arrow-right"></i> Çıkış            <div class="stat-card-title">

                    </a>                <i class="bi bi-x-circle-fill me-2"></i>Pasif Duyuru

                </div>            </div>

            </div>            <div class="stat-card-value text-secondary"><?= number_format($stats['inactive']) ?></div>

        </div>            <div class="stat-card-change text-muted">

    </nav>                <i class="bi bi-eye-slash-fill"></i> Devre dışı

            </div>

    <!-- Main Content -->        </div>

    <div class="container-fluid py-4">    </div>

        <div class="row mb-4"></div>

            <div class="col">

                <h1 class="page-title"><!-- Sayfa Başlığı ve Butonlar -->

                    <i class="bi bi-megaphone-fill"></i> Duyurular Yönetimi<div class="d-flex justify-content-between align-items-center mb-4">

                </h1>    <h1 class="page-title">

            </div>        <i class="bi bi-megaphone-fill"></i>

        </div>        <span>

            Duyurular

        <!-- Başarı/Hata Mesajları -->            <small class="page-subtitle d-block">Uygulama duyurularınızı yönetin</small>

        <?php if(isset($_GET['success'])): ?>        </span>

        <div class="alert alert-success alert-dismissible fade show">    </h1>

            <i class="bi bi-check-circle-fill me-2"></i>İşlem başarıyla tamamlandı!    <div>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>        <a href="add.php" class="btn btn-success">

        </div>            <i class="bi bi-plus-circle-fill"></i> Yeni Duyuru Ekle

        <?php endif; ?>        </a>

    </div>

        <?php if(isset($error)): ?></div>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?><!-- Toplu İşlem Formu -->

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button><form method="post" id="bulkForm">

        </div>    <input type="hidden" name="csrf_token" value="<?= escapeHtml(generateCSRFToken()); ?>">

        <?php endif; ?>    

    <!-- Toplu İşlem Butonları -->

        <!-- İstatistik Kartları -->    <div class="mb-3 d-flex gap-2 flex-wrap">

        <div class="row mb-4">        <button type="submit" name="action" value="activate" class="btn btn-success btn-sm" onclick="return confirmBulkAction('aktif')">

            <div class="col-md-4 mb-3">            <i class="bi bi-check-circle"></i> Seçilenleri Aktif Et

                <div class="stat-card">        </button>

                    <div class="stat-card-icon bg-primary">        <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm" onclick="return confirmBulkAction('pasif')">

                        <i class="bi bi-megaphone-fill"></i>            <i class="bi bi-x-circle"></i> Seçilenleri Pasif Et

                    </div>        </button>

                    <div class="stat-card-info">        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">

                        <div class="stat-card-title">Toplam Duyuru</div>            <i class="bi bi-arrow-clockwise"></i> Yenile

                        <div class="stat-card-value"><?php echo number_format($stats['total']); ?></div>        </button>

                    </div>    </div>

                </div>

            </div>    <!-- Duyurular Tablosu -->

            <div class="col-md-4 mb-3">    <div class="table-wrapper">

                <div class="stat-card">        <div class="table-responsive">

                    <div class="stat-card-icon bg-success">            <table class="table table-hover align-middle">

                        <i class="bi bi-check-circle-fill"></i>                <thead>

                    </div>                    <tr>

                    <div class="stat-card-info">                        <th style="width: 40px;">

                        <div class="stat-card-title">Aktif Duyuru</div>                            <input type="checkbox" id="selectAll" class="form-check-input">

                        <div class="stat-card-value text-success"><?php echo number_format($stats['active']); ?></div>                        </th>

                    </div>                        <th style="width: 60px;">ID</th>

                </div>                        <th style="width: 100px;">Tip</th>

            </div>                        <th>Başlık</th>

            <div class="col-md-4 mb-3">                        <th>İçerik</th>

                <div class="stat-card">                        <th style="width: 150px;">URL</th>

                    <div class="stat-card-icon bg-secondary">                        <th style="width: 90px;">Durum</th>

                        <i class="bi bi-pause-circle-fill"></i>                        <th style="width: 80px;">Öncelik</th>

                    </div>                        <th style="width: 180px;">App Package</th>

                    <div class="stat-card-info">                        <th style="width: 150px;">Oluşturma</th>

                        <div class="stat-card-title">Pasif Duyuru</div>                        <th style="width: 200px;">İşlemler</th>

                        <div class="stat-card-value text-secondary"><?php echo number_format($stats['inactive']); ?></div>                    </tr>

                    </div>                </thead>

                </div>                <tbody>

            </div>                    <?php foreach ($duyurular as $d): ?>

        </div>                    <?php foreach($duyurular as $d): ?>

                    <tr class="<?= ($d['priority'] >= 50) ? 'high-priority' : '' ?>">

        <!-- Duyurular Tablosu -->                        <td>

        <div class="card">                            <input type="checkbox" name="duyuru_ids[]" value="<?= $d['id'] ?>" class="form-check-input">

            <div class="card-header d-flex justify-content-between align-items-center">                        </td>

                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Tüm Duyurular</h5>                        <td><span class="badge bg-secondary">#<?= $d['id'] ?></span></td>

                <a href="add.php" class="btn btn-primary btn-sm">                        <td>

                    <i class="bi bi-plus-circle"></i> Yeni Duyuru                            <span class="badge badge-info">

                </a>                                <?php

            </div>                                    $typeIcons = [

            <div class="card-body">                                        'url' => 'link-45deg',

                <?php if(count($duyurular) > 0): ?>                                        'text' => 'file-text',

                <form method="POST" id="bulkForm">                                        'dialog' => 'chat-square-text',

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">                                        'info' => 'info-circle',

                    <input type="hidden" name="action" id="bulkAction">                                        'five_stars' => 'star'

                                                        ];

                    <div class="mb-3">                                    $icon = $typeIcons[$d['type']] ?? 'megaphone';

                        <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdate('activate')">                                ?>

                            <i class="bi bi-check-circle"></i> Seçilenleri Aktif Et                                <i class="bi bi-<?= $icon ?>"></i> <?= escapeHtml(ucfirst($d['type'])) ?>

                        </button>                            </span>

                        <button type="button" class="btn btn-secondary btn-sm" onclick="bulkUpdate('deactivate')">                        </td>

                            <i class="bi bi-pause-circle"></i> Seçilenleri Pasif Et                        <td><strong><?= escapeHtml($d['title']) ?></strong></td>

                        </button>                        <td>

                    </div>                            <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">

                                <?= escapeHtml(substr($d['content'], 0, 80)) ?><?= strlen($d['content']) > 80 ? '...' : '' ?>

                    <div class="table-responsive">                            </div>

                        <table class="table table-hover">                        </td>

                            <thead>                        <td>

                                <tr>                            <?php if ($d['type'] === 'url' && !empty($d['url'])): ?>

                                    <th width="40">                                <a href="<?= escapeHtml($d['url']) ?>" target="_blank" class="text-primary" data-bs-toggle="tooltip" title="<?= escapeHtml($d['url']) ?>">

                                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)">                                    <i class="bi bi-box-arrow-up-right"></i> Link

                                    </th>                                </a>

                                    <th width="80">ID</th>                            <?php else: ?>

                                    <th>Tür</th>                                <span class="text-muted">-</span>

                                    <th>Başlık</th>                            <?php endif; ?>

                                    <th>İçerik</th>                        </td>

                                    <th>URL</th>                        <td>

                                    <th>Durum</th>                            <?php if ($d['status'] === 'active'): ?>

                                    <th>Öncelik</th>                                <span class="badge badge-success">

                                    <th>Paket</th>                                    <i class="bi bi-check-circle"></i> Aktif

                                    <th>Tarih</th>                                </span>

                                    <th width="150">İşlemler</th>                            <?php else: ?>

                                </tr>                                <span class="badge bg-secondary">

                            </thead>                                    <i class="bi bi-x-circle"></i> Pasif

                            <tbody>                                </span>

                                <?php foreach($duyurular as $d): ?>                            <?php endif; ?>

                                <tr>                        </td>

                                    <td>                        <td>

                                        <input type="checkbox" name="duyuru_ids[]" value="<?php echo $d['id']; ?>" class="row-checkbox">                            <?php if($d['priority'] >= 50): ?>

                                    </td>                                <span class="badge badge-warning">

                                    <td><span class="badge bg-light text-dark">#<?php echo $d['id']; ?></span></td>                                    <i class="bi bi-exclamation-triangle"></i> <?= $d['priority'] ?>

                                    <td>                                </span>

                                        <?php                            <?php else: ?>

                                        $type_icons = [                                <span class="badge bg-light text-dark"><?= $d['priority'] ?></span>

                                            'info' => 'info-circle text-info',                            <?php endif; ?>

                                            'warning' => 'exclamation-triangle text-warning',                        </td>

                                            'update' => 'arrow-up-circle text-primary',                        <td><small class="text-muted"><?= escapeHtml($d['app_package']) ?></small></td>

                                            'promotion' => 'star text-warning'                        <td><small><?= date('d.m.Y H:i', strtotime($d['created_at'])) ?></small></td>

                                        ];                        <td>

                                        $icon = $type_icons[$d['type']] ?? 'circle text-secondary';                            <div class="btn-group btn-group-sm" role="group">

                                        ?>                                <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-primary" data-bs-toggle="tooltip" title="Düzenle">

                                        <i class="bi bi-<?php echo $icon; ?>"></i>                                     <i class="bi bi-pencil-square"></i>

                                        <?php echo htmlspecialchars(ucfirst($d['type'])); ?>                                </a>

                                    </td>                                <a href="delete.php?id=<?= $d['id'] ?>" class="btn btn-danger" 

                                    <td><strong><?php echo htmlspecialchars($d['title']); ?></strong></td>                                   onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');"

                                    <td>                                   data-bs-toggle="tooltip" title="Sil">

                                        <small><?php echo htmlspecialchars(substr($d['content'], 0, 80)); ?><?php echo strlen($d['content']) > 80 ? '...' : ''; ?></small>                                    <i class="bi bi-trash"></i>

                                    </td>                                </a>

                                    <td>                            </div>

                                        <?php if($d['url']): ?>                        </td>

                                            <a href="<?php echo htmlspecialchars($d['url']); ?>" target="_blank" class="text-primary">                    </tr>

                                                <i class="bi bi-link-45deg"></i>                    <?php endforeach; ?>

                                            </a>                    <?php if (empty($duyurular)): ?>

                                        <?php else: ?>                    <tr>

                                            <span class="text-muted">-</span>                        <td colspan="11" class="empty-state">

                                        <?php endif; ?>                            <div class="empty-state-icon">📢</div>

                                    </td>                            <div class="empty-state-title">Henüz Duyuru Yok</div>

                                    <td>                            <div class="empty-state-text">

                                        <?php if($d['status'] === 'active'): ?>                                İlk duyurunuzu eklemek için "Yeni Duyuru Ekle" butonuna tıklayın.

                                            <span class="badge bg-success">Aktif</span>                            </div>

                                        <?php else: ?>                            <a href="add.php" class="btn btn-primary mt-3">

                                            <span class="badge bg-secondary">Pasif</span>                                <i class="bi bi-plus-circle"></i> İlk Duyuruyu Ekle

                                        <?php endif; ?>                            </a>

                                    </td>                        </td>

                                    <td>                    </tr>

                                        <?php if($d['priority'] > 0): ?>                    <?php endif; ?>

                                            <span class="badge bg-warning text-dark">                </tbody>

                                                <i class="bi bi-star-fill"></i> <?php echo $d['priority']; ?>            </table>

                                            </span>        </div>

                                        <?php else: ?>    </div>

                                            <span class="text-muted">0</span></form>

                                        <?php endif; ?>

                                    </td><?php

                                    <td><small class="text-muted"><?php echo htmlspecialchars($d['app_package']); ?></small></td>$extra_js = <<<'EOD'

                                    <td><small><?php echo date('d.m.Y', strtotime($d['created_at'])); ?></small></td><script>

                                    <td>    // Tümünü seç

                                        <div class="btn-group" role="group">    document.getElementById('selectAll').addEventListener('change', function(e) {

                                            <a href="edit.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">        const checkboxes = document.querySelectorAll('input[name="duyuru_ids[]"]');

                                                <i class="bi bi-pencil"></i>        checkboxes.forEach(cb => cb.checked = e.target.checked);

                                            </a>    });

                                            <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')">    

                                                <i class="bi bi-trash"></i>    // Toplu işlem onayı

                                            </a>    function confirmBulkAction(action) {

                                        </div>        const selected = document.querySelectorAll('input[name="duyuru_ids[]"]:checked');

                                    </td>        if(selected.length === 0) {

                                </tr>            alert('Lütfen en az bir duyuru seçin.');

                                <?php endforeach; ?>            return false;

                            </tbody>        }

                        </table>        return confirm(`Seçili ${selected.length} duyuruyu ${action} yapmak istediğinize emin misiniz?`);

                    </div>    }

                </form></script>

                <?php else: ?>EOD;

                <div class="empty-state">

                    <i class="bi bi-megaphone"></i>include __DIR__ . '/../includes/footer.php';

                    <h3>Henüz Duyuru Yok</h3>?>

                    <p>Yeni bir duyuru eklemek için yukarıdaki butona tıklayın.</p>

                    <a href="add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> İlk Duyuruyu Ekle
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleAll(checkbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }

        function bulkUpdate(action) {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if(checked.length === 0) {
                alert('Lütfen en az bir duyuru seçin!');
                return;
            }
            
            const actionText = action === 'activate' ? 'aktif etmek' : 'pasif etmek';
            if(confirm(`Seçili ${checked.length} duyuruyu ${actionText} istediğinize emin misiniz?`)) {
                document.getElementById('bulkAction').value = action;
                document.getElementById('bulkForm').submit();
            }
        }

        // Alert otomatik kapanma
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
