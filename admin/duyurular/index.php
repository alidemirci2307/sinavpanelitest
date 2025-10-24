<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../security.php';

secureSessionStart();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$pdo = getDbConnection();
$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['csrf_token'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Guvenlik hatasi!";
    } else {
        $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);
        $action = $_POST['action'];
        $status = ($action === 'activate') ? 'active' : (($action === 'deactivate') ? 'inactive' : null);

        if($status && !empty($duyuru_ids)) {
            try {
                $placeholders = str_repeat('?,', count($duyuru_ids) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN (" . $placeholders . ")");
                $params = array_merge(array($status), $duyuru_ids);
                $stmt->execute($params);
                header('Location: index.php?success=1');
                exit;
            } catch(PDOException $e) {
                $error = "Hata: " . $e->getMessage();
            }
        }
    }
}

$stmt_stats = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive FROM duyurular");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM duyurular ORDER BY priority DESC, created_at DESC");
$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Duyurular</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
<div class="container-fluid">
<a class="navbar-brand" href="../index.php"><i class="bi bi-shield-check"></i> Admin</a>
<div class="collapse navbar-collapse">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-megaphone"></i> Duyurular</a></li>
<li class="nav-item"><a class="nav-link" href="../istatistikler.php"><i class="bi bi-graph-up"></i> Istatistikler</a></li>
</ul>
<div class="d-flex">
<span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?></span>
<a href="../logout.php" class="btn btn-outline-light btn-sm">Cikis</a>
</div>
</div>
</div>
</nav>
<div class="container-fluid py-4">
<h1 class="mb-4">Duyurular</h1>
<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Basarili!</div>
<?php endif; ?>
<?php if($error): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div class="row mb-4">
<div class="col-md-4"><div class="stat-card"><div class="stat-card-icon bg-primary"><i class="bi bi-megaphone-fill"></i></div><div class="stat-card-info"><div class="stat-card-title">Toplam</div><div class="stat-card-value"><?php echo $stats['total']; ?></div></div></div></div>
<div class="col-md-4"><div class="stat-card"><div class="stat-card-icon bg-success"><i class="bi bi-check-circle-fill"></i></div><div class="stat-card-info"><div class="stat-card-title">Aktif</div><div class="stat-card-value text-success"><?php echo $stats['active']; ?></div></div></div></div>
<div class="col-md-4"><div class="stat-card"><div class="stat-card-icon bg-secondary"><i class="bi bi-pause-circle-fill"></i></div><div class="stat-card-info"><div class="stat-card-title">Pasif</div><div class="stat-card-value"><?php echo $stats['inactive']; ?></div></div></div></div>
</div>
<div class="card">
<div class="card-header d-flex justify-content-between">
<h5>Tum Duyurular</h5>
<a href="add.php" class="btn btn-primary btn-sm">Yeni</a>
</div>
<div class="card-body">
<?php if(count($duyurular) > 0): ?>
<form method="POST" id="bulkForm">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
<input type="hidden" name="action" id="bulkAction">
<div class="mb-3">
<button type="button" class="btn btn-success btn-sm" onclick="bulkUpdate('activate')">Aktif Et</button>
<button type="button" class="btn btn-secondary btn-sm" onclick="bulkUpdate('deactivate')">Pasif Et</button>
</div>
<table class="table table-hover">
<thead><tr><th width="40"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th><th>ID</th><th>Tur</th><th>Baslik</th><th>Durum</th><th>Tarih</th><th>Islem</th></tr></thead>
<tbody>
<?php foreach($duyurular as $d): ?>
<tr>
<td><input type="checkbox" name="duyuru_ids[]" value="<?php echo $d['id']; ?>" class="row-checkbox"></td>
<td>#<?php echo $d['id']; ?></td>
<td><?php echo htmlspecialchars($d['type']); ?></td>
<td><strong><?php echo htmlspecialchars($d['title']); ?></strong></td>
<td><?php if($d['status'] === 'active'): ?><span class="badge bg-success">Aktif</span><?php else: ?><span class="badge bg-secondary">Pasif</span><?php endif; ?></td>
<td><?php echo date('d.m.Y', strtotime($d['created_at'])); ?></td>
<td>
<a href="edit.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
<a href="delete.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istiyor musunuz?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</form>
<?php else: ?>
<div class="text-center py-5"><h3>Henuz duyuru yok</h3><a href="add.php" class="btn btn-primary">Ilk duyuruyu ekle</a></div>
<?php endif; ?>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleAll(cb){var boxes=document.querySelectorAll('.row-checkbox');for(var i=0;i<boxes.length;i++)boxes[i].checked=cb.checked;}
function bulkUpdate(action){var checked=document.querySelectorAll('.row-checkbox:checked');if(checked.length===0){alert('Lutfen secim yapin!');return;}if(confirm('Islem yapilsin mi?')){document.getElementById('bulkAction').value=action;document.getElementById('bulkForm').submit();}}
</script>
</body>
</html>