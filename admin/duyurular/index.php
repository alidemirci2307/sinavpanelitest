<?php<?php<?php<?php

ini_set('display_errors', 1);

error_reporting(E_ALL);require_once __DIR__ . '/../../config.php';



require_once __DIR__ . '/../../config.php';require_once __DIR__ . '/../../db.php';require_once __DIR__ . '/../../config.php';require_once __DIR__ . '/../../config.php';

require_once __DIR__ . '/../../db.php';

require_once __DIR__ . '/../../security.php';require_once __DIR__ . '/../../security.php';



secureSessionStart();require_once __DIR__ . '/../../db.php';require_once __DIR__ . '/../../db.php';



if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {secureSessionStart();

    header('Location: ../login.php');

    exit;require_once __DIR__ . '/../../security.php';require_once __DIR__ . '/../../security.php';

}

// Session kontrolü

$pdo = getDbConnection();

$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {



// Toplu işlem    header('Location: ../login.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['csrf_token'])) {    exit;secureSessionStart();secureSessionStart();

    if (!verifyCSRFToken($_POST['csrf_token'])) {

        $error = "Güvenlik hatası!";}

    } else {

        $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);

        $action = $_POST['action'];

        $status = ($action === 'activate') ? 'active' : (($action === 'deactivate') ? 'inactive' : null);$pdo = getDbConnection();



        if($status && !empty($duyuru_ids)) {$page_title = "Duyurular Yönetimi";// Session kontrolü// Session kontrolü

            try {

                $placeholders = str_repeat('?,', count($duyuru_ids) - 1) . '?';

                $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($placeholders)");

                $params = array_merge(array($status), $duyuru_ids);// Toplu güncellemeif(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {

                $stmt->execute($params);

                header('Location: index.php?success=1');$error = '';

                exit;

            } catch(PDOException $e) {if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action']) && isset($_POST['csrf_token'])) {    header('Location: ../login.php');    header('Location: ../login.php');

                $error = "Hata: " . $e->getMessage();

            }    if (!verifyCSRFToken($_POST['csrf_token'])) {

        }

    }        $error = "Güvenlik hatası!";    exit;    exit;

}

    } else {

// İstatistikler

$stmt_stats = $pdo->query("SELECT         $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);}}

    COUNT(*) as total,

    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,        $action = $_POST['action'];

    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive

FROM duyurular");        $status = null;

$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);



// Duyurular

$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");        if($action === 'activate') {$pdo = getDbConnection();$pdo = getDbConnection();

$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>            $status = 'active';

<!DOCTYPE html>

<html lang="tr">        } elseif($action === 'deactivate') {$page_title = "Duyurular Yönetimi";

<head>

    <meta charset="UTF-8">            $status = 'inactive';

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Duyurular - Admin Panel</title>        }$current_page = "duyurular";// Sayfa ayarları

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/admin-style.css">

</head>        if($status && !empty($duyuru_ids)) {$page_title = "Duyurular Yönetimi";

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">            try {

        <div class="container-fluid">

            <a class="navbar-brand" href="../index.php"><i class="bi bi-shield-check"></i> Admin Panel</a>                $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';// Toplu güncelleme işlemi

            <div class="collapse navbar-collapse">

                <ul class="navbar-nav me-auto">                $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");

                    <li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-megaphone"></i> Duyurular</a></li>                $params = array_merge(array($status), $duyuru_ids);if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action']) && isset($_POST['csrf_token'])) {// Toplu güncelleme işlemi

                    <li class="nav-item"><a class="nav-link" href="../istatistikler.php"><i class="bi bi-graph-up"></i> İstatistikler</a></li>

                </ul>                $stmt->execute($params);

                <div class="d-flex align-items-center">

                    <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?></span>                header('Location: index.php?success=1');    if (!verifyCSRFToken($_POST['csrf_token'])) {if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action']) && isset($_POST['csrf_token'])) {

                    <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Çıkış</a>

                </div>                exit;

            </div>

        </div>            } catch(PDOException $e) {        $error = "Güvenlik hatası!";    if (!verifyCSRFToken($_POST['csrf_token'])) {

    </nav>

                error_log("Bulk update error: " . $e->getMessage());

    <div class="container-fluid py-4">

        <h1 class="page-title mb-4"><i class="bi bi-megaphone-fill"></i> Duyurular Yönetimi</h1>                $error = "Toplu güncelleme sırasında bir hata oluştu.";    } else {        die("Güvenlik hatası!");



        <?php if(isset($_GET['success'])): ?>            }

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle-fill"></i> İşlem başarılı!        }        $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);    }

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>    }

        <?php endif; ?>

}        $action = $_POST['action'];    

        <?php if($error): ?>

        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>

        <?php endif; ?>

// İstatistikler    $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);

        <!-- İstatistikler -->

        <div class="row mb-4">$stmt_stats = $pdo->query("SELECT 

            <div class="col-md-4">

                <div class="stat-card">    COUNT(*) as total,        if($action === 'activate') {    $action = $_POST['action'];

                    <div class="stat-card-icon bg-primary"><i class="bi bi-megaphone-fill"></i></div>

                    <div class="stat-card-info">    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,

                        <div class="stat-card-title">Toplam</div>

                        <div class="stat-card-value"><?php echo $stats['total']; ?></div>    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive            $status = 'active';

                    </div>

                </div>FROM duyurular");

            </div>

            <div class="col-md-4">$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);        } elseif($action === 'deactivate') {    if($action === 'activate') {

                <div class="stat-card">

                    <div class="stat-card-icon bg-success"><i class="bi bi-check-circle-fill"></i></div>

                    <div class="stat-card-info">

                        <div class="stat-card-title">Aktif</div>// Duyuruları getir            $status = 'inactive';        $status = 'active';

                        <div class="stat-card-value text-success"><?php echo $stats['active']; ?></div>

                    </div>$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");

                </div>

            </div>$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);        } else {    } elseif($action === 'deactivate') {

            <div class="col-md-4">

                <div class="stat-card">

                    <div class="stat-card-icon bg-secondary"><i class="bi bi-pause-circle-fill"></i></div>

                    <div class="stat-card-info">$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';            $status = null;        $status = 'inactive';

                        <div class="stat-card-title">Pasif</div>

                        <div class="stat-card-value"><?php echo $stats['inactive']; ?></div>?>

                    </div>

                </div><!DOCTYPE html>        }    } else {

            </div>

        </div><html lang="tr">



        <!-- Tablo --><head>        $status = null;

        <div class="card">

            <div class="card-header d-flex justify-content-between">    <meta charset="UTF-8">

                <h5><i class="bi bi-list-ul"></i> Tüm Duyurular</h5>

                <a href="add.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Yeni</a>    <meta name="viewport" content="width=device-width, initial-scale=1.0">        if($status && !empty($duyuru_ids)) {    }

            </div>

            <div class="card-body">    <title>Duyurular Yönetimi - Admin Panel</title>

                <?php if(count($duyurular) > 0): ?>

                <form method="POST" id="bulkForm">    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">            try {

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">

                    <input type="hidden" name="action" id="bulkAction">    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

                    

                    <div class="mb-3">    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">                $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';    if($status && !empty($duyuru_ids)) {

                        <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdate('activate')">

                            <i class="bi bi-check-circle"></i> Aktif Et    <link rel="stylesheet" href="../assets/css/admin-style.css">

                        </button>

                        <button type="button" class="btn btn-secondary btn-sm" onclick="bulkUpdate('deactivate')"></head>                $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");        try {

                            <i class="bi bi-pause-circle"></i> Pasif Et

                        </button><body>

                    </div>

    <!-- Navbar -->                $params = array_merge([$status], $duyuru_ids);            $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';

                    <div class="table-responsive">

                        <table class="table table-hover">    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">

                            <thead>

                                <tr>        <div class="container-fluid">                $stmt->execute($params);            $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");

                                    <th><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>

                                    <th>ID</th>            <a class="navbar-brand" href="../index.php">

                                    <th>Tür</th>

                                    <th>Başlık</th>                <i class="bi bi-shield-check"></i> Admin Panel                header('Location: index.php?success=1');            $params = array_merge([$status], $duyuru_ids);

                                    <th>Durum</th>

                                    <th>Öncelik</th>            </a>

                                    <th>Tarih</th>

                                    <th>İşlem</th>            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">                exit;            $stmt->execute($params);

                                </tr>

                            </thead>                <span class="navbar-toggler-icon"></span>

                            <tbody>

                                <?php foreach($duyurular as $d): ?>            </button>            } catch(PDOException $e) {            header('Location: index.php?success=1');

                                <tr>

                                    <td><input type="checkbox" name="duyuru_ids[]" value="<?php echo $d['id']; ?>" class="row-checkbox"></td>            <div class="collapse navbar-collapse" id="navbarNav">

                                    <td><span class="badge bg-light text-dark">#<?php echo $d['id']; ?></span></td>

                                    <td><?php echo htmlspecialchars($d['type']); ?></td>                <ul class="navbar-nav me-auto">                error_log("Bulk update error: " . $e->getMessage());            exit;

                                    <td><strong><?php echo htmlspecialchars($d['title']); ?></strong></td>

                                    <td>                    <li class="nav-item">

                                        <?php if($d['status'] === 'active'): ?>

                                            <span class="badge bg-success">Aktif</span>                        <a class="nav-link" href="../index.php">                $error = "Toplu güncelleme sırasında bir hata oluştu.";        } catch(PDOException $e) {

                                        <?php else: ?>

                                            <span class="badge bg-secondary">Pasif</span>                            <i class="bi bi-speedometer2"></i> Dashboard

                                        <?php endif; ?>

                                    </td>                        </a>            }            error_log("Bulk update error: " . $e->getMessage());

                                    <td><?php echo $d['priority']; ?></td>

                                    <td><?php echo date('d.m.Y', strtotime($d['created_at'])); ?></td>                    </li>

                                    <td>

                                        <a href="edit.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>                    <li class="nav-item">        }            $error = "Toplu güncelleme sırasında bir hata oluştu.";

                                        <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Silmek istediğinize emin misiniz?')"><i class="bi bi-trash"></i></a>

                                    </td>                        <a class="nav-link active" href="index.php">

                                </tr>

                                <?php endforeach; ?>                            <i class="bi bi-megaphone"></i> Duyurular    }        }

                            </tbody>

                        </table>                        </a>

                    </div>

                </form>                    </li>}    }

                <?php else: ?>

                <div class="text-center py-5">                    <li class="nav-item">

                    <i class="bi bi-megaphone" style="font-size: 4rem; color: #ccc;"></i>

                    <h3>Henüz Duyuru Yok</h3>                        <a class="nav-link" href="../istatistikler.php">}

                    <a href="add.php" class="btn btn-primary">İlk Duyuruyu Ekle</a>

                </div>                            <i class="bi bi-graph-up"></i> İstatistikler

                <?php endif; ?>

            </div>                        </a>// İstatistikler

        </div>

    </div>                    </li>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>                </ul>$stmt_stats = $pdo->query("SELECT // İstatistikler

    <script>

        function toggleAll(cb) {                <div class="d-flex align-items-center">

            var boxes = document.querySelectorAll('.row-checkbox');

            for(var i=0; i<boxes.length; i++) boxes[i].checked = cb.checked;                    <span class="text-white me-3">    COUNT(*) as total,$stmt_stats = $pdo->query("SELECT 

        }

        function bulkUpdate(action) {                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username, ENT_QUOTES, 'UTF-8'); ?>

            var checked = document.querySelectorAll('.row-checkbox:checked');

            if(checked.length === 0) { alert('Lütfen seçim yapın!'); return; }                    </span>    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,    COUNT(*) as total,

            if(confirm('Seçili ' + checked.length + ' duyuruyu güncellemek istiyor musunuz?')) {

                document.getElementById('bulkAction').value = action;                    <a href="../logout.php" class="btn btn-outline-light btn-sm">

                document.getElementById('bulkForm').submit();

            }                        <i class="bi bi-box-arrow-right"></i> Çıkış    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,

        }

    </script>                    </a>

</body>

</html>                </div>FROM duyurular");    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive


            </div>

        </div>$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);FROM duyurular");

    </nav>

$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

    <!-- Main Content -->

    <div class="container-fluid py-4">// Duyuruları getir

        <div class="row mb-4">

            <div class="col">$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");// Önceliğe göre sıralama

                <h1 class="page-title">

                    <i class="bi bi-megaphone-fill"></i> Duyurular Yönetimi$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");

                </h1>

            </div>$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

        </div>

$admin_username = $_SESSION['admin_username'] ?? 'Admin';

        <!-- Başarı/Hata Mesajları -->

        <?php if(isset($_GET['success'])): ?>?>// Header

        <div class="alert alert-success alert-dismissible fade show">

            <i class="bi bi-check-circle-fill me-2"></i>İşlem başarıyla tamamlandı!<!DOCTYPE html>include __DIR__ . '/../includes/header.php';

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div><html lang="tr">?>

        <?php endif; ?>

<head>

        <?php if($error): ?>

        <div class="alert alert-danger alert-dismissible fade show">    <meta charset="UTF-8"><!-- Başarı Mesajı -->

            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>    <meta name="viewport" content="width=device-width, initial-scale=1.0"><?php if(isset($_GET['success'])): ?>

        </div>

        <?php endif; ?>    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title><div class="alert alert-success alert-dismissible fade show">



        <!-- İstatistik Kartları -->    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    <i class="bi bi-check-circle-fill me-2"></i>İşlem başarıyla tamamlandı!

        <div class="row mb-4">

            <div class="col-md-4 mb-3">    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                <div class="stat-card">

                    <div class="stat-card-icon bg-primary">    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"></div>

                        <i class="bi bi-megaphone-fill"></i>

                    </div>    <link rel="stylesheet" href="../assets/css/admin-style.css"><?php endif; ?>

                    <div class="stat-card-info">

                        <div class="stat-card-title">Toplam Duyuru</div></head>

                        <div class="stat-card-value"><?php echo number_format($stats['total']); ?></div>

                    </div><body><?php if(isset($error)): ?>

                </div>

            </div>    <!-- Navbar --><div class="alert alert-danger alert-dismissible fade show">

            <div class="col-md-4 mb-3">

                <div class="stat-card">    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= escapeHtml($error) ?>

                    <div class="stat-card-icon bg-success">

                        <i class="bi bi-check-circle-fill"></i>        <div class="container-fluid">    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                    </div>

                    <div class="stat-card-info">            <a class="navbar-brand" href="../index.php"></div>

                        <div class="stat-card-title">Aktif Duyuru</div>

                        <div class="stat-card-value text-success"><?php echo number_format($stats['active']); ?></div>                <i class="bi bi-shield-check"></i> Admin Panel<?php endif; ?>

                    </div>

                </div>            </a>

            </div>

            <div class="col-md-4 mb-3">            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><!-- İstatistik Kartları -->

                <div class="stat-card">

                    <div class="stat-card-icon bg-secondary">                <span class="navbar-toggler-icon"></span><div class="row mb-4">

                        <i class="bi bi-pause-circle-fill"></i>

                    </div>            </button>    <div class="col-md-4 col-sm-6 mb-3">

                    <div class="stat-card-info">

                        <div class="stat-card-title">Pasif Duyuru</div>            <div class="collapse navbar-collapse" id="navbarNav">        <div class="stat-card">

                        <div class="stat-card-value text-secondary"><?php echo number_format($stats['inactive']); ?></div>

                    </div>                <ul class="navbar-nav me-auto">            <div class="stat-card-title">

                </div>

            </div>                    <li class="nav-item">                <i class="bi bi-megaphone-fill me-2"></i>Toplam Duyuru

        </div>

                        <a class="nav-link" href="../index.php">            </div>

        <!-- Duyurular Tablosu -->

        <div class="card">                            <i class="bi bi-speedometer2"></i> Dashboard            <div class="stat-card-value"><?= number_format($stats['total']) ?></div>

            <div class="card-header d-flex justify-content-between align-items-center">

                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Tüm Duyurular</h5>                        </a>            <div class="stat-card-change text-muted">

                <a href="add.php" class="btn btn-primary btn-sm">

                    <i class="bi bi-plus-circle"></i> Yeni Duyuru                    </li>                <i class="bi bi-list-ul"></i> Tüm duyurular

                </a>

            </div>                    <li class="nav-item">            </div>

            <div class="card-body">

                <?php if(count($duyurular) > 0): ?>                        <a class="nav-link active" href="index.php">        </div>

                <form method="POST" id="bulkForm">

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">                            <i class="bi bi-megaphone"></i> Duyurular    </div>

                    <input type="hidden" name="action" id="bulkAction">

                                            </a>    <div class="col-md-4 col-sm-6 mb-3">

                    <div class="mb-3">

                        <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdate('activate')">                    </li>        <div class="stat-card" style="border-left-color: var(--success-color);">

                            <i class="bi bi-check-circle"></i> Seçilenleri Aktif Et

                        </button>                    <li class="nav-item">            <div class="stat-card-title">

                        <button type="button" class="btn btn-secondary btn-sm" onclick="bulkUpdate('deactivate')">

                            <i class="bi bi-pause-circle"></i> Seçilenleri Pasif Et                        <a class="nav-link" href="../istatistikler.php">                <i class="bi bi-check-circle-fill me-2"></i>Aktif Duyuru

                        </button>

                    </div>                            <i class="bi bi-graph-up"></i> İstatistikler            </div>



                    <div class="table-responsive">                        </a>            <div class="stat-card-value text-success"><?= number_format($stats['active']) ?></div>

                        <table class="table table-hover">

                            <thead>                    </li>            <div class="stat-card-change text-muted">

                                <tr>

                                    <th width="40">                </ul>                <i class="bi bi-eye-fill"></i> Yayında

                                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)">

                                    </th>                <div class="d-flex align-items-center">            </div>

                                    <th width="80">ID</th>

                                    <th>Tür</th>                    <span class="text-white me-3">        </div>

                                    <th>Başlık</th>

                                    <th>İçerik</th>                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?>    </div>

                                    <th>URL</th>

                                    <th>Durum</th>                    </span>    <div class="col-md-4 col-sm-6 mb-3">

                                    <th>Öncelik</th>

                                    <th>Paket</th>                    <a href="../logout.php" class="btn btn-outline-light btn-sm">        <div class="stat-card" style="border-left-color: var(--secondary-color);">

                                    <th>Tarih</th>

                                    <th width="150">İşlemler</th>                        <i class="bi bi-box-arrow-right"></i> Çıkış            <div class="stat-card-title">

                                </tr>

                            </thead>                    </a>                <i class="bi bi-x-circle-fill me-2"></i>Pasif Duyuru

                            <tbody>

                                <?php foreach($duyurular as $d): ?>                </div>            </div>

                                <tr>

                                    <td>            </div>            <div class="stat-card-value text-secondary"><?= number_format($stats['inactive']) ?></div>

                                        <input type="checkbox" name="duyuru_ids[]" value="<?php echo $d['id']; ?>" class="row-checkbox">

                                    </td>        </div>            <div class="stat-card-change text-muted">

                                    <td><span class="badge bg-light text-dark">#<?php echo $d['id']; ?></span></td>

                                    <td>    </nav>                <i class="bi bi-eye-slash-fill"></i> Devre dışı

                                        <?php

                                        $type_icons = array(            </div>

                                            'info' => 'info-circle text-info',

                                            'warning' => 'exclamation-triangle text-warning',    <!-- Main Content -->        </div>

                                            'update' => 'arrow-up-circle text-primary',

                                            'promotion' => 'star text-warning'    <div class="container-fluid py-4">    </div>

                                        );

                                        $icon = isset($type_icons[$d['type']]) ? $type_icons[$d['type']] : 'circle text-secondary';        <div class="row mb-4"></div>

                                        ?>

                                        <i class="bi bi-<?php echo $icon; ?>"></i>             <div class="col">

                                        <?php echo htmlspecialchars(ucfirst($d['type']), ENT_QUOTES, 'UTF-8'); ?>

                                    </td>                <h1 class="page-title"><!-- Sayfa Başlığı ve Butonlar -->

                                    <td><strong><?php echo htmlspecialchars($d['title'], ENT_QUOTES, 'UTF-8'); ?></strong></td>

                                    <td>                    <i class="bi bi-megaphone-fill"></i> Duyurular Yönetimi<div class="d-flex justify-content-between align-items-center mb-4">

                                        <small><?php 

                                        $content = htmlspecialchars($d['content'], ENT_QUOTES, 'UTF-8');                </h1>    <h1 class="page-title">

                                        echo substr($content, 0, 80);

                                        echo strlen($d['content']) > 80 ? '...' : '';             </div>        <i class="bi bi-megaphone-fill"></i>

                                        ?></small>

                                    </td>        </div>        <span>

                                    <td>

                                        <?php if(!empty($d['url'])): ?>            Duyurular

                                            <a href="<?php echo htmlspecialchars($d['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="text-primary">

                                                <i class="bi bi-link-45deg"></i>        <!-- Başarı/Hata Mesajları -->            <small class="page-subtitle d-block">Uygulama duyurularınızı yönetin</small>

                                            </a>

                                        <?php else: ?>        <?php if(isset($_GET['success'])): ?>        </span>

                                            <span class="text-muted">-</span>

                                        <?php endif; ?>        <div class="alert alert-success alert-dismissible fade show">    </h1>

                                    </td>

                                    <td>            <i class="bi bi-check-circle-fill me-2"></i>İşlem başarıyla tamamlandı!    <div>

                                        <?php if($d['status'] === 'active'): ?>

                                            <span class="badge bg-success">Aktif</span>            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>        <a href="add.php" class="btn btn-success">

                                        <?php else: ?>

                                            <span class="badge bg-secondary">Pasif</span>        </div>            <i class="bi bi-plus-circle-fill"></i> Yeni Duyuru Ekle

                                        <?php endif; ?>

                                    </td>        <?php endif; ?>        </a>

                                    <td>

                                        <?php if($d['priority'] > 0): ?>    </div>

                                            <span class="badge bg-warning text-dark">

                                                <i class="bi bi-star-fill"></i> <?php echo $d['priority']; ?>        <?php if(isset($error)): ?></div>

                                            </span>

                                        <?php else: ?>        <div class="alert alert-danger alert-dismissible fade show">

                                            <span class="text-muted">0</span>

                                        <?php endif; ?>            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?><!-- Toplu İşlem Formu -->

                                    </td>

                                    <td><small class="text-muted"><?php echo htmlspecialchars($d['app_package'], ENT_QUOTES, 'UTF-8'); ?></small></td>            <button type="button" class="btn-close" data-bs-dismiss="alert"></button><form method="post" id="bulkForm">

                                    <td><small><?php echo date('d.m.Y', strtotime($d['created_at'])); ?></small></td>

                                    <td>        </div>    <input type="hidden" name="csrf_token" value="<?= escapeHtml(generateCSRFToken()); ?>">

                                        <div class="btn-group" role="group">

                                            <a href="edit.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-primary" title="Düzenle">        <?php endif; ?>    

                                                <i class="bi bi-pencil"></i>

                                            </a>    <!-- Toplu İşlem Butonları -->

                                            <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-danger" title="Sil" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')">

                                                <i class="bi bi-trash"></i>        <!-- İstatistik Kartları -->    <div class="mb-3 d-flex gap-2 flex-wrap">

                                            </a>

                                        </div>        <div class="row mb-4">        <button type="submit" name="action" value="activate" class="btn btn-success btn-sm" onclick="return confirmBulkAction('aktif')">

                                    </td>

                                </tr>            <div class="col-md-4 mb-3">            <i class="bi bi-check-circle"></i> Seçilenleri Aktif Et

                                <?php endforeach; ?>

                            </tbody>                <div class="stat-card">        </button>

                        </table>

                    </div>                    <div class="stat-card-icon bg-primary">        <button type="submit" name="action" value="deactivate" class="btn btn-warning btn-sm" onclick="return confirmBulkAction('pasif')">

                </form>

                <?php else: ?>                        <i class="bi bi-megaphone-fill"></i>            <i class="bi bi-x-circle"></i> Seçilenleri Pasif Et

                <div class="empty-state">

                    <i class="bi bi-megaphone"></i>                    </div>        </button>

                    <h3>Henüz Duyuru Yok</h3>

                    <p>Yeni bir duyuru eklemek için yukarıdaki butona tıklayın.</p>                    <div class="stat-card-info">        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()">

                    <a href="add.php" class="btn btn-primary">

                        <i class="bi bi-plus-circle"></i> İlk Duyuruyu Ekle                        <div class="stat-card-title">Toplam Duyuru</div>            <i class="bi bi-arrow-clockwise"></i> Yenile

                    </a>

                </div>                        <div class="stat-card-value"><?php echo number_format($stats['total']); ?></div>        </button>

                <?php endif; ?>

            </div>                    </div>    </div>

        </div>

    </div>                </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>            </div>    <!-- Duyurular Tablosu -->

    <script>

        function toggleAll(checkbox) {            <div class="col-md-4 mb-3">    <div class="table-wrapper">

            var checkboxes = document.querySelectorAll('.row-checkbox');

            for(var i = 0; i < checkboxes.length; i++) {                <div class="stat-card">        <div class="table-responsive">

                checkboxes[i].checked = checkbox.checked;

            }                    <div class="stat-card-icon bg-success">            <table class="table table-hover align-middle">

        }

                        <i class="bi bi-check-circle-fill"></i>                <thead>

        function bulkUpdate(action) {

            var checked = document.querySelectorAll('.row-checkbox:checked');                    </div>                    <tr>

            if(checked.length === 0) {

                alert('Lütfen en az bir duyuru seçin!');                    <div class="stat-card-info">                        <th style="width: 40px;">

                return;

            }                        <div class="stat-card-title">Aktif Duyuru</div>                            <input type="checkbox" id="selectAll" class="form-check-input">

            

            var actionText = action === 'activate' ? 'aktif etmek' : 'pasif etmek';                        <div class="stat-card-value text-success"><?php echo number_format($stats['active']); ?></div>                        </th>

            if(confirm('Seçili ' + checked.length + ' duyuruyu ' + actionText + ' istediğinize emin misiniz?')) {

                document.getElementById('bulkAction').value = action;                    </div>                        <th style="width: 60px;">ID</th>

                document.getElementById('bulkForm').submit();

            }                </div>                        <th style="width: 100px;">Tip</th>

        }

            </div>                        <th>Başlık</th>

        // Alert otomatik kapanma

        setTimeout(function() {            <div class="col-md-4 mb-3">                        <th>İçerik</th>

            var alerts = document.querySelectorAll('.alert');

            for(var i = 0; i < alerts.length; i++) {                <div class="stat-card">                        <th style="width: 150px;">URL</th>

                var bsAlert = new bootstrap.Alert(alerts[i]);

                bsAlert.close();                    <div class="stat-card-icon bg-secondary">                        <th style="width: 90px;">Durum</th>

            }

        }, 5000);                        <i class="bi bi-pause-circle-fill"></i>                        <th style="width: 80px;">Öncelik</th>

    </script>

</body>                    </div>                        <th style="width: 180px;">App Package</th>

</html>

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
