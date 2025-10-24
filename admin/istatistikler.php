<?php<?php<?php<?php

ini_set('display_errors', 1);

error_reporting(E_ALL);require_once __DIR__ . '/../config.php';



require_once __DIR__ . '/../config.php';require_once __DIR__ . '/../db.php';require_once __DIR__ . '/../config.php';session_start();

require_once __DIR__ . '/../db.php';

require_once __DIR__ . '/../security.php';require_once __DIR__ . '/../security.php';



secureSessionStart();require_once __DIR__ . '/../db.php';if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {



if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {secureSessionStart();

    header('Location: login.php');

    exit;require_once __DIR__ . '/../security.php';    header('Location: login.php');

}

// Session kontrolü

$pdo = getDbConnection();

$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {    exit;

$selectedPackage = isset($_GET['packageName']) ? $_GET['packageName'] : '';

    header('Location: login.php');

// Package listesi

$packageStmt = $pdo->query("SELECT DISTINCT packageName FROM user_statistics ORDER BY packageName");    exit;secureSessionStart();}

$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);

}

$params = $selectedPackage ? array(':package' => $selectedPackage) : array();

$whereSQL = $selectedPackage ? "AND packageName = :package" : "";



// Bugün$pdo = getDbConnection();

$todayStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE DATE(login_time) = CURDATE() $whereSQL");

$todayStmt->execute($params);$page_title = "İstatistikler";// Session kontrolü$host = "localhost";

$today = $todayStmt->fetch(PDO::FETCH_ASSOC);

$admin_username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';

// Dün

$yesterdayStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE DATE(login_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) $whereSQL");if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {$db     = "polisask_sinavpaneli";

$yesterdayStmt->execute($params);

$yesterday = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);// Seçili packageName



// Bu hafta$selectedPackage = isset($_GET['packageName']) ? $_GET['packageName'] : '';    header('Location: login.php');$user = "polisask_sinavpaneli";

$weekStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE YEARWEEK(login_time, 1) = YEARWEEK(CURDATE(), 1) $whereSQL");

$weekStmt->execute($params);

$week = $weekStmt->fetch(PDO::FETCH_ASSOC);

// Package listesi    exit;$pass = "Ankara2024++";

// Bu ay

$monthStmt = $pdo->prepare("SELECT COUNT(*) as total, COUNT(DISTINCT device_id) as unique_users FROM user_statistics WHERE YEAR(login_time) = YEAR(CURDATE()) AND MONTH(login_time) = MONTH(CURDATE()) $whereSQL");$packageStmt = $pdo->query("SELECT DISTINCT packageName FROM user_statistics ORDER BY packageName");

$monthStmt->execute($params);

$month = $monthStmt->fetch(PDO::FETCH_ASSOC);$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);}



// Son 30 gün

$dailyStmt = $pdo->prepare("SELECT DATE(login_time) as day, COUNT(*) as logins, COUNT(DISTINCT device_id) as users FROM user_statistics WHERE login_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) $whereSQL GROUP BY DATE(login_time) ORDER BY day ASC");

$dailyStmt->execute($params);// Genel istatistiklertry {

$dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

$whereClause = $selectedPackage ? "WHERE packageName = :package" : "";

$chartLabels = array();

$chartLogins = array();$params = $selectedPackage ? array(':package' => $selectedPackage) : array();$pdo = getDbConnection();    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

$chartUsers = array();

foreach($dailyData as $row) {

    $chartLabels[] = date('d M', strtotime($row['day']));

    $chartLogins[] = $row['logins'];// Bugün$page_title = "İstatistikler";    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $chartUsers[] = $row['users'];

}$todayQuery = "SELECT 

?>

<!DOCTYPE html>    COUNT(*) as total_logins,$admin_username = $_SESSION['admin_username'] ?? 'Admin';} catch (PDOException $e) {

<html lang="tr">

<head>    COUNT(DISTINCT device_id) as unique_users

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">FROM user_statistics     die("Veritabanı bağlantısı hatası: " . $e->getMessage());

    <title>İstatistikler - Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">WHERE DATE(login_time) = CURDATE() " . ($selectedPackage ? "AND packageName = :package" : "");

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="assets/css/admin-style.css">$todayStmt = $pdo->prepare($todayQuery);// Seçili packageName}

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

</head>$todayStmt->execute($params);

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">$today = $todayStmt->fetch(PDO::FETCH_ASSOC);$selectedPackage = $_GET['packageName'] ?? '';

        <div class="container-fluid">

            <a class="navbar-brand" href="index.php"><i class="bi bi-shield-check"></i> Admin Panel</a>

            <div class="collapse navbar-collapse">

                <ul class="navbar-nav me-auto">// Dün// Seçili packageName'i al

                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

                    <li class="nav-item"><a class="nav-link" href="duyurular/"><i class="bi bi-megaphone"></i> Duyurular</a></li>$yesterdayQuery = "SELECT 

                    <li class="nav-item"><a class="nav-link active" href="istatistikler.php"><i class="bi bi-graph-up"></i> İstatistikler</a></li>

                </ul>    COUNT(*) as total_logins,// Package listesi$selectedPackage = $_GET['packageName'] ?? '';

                <div class="d-flex align-items-center">

                    <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?></span>    COUNT(DISTINCT device_id) as unique_users

                    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Çıkış</a>

                </div>FROM user_statistics $packageStmt = $pdo->query("SELECT DISTINCT packageName FROM user_statistics ORDER BY packageName");

            </div>

        </div>WHERE DATE(login_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) " . ($selectedPackage ? "AND packageName = :package" : "");

    </nav>

$yesterdayStmt = $pdo->prepare($yesterdayQuery);$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);// Mevcut packageName'leri al

    <div class="container-fluid py-4">

        <div class="row mb-4">$yesterdayStmt->execute($params);

            <div class="col-md-6">

                <h1 class="page-title"><i class="bi bi-graph-up-arrow"></i> İstatistikler</h1>$yesterday = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);$packageQuery = "SELECT DISTINCT packageName FROM user_statistics";

            </div>

            <div class="col-md-6 text-end">

                <form method="GET">

                    <select name="packageName" class="form-select form-select-sm" style="width:auto;display:inline-block" onchange="this.form.submit()">// Bu hafta// Genel istatistikler$packageStmt = $pdo->query($packageQuery);

                        <option value="">Tüm Uygulamalar</option>

                        <?php foreach($packages as $pkg): ?>$thisWeekQuery = "SELECT 

                        <option value="<?php echo htmlspecialchars($pkg); ?>" <?php echo $selectedPackage === $pkg ? 'selected' : ''; ?>>

                            <?php echo htmlspecialchars($pkg); ?>    COUNT(*) as total_logins,$whereClause = $selectedPackage ? "WHERE packageName = :package" : "";$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);

                        </option>

                        <?php endforeach; ?>    COUNT(DISTINCT device_id) as unique_users

                    </select>

                </form>FROM user_statistics $params = $selectedPackage ? [':package' => $selectedPackage] : [];

            </div>

        </div>WHERE YEARWEEK(login_time, 1) = YEARWEEK(CURDATE(), 1) " . ($selectedPackage ? "AND packageName = :package" : "");



        <!-- Kartlar -->$thisWeekStmt = $pdo->prepare($thisWeekQuery);// Son 30 günü al

        <div class="row mb-4">

            <div class="col-lg-3 col-md-6 mb-3">$thisWeekStmt->execute($params);

                <div class="stat-card">

                    <div class="stat-card-icon bg-primary"><i class="bi bi-calendar-day"></i></div>$thisWeek = $thisWeekStmt->fetch(PDO::FETCH_ASSOC);// Bugün$endDate = new DateTime();

                    <div class="stat-card-info">

                        <div class="stat-card-title">Bugün</div>

                        <div class="stat-card-value"><?php echo number_format($today['total']); ?></div>

                        <small class="text-muted"><?php echo number_format($today['unique_users']); ?> kullanıcı</small>// Bu ay$todayQuery = "SELECT $startDate = (clone $endDate)->modify('-30 days');

                    </div>

                </div>$thisMonthQuery = "SELECT 

            </div>

            <div class="col-lg-3 col-md-6 mb-3">    COUNT(*) as total_logins,    COUNT(*) as total_logins,

                <div class="stat-card">

                    <div class="stat-card-icon bg-info"><i class="bi bi-calendar-minus"></i></div>    COUNT(DISTINCT device_id) as unique_users

                    <div class="stat-card-info">

                        <div class="stat-card-title">Dün</div>FROM user_statistics     COUNT(DISTINCT device_id) as unique_users// Tarih formatlama fonksiyonu

                        <div class="stat-card-value"><?php echo number_format($yesterday['total']); ?></div>

                        <small class="text-muted"><?php echo number_format($yesterday['unique_users']); ?> kullanıcı</small>WHERE YEAR(login_time) = YEAR(CURDATE()) AND MONTH(login_time) = MONTH(CURDATE()) " . ($selectedPackage ? "AND packageName = :package" : "");

                    </div>

                </div>$thisMonthStmt = $pdo->prepare($thisMonthQuery);FROM user_statistics function formatTurkishDate($date, $format = '%d.%m.%Y %A') {

            </div>

            <div class="col-lg-3 col-md-6 mb-3">$thisMonthStmt->execute($params);

                <div class="stat-card">

                    <div class="stat-card-icon bg-success"><i class="bi bi-calendar-week"></i></div>$thisMonth = $thisMonthStmt->fetch(PDO::FETCH_ASSOC);WHERE DATE(login_time) = CURDATE() {$whereClause}";    setlocale(LC_TIME, 'tr_TR.UTF-8');

                    <div class="stat-card-info">

                        <div class="stat-card-title">Bu Hafta</div>

                        <div class="stat-card-value"><?php echo number_format($week['total']); ?></div>

                        <small class="text-muted"><?php echo number_format($week['unique_users']); ?> kullanıcı</small>// Toplam$todayStmt = $pdo->prepare($todayQuery);    $timestamp = strtotime($date);

                    </div>

                </div>$totalQuery = "SELECT 

            </div>

            <div class="col-lg-3 col-md-6 mb-3">    COUNT(*) as total_logins,$todayStmt->execute($params);    return strftime($format, $timestamp);

                <div class="stat-card">

                    <div class="stat-card-icon bg-warning"><i class="bi bi-calendar-month"></i></div>    COUNT(DISTINCT device_id) as unique_users

                    <div class="stat-card-info">

                        <div class="stat-card-title">Bu Ay</div>FROM user_statistics " . $whereClause;$today = $todayStmt->fetch(PDO::FETCH_ASSOC);}

                        <div class="stat-card-value"><?php echo number_format($month['total']); ?></div>

                        <small class="text-muted"><?php echo number_format($month['unique_users']); ?> kullanıcı</small>$totalStmt = $pdo->prepare($totalQuery);

                    </div>

                </div>$totalStmt->execute($params);

            </div>

        </div>$total = $totalStmt->fetch(PDO::FETCH_ASSOC);



        <!-- Grafik -->// Dün// Fark hesaplama fonksiyonu

        <div class="card mb-4">

            <div class="card-header"><h5><i class="bi bi-graph-up"></i> Son 30 Gün</h5></div>// Son 30 günlük günlük istatistikler

            <div class="card-body">

                <canvas id="chart" height="80"></canvas>$dailyQuery = "SELECT $yesterdayQuery = "SELECT function calculateDifference($current, $previous) {

            </div>

        </div>    DATE(login_time) as day,



        <!-- Tablo -->    COUNT(*) as logins,    COUNT(*) as total_logins,    if ($previous == 0) return 0;

        <div class="card">

            <div class="card-header"><h5><i class="bi bi-table"></i> Günlük Detaylar</h5></div>    COUNT(DISTINCT device_id) as unique_users

            <div class="card-body">

                <div class="table-responsive">FROM user_statistics     COUNT(DISTINCT device_id) as unique_users    return $current - $previous;

                    <table class="table table-hover">

                        <thead>WHERE login_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) " . ($selectedPackage ? "AND packageName = :package" : "") . "

                            <tr>

                                <th>Tarih</th>GROUP BY DATE(login_time)FROM user_statistics }

                                <th>Gün</th>

                                <th>Giriş</th>ORDER BY day ASC";

                                <th>Kullanıcı</th>

                            </tr>$dailyStmt = $pdo->prepare($dailyQuery);WHERE DATE(login_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) {$whereClause}";

                        </thead>

                        <tbody>$dailyStmt->execute($params);

                            <?php 

                            $reversed = array_reverse($dailyData);$dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);$yesterdayStmt = $pdo->prepare($yesterdayQuery);// ** GÜNLÜK İSTATİSTİKLER **

                            foreach($reversed as $row): 

                                $days = array('Pzt','Sal','Çar','Per','Cum','Cmt','Paz');

                                $day = $days[date('N', strtotime($row['day'])) - 1];

                            ?>// Grafik verileri$yesterdayStmt->execute($params);$dailyQuery = "SELECT

                            <tr>

                                <td><?php echo date('d.m.Y', strtotime($row['day'])); ?></td>$chartLabels = array();

                                <td><span class="badge bg-light text-dark"><?php echo $day; ?></span></td>

                                <td><strong><?php echo number_format($row['logins']); ?></strong></td>$chartLogins = array();$yesterday = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);    DATE(login_time) AS day,

                                <td><?php echo number_format($row['users']); ?></td>

                            </tr>$chartUsers = array();

                            <?php endforeach; ?>

                        </tbody>foreach($dailyData as $row) {    COUNT(*) AS total_logins,

                    </table>

                </div>    $chartLabels[] = date('d M', strtotime($row['day']));

            </div>

        </div>    $chartLogins[] = $row['logins'];// Bu hafta    COUNT(DISTINCT device_id) AS unique_users,

    </div>

    $chartUsers[] = $row['unique_users'];

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>}$thisWeekQuery = "SELECT     COUNT(DISTINCT CASE WHEN NOT EXISTS (

        new Chart(document.getElementById('chart'), {

            type: 'line',?>

            data: {

                labels: <?php echo json_encode($chartLabels); ?>,<!DOCTYPE html>    COUNT(*) as total_logins,        SELECT 1 FROM user_statistics us2

                datasets: [{

                    label: 'Giriş',<html lang="tr">

                    data: <?php echo json_encode($chartLogins); ?>,

                    borderColor: 'rgb(102,126,234)',<head>    COUNT(DISTINCT device_id) as unique_users        WHERE us2.device_id = us.device_id

                    backgroundColor: 'rgba(102,126,234,0.1)',

                    tension: 0.3,    <meta charset="UTF-8">

                    fill: true

                }, {    <meta name="viewport" content="width=device-width, initial-scale=1.0">FROM user_statistics         AND us2.login_time < us.login_time

                    label: 'Kullanıcı',

                    data: <?php echo json_encode($chartUsers); ?>,    <title>İstatistikler - Admin Panel</title>

                    borderColor: 'rgb(40,167,69)',

                    backgroundColor: 'rgba(40,167,69,0.1)',    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">WHERE YEARWEEK(login_time, 1) = YEARWEEK(CURDATE(), 1) {$whereClause}";    ) THEN us.device_id END) AS new_users

                    tension: 0.3,

                    fill: true    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

                }]

            },    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">$thisWeekStmt = $pdo->prepare($thisWeekQuery);FROM user_statistics us

            options: {

                responsive: true,    <link rel="stylesheet" href="assets/css/admin-style.css">

                scales: { y: { beginAtZero: true } }

            }    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>$thisWeekStmt->execute($params);WHERE (:packageName = '' OR packageName = :packageName)

        });

    </script></head>

</body>

</html><body>$thisWeek = $thisWeekStmt->fetch(PDO::FETCH_ASSOC);AND DATE(login_time) BETWEEN :startDate AND :endDate


    <!-- Navbar -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">GROUP BY DATE(login_time)

        <div class="container-fluid">

            <a class="navbar-brand" href="index.php">// Bu ayORDER BY DATE(login_time) DESC";

                <i class="bi bi-shield-check"></i> Admin Panel

            </a>$thisMonthQuery = "SELECT 

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>    COUNT(*) as total_logins,$dailyStmt = $pdo->prepare($dailyQuery);

            </button>

            <div class="collapse navbar-collapse" id="navbarNav">    COUNT(DISTINCT device_id) as unique_users$dailyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">FROM user_statistics $dailyStatistics = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

                        <a class="nav-link" href="index.php">

                            <i class="bi bi-speedometer2"></i> DashboardWHERE YEAR(login_time) = YEAR(CURDATE()) AND MONTH(login_time) = MONTH(CURDATE()) {$whereClause}";

                        </a>

                    </li>$thisMonthStmt = $pdo->prepare($thisMonthQuery);// Günlük önceki gün kıyaslama için dizi

                    <li class="nav-item">

                        <a class="nav-link" href="duyurular/">$thisMonthStmt->execute($params);$dailyPreviousStats = [];

                            <i class="bi bi-megaphone"></i> Duyurular

                        </a>$thisMonth = $thisMonthStmt->fetch(PDO::FETCH_ASSOC);foreach ($dailyStatistics as $key => $stat) {

                    </li>

                    <li class="nav-item">    if (isset($dailyStatistics[$key + 1])) {

                        <a class="nav-link active" href="istatistikler.php">

                            <i class="bi bi-graph-up"></i> İstatistikler// Toplam        $dailyPreviousStats[$stat['day']] = $dailyStatistics[$key + 1];

                        </a>

                    </li>$totalQuery = "SELECT     }

                </ul>

                <div class="d-flex align-items-center">    COUNT(*) as total_logins,}

                    <span class="text-white me-3">

                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username, ENT_QUOTES, 'UTF-8'); ?>    COUNT(DISTINCT device_id) as unique_users

                    </span>

                    <a href="logout.php" class="btn btn-outline-light btn-sm">FROM user_statistics {$whereClause}";// ** HAFTALIK İSTATİSTİKLER **

                        <i class="bi bi-box-arrow-right"></i> Çıkış

                    </a>$totalStmt = $pdo->prepare($totalQuery);$weeklyQuery = "SELECT

                </div>

            </div>$totalStmt->execute($params);    YEARWEEK(login_time, 3) AS week_no,

        </div>

    </nav>$total = $totalStmt->fetch(PDO::FETCH_ASSOC);    DATE_FORMAT(MIN(login_time), '%Y-%m-%d') AS start_date,



    <!-- Main Content -->    DATE_FORMAT(MAX(login_time), '%Y-%m-%d') AS end_date,

    <div class="container-fluid py-4">

        <div class="row mb-4">// Son 30 günlük günlük istatistikler (grafik için)    COUNT(*) AS total_logins,

            <div class="col-md-6">

                <h1 class="page-title">$dailyQuery = "SELECT     COUNT(DISTINCT device_id) AS unique_users,

                    <i class="bi bi-graph-up-arrow"></i> İstatistikler

                </h1>    DATE(login_time) as day,    COUNT(DISTINCT CASE WHEN NOT EXISTS (

            </div>

            <div class="col-md-6 text-end">    COUNT(*) as logins,        SELECT 1 FROM user_statistics us2

                <form method="GET" class="d-inline-block">

                    <select name="packageName" class="form-select form-select-sm" style="width: auto; display: inline-block;" onchange="this.form.submit()">    COUNT(DISTINCT device_id) as unique_users        WHERE us2.device_id = us.device_id

                        <option value="">Tüm Uygulamalar</option>

                        <?php foreach($packages as $pkg): ?>FROM user_statistics         AND us2.login_time < us.login_time

                            <option value="<?php echo htmlspecialchars($pkg, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedPackage === $pkg ? 'selected' : ''; ?>>

                                <?php echo htmlspecialchars($pkg, ENT_QUOTES, 'UTF-8'); ?>WHERE login_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) {$whereClause}    ) THEN us.device_id END) AS new_users

                            </option>

                        <?php endforeach; ?>GROUP BY DATE(login_time)FROM user_statistics us

                    </select>

                </form>ORDER BY day ASC";WHERE (:packageName = '' OR packageName = :packageName)

            </div>

        </div>$dailyStmt = $pdo->prepare($dailyQuery);AND DATE(login_time) BETWEEN :startDate AND :endDate



        <!-- İstatistik Kartları -->$dailyStmt->execute($params);GROUP BY YEARWEEK(login_time, 3)

        <div class="row mb-4">

            <!-- Bugün -->$dailyData = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);ORDER BY YEARWEEK(login_time, 3) DESC";

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="stat-card">

                    <div class="stat-card-icon bg-primary">

                        <i class="bi bi-calendar-day"></i>// Grafik verileri için JSON$weeklyStmt = $pdo->prepare($weeklyQuery);

                    </div>

                    <div class="stat-card-info">$chartLabels = [];$weeklyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);

                        <div class="stat-card-title">Bugün</div>

                        <div class="stat-card-value"><?php echo number_format($today['total_logins']); ?></div>$chartLogins = [];$weeklyStatistics = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

                        <div class="stat-card-change text-muted">

                            <small><?php echo number_format($today['unique_users']); ?> benzersiz kullanıcı</small>$chartUsers = [];

                        </div>

                    </div>foreach($dailyData as $row) {// Haftalık önceki hafta kıyaslama fonksiyonu

                </div>

            </div>    $chartLabels[] = date('d M', strtotime($row['day']));function getPreviousWeekStats(&$weeklyStatistics, $currentWeekNo) {



            <!-- Dün -->    $chartLogins[] = $row['logins'];    foreach ($weeklyStatistics as $stat) {

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="stat-card">    $chartUsers[] = $row['unique_users'];        if ($stat['week_no'] == $currentWeekNo) {

                    <div class="stat-card-icon bg-info">

                        <i class="bi bi-calendar-minus"></i>}            $currentIndex = array_search($stat, $weeklyStatistics);

                    </div>

                    <div class="stat-card-info">?>            if (isset($weeklyStatistics[$currentIndex + 1])) {

                        <div class="stat-card-title">Dün</div>

                        <div class="stat-card-value"><?php echo number_format($yesterday['total_logins']); ?></div><!DOCTYPE html>                return $weeklyStatistics[$currentIndex + 1];

                        <div class="stat-card-change text-muted">

                            <small><?php echo number_format($yesterday['unique_users']); ?> benzersiz kullanıcı</small><html lang="tr">            }

                        </div>

                    </div><head>            break;

                </div>

            </div>    <meta charset="UTF-8">        }



            <!-- Bu Hafta -->    <meta name="viewport" content="width=device-width, initial-scale=1.0">    }

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="stat-card">    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>    return null;

                    <div class="stat-card-icon bg-success">

                        <i class="bi bi-calendar-week"></i>    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">}

                    </div>

                    <div class="stat-card-info">    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

                        <div class="stat-card-title">Bu Hafta</div>

                        <div class="stat-card-value"><?php echo number_format($thisWeek['total_logins']); ?></div>    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

                        <div class="stat-card-change text-muted">

                            <small><?php echo number_format($thisWeek['unique_users']); ?> benzersiz kullanıcı</small>    <link rel="stylesheet" href="assets/css/admin-style.css">// ** AYLIK İSTATİSTİKLER **

                        </div>

                    </div>    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>$monthlyQuery = "SELECT

                </div>

            </div></head>    DATE_FORMAT(login_time, '%Y-%m') AS month_year,



            <!-- Bu Ay --><body>    DATE_FORMAT(MIN(login_time), '%Y-%m-%d') AS start_date,

            <div class="col-lg-3 col-md-6 mb-3">

                <div class="stat-card">    <!-- Navbar -->    DATE_FORMAT(MAX(login_time), '%Y-%m-%d') AS end_date,

                    <div class="stat-card-icon bg-warning">

                        <i class="bi bi-calendar-month"></i>    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">    COUNT(*) AS total_logins,

                    </div>

                    <div class="stat-card-info">        <div class="container-fluid">    COUNT(DISTINCT device_id) AS unique_users,

                        <div class="stat-card-title">Bu Ay</div>

                        <div class="stat-card-value"><?php echo number_format($thisMonth['total_logins']); ?></div>            <a class="navbar-brand" href="index.php">    COUNT(DISTINCT CASE WHEN NOT EXISTS (

                        <div class="stat-card-change text-muted">

                            <small><?php echo number_format($thisMonth['unique_users']); ?> benzersiz kullanıcı</small>                <i class="bi bi-shield-check"></i> Admin Panel        SELECT 1 FROM user_statistics us2

                        </div>

                    </div>            </a>        WHERE us2.device_id = us.device_id

                </div>

            </div>            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">        AND us2.login_time < us.login_time

        </div>

                <span class="navbar-toggler-icon"></span>    ) THEN us.device_id END) AS new_users

        <!-- Toplam İstatistikler -->

        <div class="row mb-4">            </button>FROM user_statistics us

            <div class="col-md-6 mb-3">

                <div class="card">            <div class="collapse navbar-collapse" id="navbarNav">WHERE (:packageName = '' OR packageName = :packageName)

                    <div class="card-body text-center">

                        <h3 class="text-primary"><?php echo number_format($total['total_logins']); ?></h3>                <ul class="navbar-nav me-auto">AND DATE(login_time) BETWEEN :startDate AND :endDate

                        <p class="text-muted mb-0">Toplam Giriş</p>

                    </div>                    <li class="nav-item">GROUP BY DATE_FORMAT(login_time, '%Y-%m')

                </div>

            </div>                        <a class="nav-link" href="index.php">ORDER BY DATE_FORMAT(login_time, '%Y-%m') DESC";

            <div class="col-md-6 mb-3">

                <div class="card">                            <i class="bi bi-speedometer2"></i> Dashboard

                    <div class="card-body text-center">

                        <h3 class="text-success"><?php echo number_format($total['unique_users']); ?></h3>                        </a>$monthlyStmt = $pdo->prepare($monthlyQuery);

                        <p class="text-muted mb-0">Toplam Benzersiz Kullanıcı</p>

                    </div>                    </li>$monthlyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);

                </div>

            </div>                    <li class="nav-item">$monthlyStatistics = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

        </div>

                        <a class="nav-link" href="duyurular/">

        <!-- Grafik -->

        <div class="row mb-4">                            <i class="bi bi-megaphone"></i> Duyurular// Aylık önceki ay kıyaslama fonksiyonu

            <div class="col-12">

                <div class="card">                        </a>function getPreviousMonthStats(&$monthlyStatistics, $currentMonthYear) {

                    <div class="card-header">

                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Son 30 Gün Aktivitesi</h5>                    </li>    foreach ($monthlyStatistics as $stat) {

                    </div>

                    <div class="card-body">                    <li class="nav-item">        if ($stat['month_year'] == $currentMonthYear) {

                        <canvas id="activityChart" height="80"></canvas>

                    </div>                        <a class="nav-link active" href="istatistikler.php">            $currentIndex = array_search($stat, $monthlyStatistics);

                </div>

            </div>                            <i class="bi bi-graph-up"></i> İstatistikler            if (isset($monthlyStatistics[$currentIndex + 1])) {

        </div>

                        </a>                return $monthlyStatistics[$currentIndex + 1];

        <!-- Günlük Tablo -->

        <div class="row">                    </li>            }

            <div class="col-12">

                <div class="card">                </ul>            break;

                    <div class="card-header">

                        <h5 class="mb-0"><i class="bi bi-table"></i> Günlük Detaylar (Son 30 Gün)</h5>                <div class="d-flex align-items-center">        }

                    </div>

                    <div class="card-body">                    <span class="text-white me-3">    }

                        <div class="table-responsive">

                            <table class="table table-hover">                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($admin_username); ?>    return null;

                                <thead>

                                    <tr>                    </span>}

                                        <th>Tarih</th>

                                        <th>Gün</th>                    <a href="logout.php" class="btn btn-outline-light btn-sm">

                                        <th>Toplam Giriş</th>

                                        <th>Benzersiz Kullanıcı</th>                        <i class="bi bi-box-arrow-right"></i> Çıkış

                                        <th>Ortalama Giriş/Kullanıcı</th>

                                    </tr>                    </a>?>

                                </thead>

                                <tbody>                </div><!DOCTYPE html>

                                    <?php if(count($dailyData) > 0): ?>

                                        <?php             </div><html lang="tr">

                                        $reversedData = array_reverse($dailyData);

                                        foreach($reversedData as $row):         </div><head>

                                            $avgPerUser = $row['unique_users'] > 0 ? round($row['logins'] / $row['unique_users'], 2) : 0;

                                            $dayNames = array('Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz');    </nav>    <meta charset="UTF-8">

                                            $dayNum = date('N', strtotime($row['day'])) - 1;

                                            $dayName = $dayNames[$dayNum];    <title>Admin Paneli - İstatistikler</title>

                                        ?>

                                        <tr>    <!-- Main Content -->    <meta name="viewport" content="width=device-width, initial-scale=1.0">

                                            <td><?php echo date('d.m.Y', strtotime($row['day'])); ?></td>

                                            <td><span class="badge bg-light text-dark"><?php echo $dayName; ?></span></td>    <div class="container-fluid py-4">    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

                                            <td><strong><?php echo number_format($row['logins']); ?></strong></td>

                                            <td><?php echo number_format($row['unique_users']); ?></td>        <div class="row mb-4">    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

                                            <td><?php echo number_format($avgPerUser, 2); ?></td>

                                        </tr>            <div class="col-md-6">    <style>

                                        <?php endforeach; ?>

                                    <?php else: ?>                <h1 class="page-title">        .positive { color: green; font-weight: bold; }

                                        <tr>

                                            <td colspan="5" class="text-center text-muted">Henüz veri yok</td>                    <i class="bi bi-graph-up-arrow"></i> İstatistikler        .negative { color: red; font-weight: bold; }

                                        </tr>

                                    <?php endif; ?>                </h1>    </style>

                                </tbody>

                            </table>            </div></head>

                        </div>

                    </div>            <div class="col-md-6 text-end"><body class="bg-light">

                </div>

            </div>                <form method="GET" class="d-inline-block">

        </div>

    </div>                    <select name="packageName" class="form-select form-select-sm" style="width: auto; display: inline-block;" onchange="this.form.submit()"><nav class="navbar navbar-expand-lg navbar-dark bg-dark">



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>                        <option value="">Tüm Uygulamalar</option>    <div class="container-fluid">

    <script>

        // Grafik                        <?php foreach($packages as $pkg): ?>        <a class="navbar-brand" href="../index.php">Admin Paneli</a>

        var ctx = document.getElementById('activityChart').getContext('2d');

        new Chart(ctx, {                            <option value="<?php echo htmlspecialchars($pkg); ?>" <?php echo $selectedPackage === $pkg ? 'selected' : ''; ?>>        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">

            type: 'line',

            data: {                                <?php echo htmlspecialchars($pkg); ?>            <span class="navbar-toggler-icon"></span>

                labels: <?php echo json_encode($chartLabels); ?>,

                datasets: [                            </option>        </button>

                    {

                        label: 'Toplam Giriş',                        <?php endforeach; ?>        <div class="collapse navbar-collapse" id="navbarNav">

                        data: <?php echo json_encode($chartLogins); ?>,

                        borderColor: 'rgb(102, 126, 234)',                    </select>            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                        backgroundColor: 'rgba(102, 126, 234, 0.1)',

                        tension: 0.3,                </form>                <li class="nav-item"><a class="nav-link" href="index.php">Talepler</a></li>

                        fill: true

                    },            </div>                <li class="nav-item"><a class="nav-link" href="duyurular/index.php">Duyurular</a></li>

                    {

                        label: 'Benzersiz Kullanıcı',        </div>                <li class="nav-item"><a class="nav-link active" href="../istatistikler.php">İstatistikler</a></li>

                        data: <?php echo json_encode($chartUsers); ?>,

                        borderColor: 'rgb(40, 167, 69)',            </ul>

                        backgroundColor: 'rgba(40, 167, 69, 0.1)',

                        tension: 0.3,        <!-- İstatistik Kartları -->            <div class="d-flex">

                        fill: true

                    }        <div class="row mb-4">                <a class="btn btn-outline-light" href="../logout.php">Çıkış Yap</a>

                ]

            },            <!-- Bugün -->            </div>

            options: {

                responsive: true,            <div class="col-lg-3 col-md-6 mb-3">        </div>

                maintainAspectRatio: true,

                plugins: {                <div class="stat-card">    </div>

                    legend: {

                        position: 'top'                    <div class="stat-card-icon bg-primary"></nav>

                    },

                    tooltip: {                        <i class="bi bi-calendar-day"></i>

                        mode: 'index',

                        intersect: false                    </div><div class="container mt-4">

                    }

                },                    <div class="stat-card-info">    <h5 class="text-center">Son 30 Günlük İstatistikler</h5>

                scales: {

                    y: {                        <div class="stat-card-title">Bugün</div>

                        beginAtZero: true,

                        ticks: {                        <div class="stat-card-value"><?php echo number_format($today['total_logins']); ?></div>    <form method="GET" class="mb-3">

                            precision: 0

                        }                        <div class="stat-card-change text-muted">        <label for="packageName" class="form-label">Uygulama Seç:</label>

                    }

                }                            <small><?php echo number_format($today['unique_users']); ?> benzersiz kullanıcı</small>        <select name="packageName" id="packageName" class="form-select" onchange="this.form.submit()">

            }

        });                        </div>            <option value="">Tümü</option>

    </script>

</body>                    </div>            <?php foreach ($packages as $package): ?>

</html>

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
