<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDbConnection();
$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$selectedPackage = isset($_GET['packageName']) ? $_GET['packageName'] : '';

$packageStmt = $pdo->query("SELECT DISTINCT packageName FROM user_statistics ORDER BY packageName");
$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);

$params = $selectedPackage ? array(':package' => $selectedPackage) : array();
$whereSQL = $selectedPackage ? "AND packageName = :package" : "";

$todayStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE DATE(login_time) = CURDATE() $whereSQL");
$todayStmt->execute($params);
$today = $todayStmt->fetch(PDO::FETCH_ASSOC);

$yesterdayStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE DATE(login_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) $whereSQL");
$yesterdayStmt->execute($params);
$yesterday = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);

$weekStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE YEARWEEK(login_time, 1) = YEARWEEK(CURDATE(), 1) $whereSQL");
$weekStmt->execute($params);
$week = $weekStmt->fetch(PDO::FETCH_ASSOC);

$monthStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE YEAR(login_time) = YEAR(CURDATE()) AND MONTH(login_time) = MONTH(CURDATE()) $whereSQL");
$monthStmt->execute($params);
$month = $monthStmt->fetch(PDO::FETCH_ASSOC);

$dailyStmt = $pdo->prepare("SELECT DATE(login_time) as day, COUNT(*) as logins, COUNT(DISTINCT device_id) as users FROM user_statistics WHERE login_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) $whereSQL GROUP BY DATE(login_time) ORDER BY day ASC");
$dailyStmt->execute($params);
$dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = array();
$chartLogins = array();
$chartUsers = array();
foreach($dailyData as $row) {
    $chartLabels[] = date('d M', strtotime($row['day']));
    $chartLogins[] = $row['logins'];
    $chartUsers[] = $row['users'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Istatistikler</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/admin-style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
<div class="container-fluid">
<a class="navbar-brand" href="index.php"><i class="bi bi-shield-check"></i> Admin</a>
<div class="collapse navbar-collapse">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
<li class="nav-item"><a class="nav-link" href="duyurular/"><i class="bi bi-megaphone"></i> Duyurular</a></li>
<li class="nav-item"><a class="nav-link active" href="istatistikler.php"><i class="bi bi-graph-up"></i> Istatistikler</a></li>
</ul>
<div class="d-flex">
<span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?></span>
<a href="logout.php" class="btn btn-outline-light btn-sm">Cikis</a>
</div>
</div>
</div>
</nav>
<div class="container-fluid py-4">
<div class="row mb-4">
<div class="col-md-6"><h1>Istatistikler</h1></div>
<div class="col-md-6 text-end">
<form method="GET">
<select name="packageName" class="form-select form-select-sm" style="width:auto;display:inline-block" onchange="this.form.submit()">
<option value="">Tum Uygulamalar</option>
<?php foreach($packages as $pkg): ?>
<option value="<?php echo htmlspecialchars($pkg); ?>" <?php echo $selectedPackage === $pkg ? 'selected' : ''; ?>><?php echo htmlspecialchars($pkg); ?></option>
<?php endforeach; ?>
</select>
</form>
</div>
</div>
<div class="row mb-4">
<div class="col-lg-3 col-md-6 mb-3"><div class="stat-card"><div class="stat-card-icon bg-primary"><i class="bi bi-calendar-day"></i></div><div class="stat-card-info"><div class="stat-card-title">Bugun</div><div class="stat-card-value"><?php echo number_format($today['total']); ?></div><small class="text-muted"><?php echo number_format($today['unique_users']); ?> kullanici</small></div></div></div>
<div class="col-lg-3 col-md-6 mb-3"><div class="stat-card"><div class="stat-card-icon bg-info"><i class="bi bi-calendar-minus"></i></div><div class="stat-card-info"><div class="stat-card-title">Dun</div><div class="stat-card-value"><?php echo number_format($yesterday['total']); ?></div><small class="text-muted"><?php echo number_format($yesterday['unique_users']); ?> kullanici</small></div></div></div>
<div class="col-lg-3 col-md-6 mb-3"><div class="stat-card"><div class="stat-card-icon bg-success"><i class="bi bi-calendar-week"></i></div><div class="stat-card-info"><div class="stat-card-title">Bu Hafta</div><div class="stat-card-value"><?php echo number_format($week['total']); ?></div><small class="text-muted"><?php echo number_format($week['unique_users']); ?> kullanici</small></div></div></div>
<div class="col-lg-3 col-md-6 mb-3"><div class="stat-card"><div class="stat-card-icon bg-warning"><i class="bi bi-calendar-month"></i></div><div class="stat-card-info"><div class="stat-card-title">Bu Ay</div><div class="stat-card-value"><?php echo number_format($month['total']); ?></div><small class="text-muted"><?php echo number_format($month['unique_users']); ?> kullanici</small></div></div></div>
</div>
<div class="card mb-4">
<div class="card-header"><h5><i class="bi bi-graph-up"></i> Son 30 Gun</h5></div>
<div class="card-body"><canvas id="chart" height="80"></canvas></div>
</div>
<div class="card">
<div class="card-header"><h5><i class="bi bi-table"></i> Gunluk Detaylar</h5></div>
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover">
<thead><tr><th>Tarih</th><th>Gun</th><th>Giris</th><th>Kullanici</th></tr></thead>
<tbody>
<?php 
$reversed = array_reverse($dailyData);
foreach($reversed as $row): 
$days = array('Pzt','Sal','Car','Per','Cum','Cmt','Paz');
$day = $days[date('N', strtotime($row['day'])) - 1];
?>
<tr>
<td><?php echo date('d.m.Y', strtotime($row['day'])); ?></td>
<td><span class="badge bg-light text-dark"><?php echo $day; ?></span></td>
<td><strong><?php echo number_format($row['logins']); ?></strong></td>
<td><?php echo number_format($row['users']); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
new Chart(document.getElementById('chart'),{type:'line',data:{labels:<?php echo json_encode($chartLabels); ?>,datasets:[{label:'Giris',data:<?php echo json_encode($chartLogins); ?>,borderColor:'rgb(102,126,234)',backgroundColor:'rgba(102,126,234,0.1)',tension:0.3,fill:true},{label:'Kullanici',data:<?php echo json_encode($chartUsers); ?>,borderColor:'rgb(40,167,69)',backgroundColor:'rgba(40,167,69,0.1)',tension:0.3,fill:true}]},options:{responsive:true,scales:{y:{beginAtZero:true}}}});
</script>
</body>
</html>