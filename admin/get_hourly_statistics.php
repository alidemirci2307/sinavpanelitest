<?php
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}

$day = isset($_GET['day']) ? $_GET['day'] : null;
$packageName = isset($_GET['packageName']) ? $_GET['packageName'] : '';

if (!$day) {
    echo json_encode([]);
    exit;
}

$previousDay = date('Y-m-d', strtotime($day . ' -1 day'));

// Gün bazlı verileri getir
$query = "SELECT 
            HOUR(login_time) AS hour, 
            COUNT(*) AS total_logins, 
            COUNT(DISTINCT device_id) AS unique_users,
            SUM(CASE WHEN DATE(login_time) = DATE(first_login_time) AND DATE(first_login_time) = :day THEN 1 ELSE 0 END) AS new_users
          FROM user_statistics us
          WHERE DATE(login_time) = :day 
          AND (:packageName = '' OR packageName = :packageName) 
          GROUP BY hour 
          ORDER BY hour";
$stmt = $pdo->prepare($query);
$stmt->execute([':day' => $day, ':packageName' => $packageName]);
$hourlyStatistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prevQuery = "SELECT 
                HOUR(login_time) AS hour, 
                COUNT(*) AS total_logins, 
                COUNT(DISTINCT device_id) AS unique_users,
                SUM(CASE WHEN DATE(login_time) = DATE(first_login_time) AND DATE(first_login_time) = :previousDay THEN 1 ELSE 0 END) AS new_users
              FROM user_statistics us
              WHERE DATE(login_time) = :previousDay 
              AND (:packageName = '' OR packageName = :packageName) 
              GROUP BY hour 
              ORDER BY hour";
$prevStmt = $pdo->prepare($prevQuery);
$prevStmt->execute([':previousDay' => $previousDay, ':packageName' => $packageName]);
$previousHourlyStatistics = $prevStmt->fetchAll(PDO::FETCH_ASSOC);

$prevDataMap = [];
foreach ($previousHourlyStatistics as $stat) {
    $prevDataMap[$stat['hour']] = $stat;
}

$hourlyData = [];

foreach ($hourlyStatistics as $stat) {
    $hour = $stat['hour'];
    $prevData = $prevDataMap[$hour] ?? ['total_logins' => 0, 'unique_users' => 0, 'new_users' => 0];

    // Kümülatif verileri hesapla
    $cumulativeQuery = "SELECT 
                            COUNT(*) AS cumulative_logins,
                            COUNT(DISTINCT device_id) AS cumulative_unique_users,
                            SUM(CASE WHEN DATE(login_time) = DATE(first_login_time) AND DATE(first_login_time) = :day THEN 1 ELSE 0 END) AS cumulative_new_users
                        FROM user_statistics us
                        WHERE DATE(login_time) = :day 
                        AND (:packageName = '' OR packageName = :packageName) 
                        AND HOUR(login_time) <= :hour";
    $cumulativeStmt = $pdo->prepare($cumulativeQuery);
    $cumulativeStmt->execute([':day' => $day, ':hour' => $hour, ':packageName' => $packageName]);
    $cumulativeResult = $cumulativeStmt->fetch(PDO::FETCH_ASSOC);

    // Bir önceki günün kümülatif değerlerini hesapla
    $prevCumulativeQuery = "SELECT 
                                COUNT(*) AS cumulative_logins,
                                COUNT(DISTINCT device_id) AS cumulative_unique_users,
                                SUM(CASE WHEN DATE(login_time) = DATE(first_login_time) AND DATE(first_login_time) = :previousDay THEN 1 ELSE 0 END) AS cumulative_new_users
                            FROM user_statistics us
                            WHERE DATE(login_time) = :previousDay 
                            AND (:packageName = '' OR packageName = :packageName) 
                            AND HOUR(login_time) <= :hour";
    $prevCumulativeStmt = $pdo->prepare($prevCumulativeQuery);
    $prevCumulativeStmt->execute([':previousDay' => $previousDay, ':hour' => $hour, ':packageName' => $packageName]);
    $prevCumulativeResult = $prevCumulativeStmt->fetch(PDO::FETCH_ASSOC);

    // Farkları hesapla
    $hourlyData[] = [
        'hour' => $hour,
        'total_logins' => $stat['total_logins'],
        'diff_total_logins' => $stat['total_logins'] - $prevData['total_logins'],
        'unique_users' => $stat['unique_users'],
        'diff_unique_users' => $stat['unique_users'] - $prevData['unique_users'],
        'new_users' => $stat['new_users'],
        'diff_new_users' => $stat['new_users'] - $prevData['new_users'],
        'cumulative_logins' => $cumulativeResult['cumulative_logins'],
        'diff_cumulative_logins' => $cumulativeResult['cumulative_logins'] - $prevCumulativeResult['cumulative_logins'],
        'cumulative_unique_users' => $cumulativeResult['cumulative_unique_users'],
        'diff_cumulative_unique_users' => $cumulativeResult['cumulative_unique_users'] - $prevCumulativeResult['cumulative_unique_users'],
        'cumulative_new_users' => $cumulativeResult['cumulative_new_users'],
        'diff_cumulative_new_users' => $cumulativeResult['cumulative_new_users'] - $prevCumulativeResult['cumulative_new_users']
    ];
}

echo json_encode($hourlyData);
?>