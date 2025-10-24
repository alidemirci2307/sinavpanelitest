<?php
// En basit test - eğer bu çalışmazsa sunucu PHP'yi çalıştırmıyor
echo "PHP çalışıyor!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "<hr>";

// Config dosyası var mı?
$configPath = __DIR__ . '/../../config.php';
if(file_exists($configPath)) {
    echo "✓ config.php bulundu<br>";
    require_once $configPath;
    echo "✓ config.php yüklendi<br>";
} else {
    die("✗ config.php BULUNAMADI: " . $configPath);
}

// DB dosyası var mı?
$dbPath = __DIR__ . '/../../db.php';
if(file_exists($dbPath)) {
    echo "✓ db.php bulundu<br>";
    require_once $dbPath;
    echo "✓ db.php yüklendi<br>";
} else {
    die("✗ db.php BULUNAMADI: " . $dbPath);
}

// Security dosyası var mı?
$securityPath = __DIR__ . '/../../security.php';
if(file_exists($securityPath)) {
    echo "✓ security.php bulundu<br>";
    require_once $securityPath;
    echo "✓ security.php yüklendi<br>";
} else {
    die("✗ security.php BULUNAMADI: " . $securityPath);
}

echo "<hr>";

// Fonksiyonlar var mı?
if(function_exists('getDbConnection')) {
    echo "✓ getDbConnection() fonksiyonu mevcut<br>";
    try {
        $pdo = getDbConnection();
        echo "✓ Veritabanı bağlantısı başarılı<br>";
        
        // Duyurular tablosu var mı?
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM duyurular");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Duyurular tablosu erişilebilir (" . $result['total'] . " kayıt)<br>";
        
    } catch(Exception $e) {
        echo "✗ Veritabanı hatası: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ getDbConnection() fonksiyonu YOK<br>";
}

if(function_exists('secureSessionStart')) {
    echo "✓ secureSessionStart() fonksiyonu mevcut<br>";
} else {
    echo "✗ secureSessionStart() fonksiyonu YOK<br>";
}

if(function_exists('generateCSRFToken')) {
    echo "✓ generateCSRFToken() fonksiyonu mevcut<br>";
} else {
    echo "✗ generateCSRFToken() fonksiyonu YOK<br>";
}

echo "<hr>";
echo "<strong>Sonuç:</strong> Tüm kontroller başarılı ise index.php çalışmalı!<br>";
echo "<a href='index.php'>index.php'yi aç</a>";
?>
