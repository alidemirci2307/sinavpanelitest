<?php
/**
 * Dosya Karşılaştırma
 * Sunucudaki dosyaların yerel dosyalarla aynı olup olmadığını kontrol eder
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Dosya Kontrol</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .file { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .match { border-left: 4px solid #28a745; }
        .mismatch { border-left: 4px solid #dc3545; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>📁 Dosya Karşılaştırma</h1>
    
    <?php
    $files = [
        'config.php' => __DIR__ . '/../../config.php',
        'db.php' => __DIR__ . '/../../db.php',
        'security.php' => __DIR__ . '/../../security.php'
    ];
    
    foreach ($files as $name => $path) {
        echo '<div class="file">';
        echo "<h3>{$name}</h3>";
        
        if (file_exists($path)) {
            $size = filesize($path);
            $content = file_get_contents($path);
            $lines = substr_count($content, "\n") + 1;
            
            echo "✓ Dosya var<br>";
            echo "📊 Boyut: <code>" . number_format($size) . "</code> bytes<br>";
            echo "📄 Satır sayısı: <code>{$lines}</code><br>";
            
            // escapeHtml fonksiyonu kontrolü (security.php için)
            if ($name === 'security.php') {
                if (strpos($content, 'function escapeHtml(') !== false) {
                    echo "✓ <code>escapeHtml()</code> fonksiyonu MEVCUT<br>";
                } else {
                    echo "✗ <code>escapeHtml()</code> fonksiyonu YOK!<br>";
                    echo '<div style="background: #fff3cd; padding: 10px; margin-top: 10px; border-radius: 4px;">';
                    echo '<strong>ÇÖZÜM:</strong> security.php dosyasını yeniden yükleyin!';
                    echo '</div>';
                }
            }
            
            // İlk 500 karakteri göster
            echo "<details style='margin-top: 10px;'>";
            echo "<summary>İlk 500 karakteri göster</summary>";
            echo "<pre style='background: #f8f9fa; padding: 10px; overflow-x: auto; font-size: 11px;'>";
            echo htmlspecialchars(substr($content, 0, 500));
            echo "</pre>";
            echo "</details>";
            
        } else {
            echo "✗ Dosya BULUNAMADI!<br>";
            echo "Beklenen yol: <code>{$path}</code>";
        }
        
        echo '</div>';
    }
    ?>
    
    <div class="file" style="border-left: 4px solid #007bff;">
        <h3>🔧 Önerilen Boyutlar (Referans)</h3>
        <p>config.php: ~900-950 bytes</p>
        <p>db.php: ~1,050-1,100 bytes</p>
        <p>security.php: ~3,000-3,150 bytes (escapeHtml fonksiyonu dahil)</p>
        <br>
        <p>Eğer boyutlar çok farklıysa dosyaları yeniden yükleyin!</p>
    </div>
    
    <div class="file" style="border-left: 4px solid #28a745;">
        <h3>✅ Sonraki Adımlar</h3>
        <p>1. Yukarıdaki dosyaları kontrol edin</p>
        <p>2. Eksik veya farklı dosyaları yeniden yükleyin</p>
        <p>3. <a href="debug.php">Debug sayfasını</a> tekrar çalıştırın</p>
        <p>4. <a href="index.php">Duyurular sayfasını</a> açın</p>
    </div>
</body>
</html>
