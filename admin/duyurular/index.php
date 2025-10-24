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

// Toplu güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['action'])) {
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
            // Hazırlanmış ifadeler ile güvenli toplu güncelleme
            $in  = str_repeat('?,', count($duyuru_ids) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN ($in)");
            $params = array_merge([$status], $duyuru_ids);
            $stmt->execute($params);
            header('Location: index.php');
            exit;
        } catch(PDOException $e) {
            die("Toplu güncelleme sırasında bir hata oluştu: " . $e->getMessage());
        }
    }
}

// Önceliğe göre sıralama (yüksek öncelik ilk gelir)
$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");
$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli - Duyurular</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .high-priority {
            border: 2px solid #ffc107;
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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">Admin Paneli</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="../index.php">Talepler</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php">Duyurular</a></li>
                <li class="nav-item"><a class="nav-link" href="../istatistikler.php">İstatistikler</a></li>
            </ul>
            <div class="d-flex">
                <a class="btn btn-outline-light" href="../logout.php">Çıkış Yap</a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-4">
    <h3>Duyurular</h3>
    <a href="add.php" class="btn btn-success mb-3">Yeni Duyuru Ekle</a>
    <form method="post" action="">
        <div class="mb-3">
            <button type="submit" name="action" value="activate" class="btn btn-primary">Seçilenleri Aktif Yap</button>
            <button type="submit" name="action" value="deactivate" class="btn btn-secondary">Seçilenleri Pasif Yap</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>Tip</th>
                        <th>Başlık</th>
                        <th>İçerik</th>
                        <th>URL</th>
                        <th>Durum</th>
                        <th>Öncelik</th>
                        <th>App Package</th>
                        <th>Oluşturulma Tarihi</th>
                        <th>Güncellenme Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($duyurular as $d): ?>
                    <tr class="<?= ($d['priority'] >= 10) ? 'high-priority' : '' ?>">
                        <td><input type="checkbox" name="duyuru_ids[]" value="<?= $d['id'] ?>"></td>
                        <td><?= $d['id'] ?></td>
                        <td><?= htmlspecialchars($d['type'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= nl2br(htmlspecialchars($d['content'], ENT_QUOTES, 'UTF-8')) ?></td>
                        <td>
                            <?php if ($d['type'] === 'url' && !empty($d['url'])): ?>
                                <a href="<?= htmlspecialchars($d['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($d['url'], ENT_QUOTES, 'UTF-8') ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($d['status'] === 'active'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['priority'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($d['app_package'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $d['created_at'] ?></td>
                        <td><?= $d['updated_at'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-primary btn-sm">Düzenle</a>
                            <a href="delete.php?id=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($duyurular)): ?>
                    <tr>
                        <td colspan="13" class="text-center">Henüz duyuru yok.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('selectAll').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('input[name="duyuru_ids[]"]');
        checkboxes.forEach(cb => cb.checked = e.target.checked);
    });
</script>

</body>
</html>

