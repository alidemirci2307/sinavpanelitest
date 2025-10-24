<?php
/**
 * Duyurular Debug Sayfası
 * Bu sayfa hangi adımda hata olduğunu gösterir
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyurular Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .step { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ddd; }
        .step.success { border-left-color: #28a745; }
        .step.error { border-left-color: #dc3545; background: #f8d7da; }
        .step-title { font-weight: bold; margin-bottom: 10px; }
        .error-msg { color: #dc3545; padding: 10px; background: #fff; margin-top: 10px; border-radius: 4px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Duyurular Sayfası Debug</h1>
    
    <?php
    // 1. Dosya varlık kontrolü
    echo '<div class="step">';
    echo '<div class="step-title">1. Dosya Kontrolü</div>';
    
    $files = [
        'config.php' => __DIR__ . '/../../config.php',
        'db.php' => __DIR__ . '/../../db.php',
        'security.php' => __DIR__ . '/../../security.php'
    ];
    
    $all_exist = true;
    foreach ($files as $name => $path) {
        if (file_exists($path)) {
            echo "✓ <code>{$name}</code> bulundu (" . number_format(filesize($path)) . " bytes)<br>";
        } else {
            echo "✗ <code>{$name}</code> BULUNAMADI! Yol: <code>{$path}</code><br>";
            $all_exist = false;
        }
    }
    echo '</div>';
    
    if (!$all_exist) {
        echo '<div class="step error"><strong>HATA:</strong> Yukarıdaki eksik dosyaları sunucuya yükleyin!</div>';
        exit;
    }
    
    // 2. config.php yükleme
    echo '<div class="step">';
    echo '<div class="step-title">2. config.php Yükleniyor...</div>';
    try {
        require_once __DIR__ . '/../../config.php';
        echo "✓ config.php başarıyla yüklendi<br>";
        
        // Sabitleri kontrol et
        $constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($constants as $const) {
            if (defined($const)) {
                echo "✓ <code>{$const}</code> tanımlı<br>";
            } else {
                echo "✗ <code>{$const}</code> tanımlı DEĞİL!<br>";
            }
        }
    } catch (Exception $e) {
        echo '<div class="error-msg">✗ HATA: ' . $e->getMessage() . '</div>';
        echo '</div>';
        exit;
    }
    echo '</div>';
    
    // 3. db.php yükleme
    echo '<div class="step">';
    echo '<div class="step-title">3. db.php Yükleniyor...</div>';
    try {
        require_once __DIR__ . '/../../db.php';
        echo "✓ db.php başarıyla yüklendi<br>";
        
        if (function_exists('getDbConnection')) {
            echo "✓ <code>getDbConnection()</code> fonksiyonu mevcut<br>";
        } else {
            echo "✗ <code>getDbConnection()</code> fonksiyonu BULUNAMADI!<br>";
        }
    } catch (Exception $e) {
        echo '<div class="error-msg">✗ HATA: ' . $e->getMessage() . '</div>';
        echo '</div>';
        exit;
    }
    echo '</div>';
    
    // 4. security.php yükleme
    echo '<div class="step">';
    echo '<div class="step-title">4. security.php Yükleniyor...</div>';
    try {
        require_once __DIR__ . '/../../security.php';
        echo "✓ security.php başarıyla yüklendi<br>";
        
        $functions = ['secureSessionStart', 'generateCSRFToken', 'verifyCSRFToken', 'sanitizeInput'];
        foreach ($functions as $func) {
            if (function_exists($func)) {
                echo "✓ <code>{$func}()</code> mevcut<br>";
            } else {
                echo "✗ <code>{$func}()</code> BULUNAMADI!<br>";
            }
        }
    } catch (Exception $e) {
        echo '<div class="error-msg">✗ HATA: ' . $e->getMessage() . '</div>';
        echo '</div>';
        exit;
    }
    echo '</div>';
    
    // 5. Veritabanı bağlantısı
    echo '<div class="step">';
    echo '<div class="step-title">5. Veritabanı Bağlantısı Kuruluyor...</div>';
    try {
        $pdo = getDbConnection();
        echo "✓ Veritabanına bağlandı<br>";
        
        // Tablo kontrolü
        $stmt = $pdo->query("SHOW TABLES LIKE 'duyurular'");
        if ($stmt->rowCount() > 0) {
            echo "✓ <code>duyurular</code> tablosu mevcut<br>";
            
            // Kayıt sayısı
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM duyurular");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✓ Toplam {$count['total']} duyuru var<br>";
        } else {
            echo "✗ <code>duyurular</code> tablosu BULUNAMADI!<br>";
        }
    } catch (PDOException $e) {
        echo '<div class="error-msg">✗ VERİTABANI HATASI: ' . $e->getMessage() . '</div>';
        echo '</div>';
        exit;
    } catch (Exception $e) {
        echo '<div class="error-msg">✗ HATA: ' . $e->getMessage() . '</div>';
        echo '</div>';
        exit;
    }
    echo '</div>';
    
    // 6. Session
    echo '<div class="step">';
    echo '<div class="step-title">6. Session Başlatılıyor...</div>';
    try {
        secureSessionStart();
        echo "✓ Session başlatıldı<br>";
        echo "✓ Session ID: <code>" . session_id() . "</code><br>";
        
        if (isset($_SESSION['admin_logged_in'])) {
            echo "✓ Admin giriş yapmış<br>";
            echo "✓ Admin: <code>" . htmlspecialchars($_SESSION['admin_username'] ?? 'Bilinmiyor') . "</code><br>";
        } else {
            echo "⚠ Admin giriş yapmamış (login sayfasına yönlendirilecek)<br>";
        }
    } catch (Exception $e) {
        echo '<div class="error-msg">✗ HATA: ' . $e->getMessage() . '</div>';
        echo '</div>';
        exit;
    }
    echo '</div>';
    
    // Başarı
    echo '<div class="step success">';
    echo '<div class="step-title">✅ Tüm Kontroller Başarılı!</div>';
    echo '<p>Duyurular sayfası çalışmaya hazır.</p>';
    echo '<a href="index.php" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">Duyurular Sayfasına Git →</a>';
    echo '</div>';
    
    // Sunucu bilgileri
    echo '<div class="step">';
    echo '<div class="step-title">📊 Sunucu Bilgileri</div>';
    echo "PHP Versiyonu: <code>" . PHP_VERSION . "</code><br>";
    echo "Çalışma Dizini: <code>" . __DIR__ . "</code><br>";
    echo "Sunucu: <code>" . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor') . "</code><br>";
    echo '</div>';
    ?>
</body>
</html>
