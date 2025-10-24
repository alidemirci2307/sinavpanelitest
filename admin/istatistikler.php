<?php<?php

require_once __DIR__ . '/../config.php';session_start();

require_once __DIR__ . '/../db.php';if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {

require_once __DIR__ . '/../security.php';    header('Location: login.php');

    exit;

secureSessionStart();}



// Session kontrolü$host = "localhost";

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {$db     = "polisask_sinavpaneli";

    header('Location: login.php');$user = "polisask_sinavpaneli";

    exit;$pass = "Ankara2024++";

}

try {

$pdo = getDbConnection();    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

$page_title = "İstatistikler";    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$admin_username = $_SESSION['admin_username'] ?? 'Admin';} catch (PDOException $e) {

    die("Veritabanı bağlantısı hatası: " . $e->getMessage());

// Seçili packageName}

$selectedPackage = $_GET['packageName'] ?? '';

// Seçili packageName'i al

// Package listesi$selectedPackage = $_GET['packageName'] ?? '';

$packageStmt = $pdo->query("SELECT DISTINCT packageName FROM user_statistics ORDER BY packageName");

$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);// Mevcut packageName'leri al

$packageQuery = "SELECT DISTINCT packageName FROM user_statistics";

// Genel istatistikler$packageStmt = $pdo->query($packageQuery);

$whereClause = $selectedPackage ? "WHERE packageName = :package" : "";$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);

$params = $selectedPackage ? [':package' => $selectedPackage] : [];

// Son 30 günü al

// Bugün$endDate = new DateTime();

$todayQuery = "SELECT $startDate = (clone $endDate)->modify('-30 days');

    COUNT(*) as total_logins,

    COUNT(DISTINCT device_id) as unique_users// Tarih formatlama fonksiyonu

FROM user_statistics function formatTurkishDate($date, $format = '%d.%m.%Y %A') {

WHERE DATE(login_time) = CURDATE() {$whereClause}";    setlocale(LC_TIME, 'tr_TR.UTF-8');

$todayStmt = $pdo->prepare($todayQuery);    $timestamp = strtotime($date);

$todayStmt->execute($params);    return strftime($format, $timestamp);

$today = $todayStmt->fetch(PDO::FETCH_ASSOC);}



// Dün// Fark hesaplama fonksiyonu

$yesterdayQuery = "SELECT function calculateDifference($current, $previous) {

    COUNT(*) as total_logins,    if ($previous == 0) return 0;

    COUNT(DISTINCT device_id) as unique_users    return $current - $previous;

FROM user_statistics }

WHERE DATE(login_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) {$whereClause}";

$yesterdayStmt = $pdo->prepare($yesterdayQuery);// ** GÜNLÜK İSTATİSTİKLER **

$yesterdayStmt->execute($params);$dailyQuery = "SELECT

$yesterday = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);    DATE(login_time) AS day,

    COUNT(*) AS total_logins,

// Bu hafta    COUNT(DISTINCT device_id) AS unique_users,

$thisWeekQuery = "SELECT     COUNT(DISTINCT CASE WHEN NOT EXISTS (

    COUNT(*) as total_logins,        SELECT 1 FROM user_statistics us2

    COUNT(DISTINCT device_id) as unique_users        WHERE us2.device_id = us.device_id

FROM user_statistics         AND us2.login_time < us.login_time

WHERE YEARWEEK(login_time, 1) = YEARWEEK(CURDATE(), 1) {$whereClause}";    ) THEN us.device_id END) AS new_users

$thisWeekStmt = $pdo->prepare($thisWeekQuery);FROM user_statistics us

$thisWeekStmt->execute($params);WHERE (:packageName = '' OR packageName = :packageName)

$thisWeek = $thisWeekStmt->fetch(PDO::FETCH_ASSOC);AND DATE(login_time) BETWEEN :startDate AND :endDate

GROUP BY DATE(login_time)

// Bu ayORDER BY DATE(login_time) DESC";

$thisMonthQuery = "SELECT 

    COUNT(*) as total_logins,$dailyStmt = $pdo->prepare($dailyQuery);

    COUNT(DISTINCT device_id) as unique_users$dailyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);

FROM user_statistics $dailyStatistics = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

WHERE YEAR(login_time) = YEAR(CURDATE()) AND MONTH(login_time) = MONTH(CURDATE()) {$whereClause}";

$thisMonthStmt = $pdo->prepare($thisMonthQuery);// Günlük önceki gün kıyaslama için dizi

$thisMonthStmt->execute($params);$dailyPreviousStats = [];

$thisMonth = $thisMonthStmt->fetch(PDO::FETCH_ASSOC);foreach ($dailyStatistics as $key => $stat) {

    if (isset($dailyStatistics[$key + 1])) {

// Toplam        $dailyPreviousStats[$stat['day']] = $dailyStatistics[$key + 1];

$totalQuery = "SELECT     }

    COUNT(*) as total_logins,}

    COUNT(DISTINCT device_id) as unique_users

FROM user_statistics {$whereClause}";// ** HAFTALIK İSTATİSTİKLER **

$totalStmt = $pdo->prepare($totalQuery);$weeklyQuery = "SELECT

$totalStmt->execute($params);    YEARWEEK(login_time, 3) AS week_no,

$total = $totalStmt->fetch(PDO::FETCH_ASSOC);    DATE_FORMAT(MIN(login_time), '%Y-%m-%d') AS start_date,

    DATE_FORMAT(MAX(login_time), '%Y-%m-%d') AS end_date,

// Son 30 günlük günlük istatistikler (grafik için)    COUNT(*) AS total_logins,

$dailyQuery = "SELECT     COUNT(DISTINCT device_id) AS unique_users,

    DATE(login_time) as day,    COUNT(DISTINCT CASE WHEN NOT EXISTS (

    COUNT(*) as logins,        SELECT 1 FROM user_statistics us2

    COUNT(DISTINCT device_id) as unique_users        WHERE us2.device_id = us.device_id

FROM user_statistics         AND us2.login_time < us.login_time

WHERE login_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) {$whereClause}    ) THEN us.device_id END) AS new_users

GROUP BY DATE(login_time)FROM user_statistics us

ORDER BY day ASC";WHERE (:packageName = '' OR packageName = :packageName)

$dailyStmt = $pdo->prepare($dailyQuery);AND DATE(login_time) BETWEEN :startDate AND :endDate

$dailyStmt->execute($params);GROUP BY YEARWEEK(login_time, 3)

$dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);ORDER BY YEARWEEK(login_time, 3) DESC";



// Grafik verileri için JSON$weeklyStmt = $pdo->prepare($weeklyQuery);

$chartLabels = [];$weeklyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);

$chartLogins = [];$weeklyStatistics = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

$chartUsers = [];

foreach($dailyData as $row) {// Haftalık önceki hafta kıyaslama fonksiyonu

    $chartLabels[] = date('d M', strtotime($row['day']));function getPreviousWeekStats(&$weeklyStatistics, $currentWeekNo) {

    $chartLogins[] = $row['logins'];    foreach ($weeklyStatistics as $stat) {

    $chartUsers[] = $row['unique_users'];        if ($stat['week_no'] == $currentWeekNo) {

}            $currentIndex = array_search($stat, $weeklyStatistics);

?>            if (isset($weeklyStatistics[$currentIndex + 1])) {

<!DOCTYPE html>                return $weeklyStatistics[$currentIndex + 1];

<html lang="tr">            }

<head>            break;

    <meta charset="UTF-8">        }

    <meta name="viewport" content="width=device-width, initial-scale=1.0">    }

    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>    return null;

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/admin-style.css">// ** AYLIK İSTATİSTİKLER **

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>$monthlyQuery = "SELECT

</head>    DATE_FORMAT(login_time, '%Y-%m') AS month_year,

<body>    DATE_FORMAT(MIN(login_time), '%Y-%m-%d') AS start_date,

    <!-- Navbar -->    DATE_FORMAT(MAX(login_time), '%Y-%m-%d') AS end_date,

    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">    COUNT(*) AS total_logins,

        <div class="container-fluid">    COUNT(DISTINCT device_id) AS unique_users,

            <a class="navbar-brand" href="index.php">    COUNT(DISTINCT CASE WHEN NOT EXISTS (

                <i class="bi bi-shield-check"></i> Admin Panel        SELECT 1 FROM user_statistics us2

            </a>        WHERE us2.device_id = us.device_id

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">        AND us2.login_time < us.login_time

                <span class="navbar-toggler-icon"></span>    ) THEN us.device_id END) AS new_users

            </button>FROM user_statistics us

            <div class="collapse navbar-collapse" id="navbarNav">WHERE (:packageName = '' OR packageName = :packageName)

                <ul class="navbar-nav me-auto">AND DATE(login_time) BETWEEN :startDate AND :endDate

                    <li class="nav-item">GROUP BY DATE_FORMAT(login_time, '%Y-%m')

                        <a class="nav-link" href="index.php">ORDER BY DATE_FORMAT(login_time, '%Y-%m') DESC";

                            <i class="bi bi-speedometer2"></i> Dashboard

                        </a>$monthlyStmt = $pdo->prepare($monthlyQuery);

                    </li>$monthlyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);

                    <li class="nav-item">$monthlyStatistics = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

                        <a class="nav-link" href="duyurular/">

                            <i class="bi bi-megaphone"></i> Duyurular// Aylık önceki ay kıyaslama fonksiyonu

                        </a>function getPreviousMonthStats(&$monthlyStatistics, $currentMonthYear) {

                    </li>    foreach ($monthlyStatistics as $stat) {

                    <li class="nav-item">        if ($stat['month_year'] == $currentMonthYear) {

                        <a class="nav-link active" href="istatistikler.php">            $currentIndex = array_search($stat, $monthlyStatistics);

                            <i class="bi bi-graph-up"></i> İstatistikler            if (isset($monthlyStatistics[$currentIndex + 1])) {

                        </a>                return $monthlyStatistics[$currentIndex + 1];

                    </li>            }

                </ul>            break;

                <div class="d-flex align-items-center">        }

                    <span class="text-white me-3">    }

                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?>    return null;

                    </span>}

                    <a href="logout.php" class="btn btn-outline-light btn-sm">

                        <i class="bi bi-box-arrow-right"></i> Çıkış

                    </a>?>

                </div><!DOCTYPE html>

            </div><html lang="tr">

        </div><head>

    </nav>    <meta charset="UTF-8">

    <title>Admin Paneli - İstatistikler</title>

    <!-- Main Content -->    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <div class="container-fluid py-4">    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

        <div class="row mb-4">    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

            <div class="col-md-6">    <style>

                <h1 class="page-title">        .positive { color: green; font-weight: bold; }

                    <i class="bi bi-graph-up-arrow"></i> İstatistikler        .negative { color: red; font-weight: bold; }

                </h1>    </style>

            </div></head>

            <div class="col-md-6 text-end"><body class="bg-light">

                <form method="GET" class="d-inline-block">

                    <select name="packageName" class="form-select form-select-sm" style="width: auto; display: inline-block;" onchange="this.form.submit()"><nav class="navbar navbar-expand-lg navbar-dark bg-dark">

                        <option value="">Tüm Uygulamalar</option>    <div class="container-fluid">

                        <?php foreach($packages as $pkg): ?>        <a class="navbar-brand" href="../index.php">Admin Paneli</a>

                            <option value="<?php echo htmlspecialchars($pkg); ?>" <?php echo $selectedPackage === $pkg ? 'selected' : ''; ?>>        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">

                                <?php echo htmlspecialchars($pkg); ?>            <span class="navbar-toggler-icon"></span>

                            </option>        </button>

                        <?php endforeach; ?>        <div class="collapse navbar-collapse" id="navbarNav">

                    </select>            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                </form>                <li class="nav-item"><a class="nav-link" href="index.php">Talepler</a></li>

            </div>                <li class="nav-item"><a class="nav-link" href="duyurular/index.php">Duyurular</a></li>

        </div>                <li class="nav-item"><a class="nav-link active" href="../istatistikler.php">İstatistikler</a></li>

            </ul>

        <!-- İstatistik Kartları -->            <div class="d-flex">

        <div class="row mb-4">                <a class="btn btn-outline-light" href="../logout.php">Çıkış Yap</a>

            <!-- Bugün -->            </div>

            <div class="col-lg-3 col-md-6 mb-3">        </div>

                <div class="stat-card">    </div>

                    <div class="stat-card-icon bg-primary"></nav>

                        <i class="bi bi-calendar-day"></i>

                    </div><div class="container mt-4">

                    <div class="stat-card-info">    <h5 class="text-center">Son 30 Günlük İstatistikler</h5>

                        <div class="stat-card-title">Bugün</div>

                        <div class="stat-card-value"><?php echo number_format($today['total_logins']); ?></div>    <form method="GET" class="mb-3">

                        <div class="stat-card-change text-muted">        <label for="packageName" class="form-label">Uygulama Seç:</label>

                            <small><?php echo number_format($today['unique_users']); ?> benzersiz kullanıcı</small>        <select name="packageName" id="packageName" class="form-select" onchange="this.form.submit()">

                        </div>            <option value="">Tümü</option>

                    </div>            <?php foreach ($packages as $package): ?>

                </div>                <option value="<?= htmlspecialchars($package) ?>" <?= $package == $selectedPackage ? 'selected' : '' ?>>

            </div>                    <?= htmlspecialchars($package) ?>

                </option>

            <!-- Dün -->            <?php endforeach; ?>

            <div class="col-lg-3 col-md-6 mb-3">        </select>

                <div class="stat-card">    </form>

                    <div class="stat-card-icon bg-info">

                        <i class="bi bi-calendar-minus"></i>    <ul class="nav nav-tabs" id="myTab" role="tablist">

                    </div>        <li class="nav-item" role="presentation">

                    <div class="stat-card-info">            <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab" aria-controls="daily" aria-selected="true">Günlük</button>

                        <div class="stat-card-title">Dün</div>        </li>

                        <div class="stat-card-value"><?php echo number_format($yesterday['total_logins']); ?></div>        <li class="nav-item" role="presentation">

                        <div class="stat-card-change text-muted">            <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab" aria-controls="weekly" aria-selected="false">Haftalık</button>

                            <small><?php echo number_format($yesterday['unique_users']); ?> benzersiz kullanıcı</small>        </li>

                        </div>        <li class="nav-item" role="presentation">

                    </div>            <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab" aria-controls="monthly" aria-selected="false">Aylık</button>

                </div>        </li>

            </div>    </ul>



            <!-- Bu Hafta -->    <div class="tab-content" id="myTabContent">

            <div class="col-lg-3 col-md-6 mb-3">        <div class="tab-pane fade show active" id="daily" role="tabpanel" aria-labelledby="daily-tab">

                <div class="stat-card">            <div class="table-responsive mt-3">

                    <div class="stat-card-icon bg-success">                <table class="table table-bordered table-striped table-hover align-middle">

                        <i class="bi bi-calendar-week"></i>                    <thead class="table-dark">

                    </div>                        <tr>

                    <div class="stat-card-info">                            <th>Tarih</th>

                        <div class="stat-card-title">Bu Hafta</div>                            <th>Toplam Giriş</th>

                        <div class="stat-card-value"><?php echo number_format($thisWeek['total_logins']); ?></div>                            <th>Eşsiz Kullanıcı</th>

                        <div class="stat-card-change text-muted">                            <th>Yeni Kullanıcılar</th>

                            <small><?php echo number_format($thisWeek['unique_users']); ?> benzersiz kullanıcı</small>                            <th>Dün ile Kıyaslama</th>

                        </div>                        </tr>

                    </div>                    </thead>

                </div>                    <tbody>

            </div>                        <?php foreach ($dailyStatistics as $stat): ?>

                        <tr>

            <!-- Bu Ay -->                            <td><?= formatTurkishDate($stat['day']) ?></td>

            <div class="col-lg-3 col-md-6 mb-3">                            <td><?= $stat['total_logins'] ?></td>

                <div class="stat-card">                            <td><?= $stat['unique_users'] ?></td>

                    <div class="stat-card-icon bg-warning">                            <td><?= $stat['new_users'] ?></td>

                        <i class="bi bi-calendar-month"></i>                            <td>

                    </div>                                <?php if (isset($dailyPreviousStats[$stat['day']])): ?>

                    <div class="stat-card-info">                                    <?php

                        <div class="stat-card-title">Bu Ay</div>                                    $diffTotalLogins = calculateDifference($stat['total_logins'], $dailyPreviousStats[$stat['day']]['total_logins']);

                        <div class="stat-card-value"><?php echo number_format($thisMonth['total_logins']); ?></div>                                    $diffUniqueUsers = calculateDifference($stat['unique_users'], $dailyPreviousStats[$stat['day']]['unique_users']);

                        <div class="stat-card-change text-muted">                                    $diffNewUsers = calculateDifference($stat['new_users'], $dailyPreviousStats[$stat['day']]['new_users']);

                            <small><?php echo number_format($thisMonth['unique_users']); ?> benzersiz kullanıcı</small>                                    ?>

                        </div>                                    Toplam Giriş: <span class="<?= $diffTotalLogins >= 0 ? 'positive' : 'negative' ?>"><?= $diffTotalLogins >= 0 ? '+' : '' ?><?= $diffTotalLogins ?></span><br>

                    </div>                                    Eşsiz Kullanıcı: <span class="<?= $diffUniqueUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffUniqueUsers >= 0 ? '+' : '' ?><?= $diffUniqueUsers ?></span><br>

                </div>                                    Yeni Kullanıcılar: <span class="<?= $diffNewUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffNewUsers >= 0 ? '+' : '' ?><?= $diffNewUsers ?></span>

            </div>                                <?php else: ?>

        </div>                                    Veri Yok

                                <?php endif; ?>

        <!-- Toplam İstatistikler -->                            </td>

        <div class="row mb-4">                        </tr>

            <div class="col-md-6 mb-3">                        <?php endforeach; ?>

                <div class="card">                        <?php if (empty($dailyStatistics)): ?>

                    <div class="card-body text-center">                        <tr>

                        <h3 class="text-primary"><?php echo number_format($total['total_logins']); ?></h3>                            <td colspan="5" class="text-center">Henüz günlük istatistik yok.</td>

                        <p class="text-muted mb-0">Toplam Giriş</p>                        </tr>

                    </div>                        <?php endif; ?>

                </div>                    </tbody>

            </div>                </table>

            <div class="col-md-6 mb-3">            </div>

                <div class="card">        </div>

                    <div class="card-body text-center">

                        <h3 class="text-success"><?php echo number_format($total['unique_users']); ?></h3>        <div class="tab-pane fade" id="weekly" role="tabpanel" aria-labelledby="weekly-tab">

                        <p class="text-muted mb-0">Toplam Benzersiz Kullanıcı</p>            <div class="table-responsive mt-3">

                    </div>                <table class="table table-bordered table-striped table-hover align-middle">

                </div>                    <thead class="table-dark">

            </div>                        <tr>

        </div>                            <th>Hafta</th>

                            <th>Toplam Giriş</th>

        <!-- Grafik -->                            <th>Eşsiz Kullanıcı</th>

        <div class="row mb-4">                            <th>Yeni Kullanıcılar</th>

            <div class="col-12">                            <th>Önceki Hafta ile Kıyaslama</th>

                <div class="card">                        </tr>

                    <div class="card-header">                    </thead>

                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Son 30 Gün Aktivitesi</h5>                    <tbody>

                    </div>                        <?php foreach ($weeklyStatistics as $stat): ?>

                    <div class="card-body">                        <tr>

                        <canvas id="activityChart" height="80"></canvas>                            <td><?= formatTurkishDate($stat['start_date'], '%d.%m.%Y') ?> - <?= formatTurkishDate($stat['end_date'], '%d.%m.%Y') ?></td>

                    </div>                            <td><?= $stat['total_logins'] ?></td>

                </div>                            <td><?= $stat['unique_users'] ?></td>

            </div>                            <td><?= $stat['new_users'] ?></td>

        </div>                            <td>

                                <?php

        <!-- Günlük Tablo -->                                    $previousWeekStat = getPreviousWeekStats($weeklyStatistics, $stat['week_no']);

        <div class="row">                                    if ($previousWeekStat):

            <div class="col-12">                                        $diffTotalLogins = calculateDifference($stat['total_logins'], $previousWeekStat['total_logins']);

                <div class="card">                                        $diffUniqueUsers = calculateDifference($stat['unique_users'], $previousWeekStat['unique_users']);

                    <div class="card-header">                                        $diffNewUsers = calculateDifference($stat['new_users'], $previousWeekStat['new_users']);

                        <h5 class="mb-0"><i class="bi bi-table"></i> Günlük Detaylar (Son 30 Gün)</h5>                                    ?>

                    </div>                                        Toplam Giriş: <span class="<?= $diffTotalLogins >= 0 ? 'positive' : 'negative' ?>"><?= $diffTotalLogins >= 0 ? '+' : '' ?><?= $diffTotalLogins ?></span><br>

                    <div class="card-body">                                        Eşsiz Kullanıcı: <span class="<?= $diffUniqueUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffUniqueUsers >= 0 ? '+' : '' ?><?= $diffUniqueUsers ?></span><br>

                        <div class="table-responsive">                                        Yeni Kullanıcılar: <span class="<?= $diffNewUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffNewUsers >= 0 ? '+' : '' ?><?= $diffNewUsers ?></span>

                            <table class="table table-hover">                                    <?php else: ?>

                                <thead>                                        Veri Yok

                                    <tr>                                    <?php endif; ?>

                                        <th>Tarih</th>                            </td>

                                        <th>Gün</th>                        </tr>

                                        <th>Toplam Giriş</th>                        <?php endforeach; ?>

                                        <th>Benzersiz Kullanıcı</th>                        <?php if (empty($weeklyStatistics)): ?>

                                        <th>Ortalama Giriş/Kullanıcı</th>                        <tr>

                                    </tr>                            <td colspan="5" class="text-center">Henüz haftalık istatistik yok.</td>

                                </thead>                        </tr>

                                <tbody>                        <?php endif; ?>

                                    <?php if(count($dailyData) > 0): ?>                    </tbody>

                                        <?php                 </table>

                                        // Tersten sırala (en yeni önce)            </div>

                                        $reversedData = array_reverse($dailyData);        </div>

                                        foreach($reversedData as $row): 

                                            $avgPerUser = $row['unique_users'] > 0 ? round($row['logins'] / $row['unique_users'], 2) : 0;        <div class="tab-pane fade" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">

                                            $dayName = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'][date('N', strtotime($row['day'])) - 1];            <div class="table-responsive mt-3">

                                        ?>                <table class="table table-bordered table-striped table-hover align-middle">

                                        <tr>                    <thead class="table-dark">

                                            <td><?php echo date('d.m.Y', strtotime($row['day'])); ?></td>                        <tr>

                                            <td><span class="badge bg-light text-dark"><?php echo $dayName; ?></span></td>                            <th>Ay</th>

                                            <td><strong><?php echo number_format($row['logins']); ?></strong></td>                            <th>Toplam Giriş</th>

                                            <td><?php echo number_format($row['unique_users']); ?></td>                            <th>Eşsiz Kullanıcı</th>

                                            <td><?php echo number_format($avgPerUser, 2); ?></td>                            <th>Yeni Kullanıcılar</th>

                                        </tr>                            <th>Önceki Ay ile Kıyaslama</th>

                                        <?php endforeach; ?>                        </tr>

                                    <?php else: ?>                    </thead>

                                        <tr>                    <tbody>

                                            <td colspan="5" class="text-center text-muted">Henüz veri yok</td>                        <?php foreach ($monthlyStatistics as $stat): ?>

                                        </tr>                        <tr>

                                    <?php endif; ?>                            <td><?= formatTurkishDate($stat['start_date'], '%m.%Y') ?></td>

                                </tbody>                            <td><?= $stat['total_logins'] ?></td>

                            </table>                            <td><?= $stat['unique_users'] ?></td>

                        </div>                            <td><?= $stat['new_users'] ?></td>

                    </div>                            <td>

                </div>                                <?php

            </div>                                    $previousMonthStat = getPreviousMonthStats($monthlyStatistics, $stat['month_year']);

        </div>                                    if ($previousMonthStat):

    </div>                                        $diffTotalLogins = calculateDifference($stat['total_logins'], $previousMonthStat['total_logins']);

                                        $diffUniqueUsers = calculateDifference($stat['unique_users'], $previousMonthStat['unique_users']);

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>                                        $diffNewUsers = calculateDifference($stat['new_users'], $previousMonthStat['new_users']);

    <script>                                    ?>

        // Grafik                                        Toplam Giriş: <span class="<?= $diffTotalLogins >= 0 ? 'positive' : 'negative' ?>"><?= $diffTotalLogins >= 0 ? '+' : '' ?><?= $diffTotalLogins ?></span><br>

        const ctx = document.getElementById('activityChart').getContext('2d');                                        Eşsiz Kullanıcı: <span class="<?= $diffUniqueUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffUniqueUsers >= 0 ? '+' : '' ?><?= $diffUniqueUsers ?></span><br>

        new Chart(ctx, {                                        Yeni Kullanıcılar: <span class="<?= $diffNewUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffNewUsers >= 0 ? '+' : '' ?><?= $diffNewUsers ?></span>

            type: 'line',                                    <?php else: ?>

            data: {                                        Veri Yok

                labels: <?php echo json_encode($chartLabels); ?>,                                    <?php endif; ?>

                datasets: [                            </td>

                    {                        </tr>

                        label: 'Toplam Giriş',                        <?php endforeach; ?>

                        data: <?php echo json_encode($chartLogins); ?>,                        <?php if (empty($monthlyStatistics)): ?>

                        borderColor: 'rgb(102, 126, 234)',                        <tr>

                        backgroundColor: 'rgba(102, 126, 234, 0.1)',                            <td colspan="5" class="text-center">Henüz aylık istatistik yok.</td>

                        tension: 0.3,                        </tr>

                        fill: true                        <?php endif; ?>

                    },                    </tbody>

                    {                </table>

                        label: 'Benzersiz Kullanıcı',            </div>

                        data: <?php echo json_encode($chartUsers); ?>,        </div>

                        borderColor: 'rgb(40, 167, 69)',    </div>

                        backgroundColor: 'rgba(40, 167, 69, 0.1)',</div>

                        tension: 0.3,</body>

                        fill: true</html>
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
