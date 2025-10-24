<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$host = "localhost";
$db     = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı hatası: " . $e->getMessage());
}

// Seçili packageName'i al
$selectedPackage = $_GET['packageName'] ?? '';

// Mevcut packageName'leri al
$packageQuery = "SELECT DISTINCT packageName FROM user_statistics";
$packageStmt = $pdo->query($packageQuery);
$packages = $packageStmt->fetchAll(PDO::FETCH_COLUMN);

// Son 30 günü al
$endDate = new DateTime();
$startDate = (clone $endDate)->modify('-30 days');

// Tarih formatlama fonksiyonu
function formatTurkishDate($date, $format = '%d.%m.%Y %A') {
    setlocale(LC_TIME, 'tr_TR.UTF-8');
    $timestamp = strtotime($date);
    return strftime($format, $timestamp);
}

// Fark hesaplama fonksiyonu
function calculateDifference($current, $previous) {
    if ($previous == 0) return 0;
    return $current - $previous;
}

// ** GÜNLÜK İSTATİSTİKLER **
$dailyQuery = "SELECT
    DATE(login_time) AS day,
    COUNT(*) AS total_logins,
    COUNT(DISTINCT device_id) AS unique_users,
    COUNT(DISTINCT CASE WHEN NOT EXISTS (
        SELECT 1 FROM user_statistics us2
        WHERE us2.device_id = us.device_id
        AND us2.login_time < us.login_time
    ) THEN us.device_id END) AS new_users
FROM user_statistics us
WHERE (:packageName = '' OR packageName = :packageName)
AND DATE(login_time) BETWEEN :startDate AND :endDate
GROUP BY DATE(login_time)
ORDER BY DATE(login_time) DESC";

$dailyStmt = $pdo->prepare($dailyQuery);
$dailyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);
$dailyStatistics = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

// Günlük önceki gün kıyaslama için dizi
$dailyPreviousStats = [];
foreach ($dailyStatistics as $key => $stat) {
    if (isset($dailyStatistics[$key + 1])) {
        $dailyPreviousStats[$stat['day']] = $dailyStatistics[$key + 1];
    }
}

// ** HAFTALIK İSTATİSTİKLER **
$weeklyQuery = "SELECT
    YEARWEEK(login_time, 3) AS week_no,
    DATE_FORMAT(MIN(login_time), '%Y-%m-%d') AS start_date,
    DATE_FORMAT(MAX(login_time), '%Y-%m-%d') AS end_date,
    COUNT(*) AS total_logins,
    COUNT(DISTINCT device_id) AS unique_users,
    COUNT(DISTINCT CASE WHEN NOT EXISTS (
        SELECT 1 FROM user_statistics us2
        WHERE us2.device_id = us.device_id
        AND us2.login_time < us.login_time
    ) THEN us.device_id END) AS new_users
FROM user_statistics us
WHERE (:packageName = '' OR packageName = :packageName)
AND DATE(login_time) BETWEEN :startDate AND :endDate
GROUP BY YEARWEEK(login_time, 3)
ORDER BY YEARWEEK(login_time, 3) DESC";

$weeklyStmt = $pdo->prepare($weeklyQuery);
$weeklyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);
$weeklyStatistics = $weeklyStmt->fetchAll(PDO::FETCH_ASSOC);

// Haftalık önceki hafta kıyaslama fonksiyonu
function getPreviousWeekStats(&$weeklyStatistics, $currentWeekNo) {
    foreach ($weeklyStatistics as $stat) {
        if ($stat['week_no'] == $currentWeekNo) {
            $currentIndex = array_search($stat, $weeklyStatistics);
            if (isset($weeklyStatistics[$currentIndex + 1])) {
                return $weeklyStatistics[$currentIndex + 1];
            }
            break;
        }
    }
    return null;
}


// ** AYLIK İSTATİSTİKLER **
$monthlyQuery = "SELECT
    DATE_FORMAT(login_time, '%Y-%m') AS month_year,
    DATE_FORMAT(MIN(login_time), '%Y-%m-%d') AS start_date,
    DATE_FORMAT(MAX(login_time), '%Y-%m-%d') AS end_date,
    COUNT(*) AS total_logins,
    COUNT(DISTINCT device_id) AS unique_users,
    COUNT(DISTINCT CASE WHEN NOT EXISTS (
        SELECT 1 FROM user_statistics us2
        WHERE us2.device_id = us.device_id
        AND us2.login_time < us.login_time
    ) THEN us.device_id END) AS new_users
FROM user_statistics us
WHERE (:packageName = '' OR packageName = :packageName)
AND DATE(login_time) BETWEEN :startDate AND :endDate
GROUP BY DATE_FORMAT(login_time, '%Y-%m')
ORDER BY DATE_FORMAT(login_time, '%Y-%m') DESC";

$monthlyStmt = $pdo->prepare($monthlyQuery);
$monthlyStmt->execute(['packageName' => $selectedPackage, 'startDate' => $startDate->format('Y-m-d'), 'endDate' => $endDate->format('Y-m-d')]);
$monthlyStatistics = $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

// Aylık önceki ay kıyaslama fonksiyonu
function getPreviousMonthStats(&$monthlyStatistics, $currentMonthYear) {
    foreach ($monthlyStatistics as $stat) {
        if ($stat['month_year'] == $currentMonthYear) {
            $currentIndex = array_search($stat, $monthlyStatistics);
            if (isset($monthlyStatistics[$currentIndex + 1])) {
                return $monthlyStatistics[$currentIndex + 1];
            }
            break;
        }
    }
    return null;
}


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli - İstatistikler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .positive { color: green; font-weight: bold; }
        .negative { color: red; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">Admin Paneli</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Talepler</a></li>
                <li class="nav-item"><a class="nav-link" href="duyurular/index.php">Duyurular</a></li>
                <li class="nav-item"><a class="nav-link active" href="../istatistikler.php">İstatistikler</a></li>
            </ul>
            <div class="d-flex">
                <a class="btn btn-outline-light" href="../logout.php">Çıkış Yap</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h5 class="text-center">Son 30 Günlük İstatistikler</h5>

    <form method="GET" class="mb-3">
        <label for="packageName" class="form-label">Uygulama Seç:</label>
        <select name="packageName" id="packageName" class="form-select" onchange="this.form.submit()">
            <option value="">Tümü</option>
            <?php foreach ($packages as $package): ?>
                <option value="<?= htmlspecialchars($package) ?>" <?= $package == $selectedPackage ? 'selected' : '' ?>>
                    <?= htmlspecialchars($package) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button" role="tab" aria-controls="daily" aria-selected="true">Günlük</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab" aria-controls="weekly" aria-selected="false">Haftalık</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab" aria-controls="monthly" aria-selected="false">Aylık</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="daily" role="tabpanel" aria-labelledby="daily-tab">
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Tarih</th>
                            <th>Toplam Giriş</th>
                            <th>Eşsiz Kullanıcı</th>
                            <th>Yeni Kullanıcılar</th>
                            <th>Dün ile Kıyaslama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyStatistics as $stat): ?>
                        <tr>
                            <td><?= formatTurkishDate($stat['day']) ?></td>
                            <td><?= $stat['total_logins'] ?></td>
                            <td><?= $stat['unique_users'] ?></td>
                            <td><?= $stat['new_users'] ?></td>
                            <td>
                                <?php if (isset($dailyPreviousStats[$stat['day']])): ?>
                                    <?php
                                    $diffTotalLogins = calculateDifference($stat['total_logins'], $dailyPreviousStats[$stat['day']]['total_logins']);
                                    $diffUniqueUsers = calculateDifference($stat['unique_users'], $dailyPreviousStats[$stat['day']]['unique_users']);
                                    $diffNewUsers = calculateDifference($stat['new_users'], $dailyPreviousStats[$stat['day']]['new_users']);
                                    ?>
                                    Toplam Giriş: <span class="<?= $diffTotalLogins >= 0 ? 'positive' : 'negative' ?>"><?= $diffTotalLogins >= 0 ? '+' : '' ?><?= $diffTotalLogins ?></span><br>
                                    Eşsiz Kullanıcı: <span class="<?= $diffUniqueUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffUniqueUsers >= 0 ? '+' : '' ?><?= $diffUniqueUsers ?></span><br>
                                    Yeni Kullanıcılar: <span class="<?= $diffNewUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffNewUsers >= 0 ? '+' : '' ?><?= $diffNewUsers ?></span>
                                <?php else: ?>
                                    Veri Yok
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($dailyStatistics)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Henüz günlük istatistik yok.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="weekly" role="tabpanel" aria-labelledby="weekly-tab">
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Hafta</th>
                            <th>Toplam Giriş</th>
                            <th>Eşsiz Kullanıcı</th>
                            <th>Yeni Kullanıcılar</th>
                            <th>Önceki Hafta ile Kıyaslama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($weeklyStatistics as $stat): ?>
                        <tr>
                            <td><?= formatTurkishDate($stat['start_date'], '%d.%m.%Y') ?> - <?= formatTurkishDate($stat['end_date'], '%d.%m.%Y') ?></td>
                            <td><?= $stat['total_logins'] ?></td>
                            <td><?= $stat['unique_users'] ?></td>
                            <td><?= $stat['new_users'] ?></td>
                            <td>
                                <?php
                                    $previousWeekStat = getPreviousWeekStats($weeklyStatistics, $stat['week_no']);
                                    if ($previousWeekStat):
                                        $diffTotalLogins = calculateDifference($stat['total_logins'], $previousWeekStat['total_logins']);
                                        $diffUniqueUsers = calculateDifference($stat['unique_users'], $previousWeekStat['unique_users']);
                                        $diffNewUsers = calculateDifference($stat['new_users'], $previousWeekStat['new_users']);
                                    ?>
                                        Toplam Giriş: <span class="<?= $diffTotalLogins >= 0 ? 'positive' : 'negative' ?>"><?= $diffTotalLogins >= 0 ? '+' : '' ?><?= $diffTotalLogins ?></span><br>
                                        Eşsiz Kullanıcı: <span class="<?= $diffUniqueUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffUniqueUsers >= 0 ? '+' : '' ?><?= $diffUniqueUsers ?></span><br>
                                        Yeni Kullanıcılar: <span class="<?= $diffNewUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffNewUsers >= 0 ? '+' : '' ?><?= $diffNewUsers ?></span>
                                    <?php else: ?>
                                        Veri Yok
                                    <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($weeklyStatistics)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Henüz haftalık istatistik yok.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Ay</th>
                            <th>Toplam Giriş</th>
                            <th>Eşsiz Kullanıcı</th>
                            <th>Yeni Kullanıcılar</th>
                            <th>Önceki Ay ile Kıyaslama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthlyStatistics as $stat): ?>
                        <tr>
                            <td><?= formatTurkishDate($stat['start_date'], '%m.%Y') ?></td>
                            <td><?= $stat['total_logins'] ?></td>
                            <td><?= $stat['unique_users'] ?></td>
                            <td><?= $stat['new_users'] ?></td>
                            <td>
                                <?php
                                    $previousMonthStat = getPreviousMonthStats($monthlyStatistics, $stat['month_year']);
                                    if ($previousMonthStat):
                                        $diffTotalLogins = calculateDifference($stat['total_logins'], $previousMonthStat['total_logins']);
                                        $diffUniqueUsers = calculateDifference($stat['unique_users'], $previousMonthStat['unique_users']);
                                        $diffNewUsers = calculateDifference($stat['new_users'], $previousMonthStat['new_users']);
                                    ?>
                                        Toplam Giriş: <span class="<?= $diffTotalLogins >= 0 ? 'positive' : 'negative' ?>"><?= $diffTotalLogins >= 0 ? '+' : '' ?><?= $diffTotalLogins ?></span><br>
                                        Eşsiz Kullanıcı: <span class="<?= $diffUniqueUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffUniqueUsers >= 0 ? '+' : '' ?><?= $diffUniqueUsers ?></span><br>
                                        Yeni Kullanıcılar: <span class="<?= $diffNewUsers >= 0 ? 'positive' : 'negative' ?>"><?= $diffNewUsers >= 0 ? '+' : '' ?><?= $diffNewUsers ?></span>
                                    <?php else: ?>
                                        Veri Yok
                                    <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($monthlyStatistics)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Henüz aylık istatistik yok.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>