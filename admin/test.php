<?php
// Test sayfası - Kurulumun doğru çalışıp çalışmadığını kontrol eder
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Panel Kurulum Testi</h1>";
echo "<hr>";

// 1. PHP Versiyonu
echo "<h2>✓ PHP Versiyonu</h2>";
echo "<p>PHP " . phpversion() . "</p>";

// 2. Dosya Varlığı
echo "<h2>Dosya Kontrolü</h2>";
$files = [
    'config.php' => file_exists(__DIR__ . '/../config.php'),
    'db.php' => file_exists(__DIR__ . '/../db.php'),
    'security.php' => file_exists(__DIR__ . '/../security.php'),
    'admin/includes/header.php' => file_exists(__DIR__ . '/includes/header.php'),
    'admin/includes/footer.php' => file_exists(__DIR__ . '/includes/footer.php'),
    'admin/assets/css/admin-style.css' => file_exists(__DIR__ . '/assets/css/admin-style.css'),
];

foreach ($files as $file => $exists) {
    echo "<p>" . ($exists ? "✓" : "✗") . " {$file}</p>";
}

// 3. Config.php'yi yükle
echo "<h2>Config.php Yükleme</h2>";
try {
    require_once __DIR__ . '/../config.php';
    echo "<p>✓ config.php yüklendi</p>";
    echo "<p>DB_HOST: " . DB_HOST . "</p>";
    echo "<p>DB_NAME: " . DB_NAME . "</p>";
    echo "<p>DB_USER: " . DB_USER . "</p>";
    echo "<p>DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "</p>";
} catch (Exception $e) {
    echo "<p>✗ Hata: " . $e->getMessage() . "</p>";
}

// 4. DB.php'yi yükle ve bağlantıyı test et
echo "<h2>Veritabanı Bağlantısı</h2>";
try {
    require_once __DIR__ . '/../db.php';
    echo "<p>✓ db.php yüklendi</p>";
    
    $pdo = getDbConnection();
    echo "<p>✓ Veritabanı bağlantısı başarılı</p>";
    
    // Test sorgusu
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM feedbacks");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✓ Feedbacks tablosunda {$result['count']} kayıt var</p>";
    
} catch (Exception $e) {
    echo "<p>✗ Hata: " . $e->getMessage() . "</p>";
}

// 5. Security.php'yi yükle
echo "<h2>Security.php Yükleme</h2>";
try {
    require_once __DIR__ . '/../security.php';
    echo "<p>✓ security.php yüklendi</p>";
    
    // Session başlat
    secureSessionStart();
    echo "<p>✓ Session başlatıldı</p>";
    
    // CSRF token oluştur
    $token = generateCSRFToken();
    echo "<p>✓ CSRF Token: " . substr($token, 0, 20) . "...</p>";
    
    // escapeHtml test
    $test = escapeHtml("<script>alert('test')</script>");
    echo "<p>✓ escapeHtml fonksiyonu çalışıyor</p>";
    
} catch (Exception $e) {
    echo "<p>✗ Hata: " . $e->getMessage() . "</p>";
}

// 6. Admin kullanıcısı kontrolü
echo "<h2>Admin Kullanıcısı</h2>";
try {
    $stmt = $pdo->query("SELECT username FROM admin_users LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        echo "<p>✓ Admin kullanıcısı mevcut: {$admin['username']}</p>";
    } else {
        echo "<p>✗ Admin kullanıcısı bulunamadı! Lütfen bir admin kullanıcısı oluşturun.</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Hata: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Sonuç</h2>";
echo "<p><a href='login.php'>Login Sayfasına Git</a></p>";
echo "<p><a href='index.php'>Ana Sayfaya Git (Login gerektirir)</a></p>";
?>
