<?php
/**
 * Sunucu Dosya Kontrolü
 * Hangi dosyaların sunucuda olduğunu kontrol eder
 */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Kontrolü</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .file-check {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 6px;
            border-left: 4px solid #ddd;
        }
        .file-check.exists {
            border-left-color: #28a745;
        }
        .file-check.missing {
            border-left-color: #dc3545;
        }
        .file-name {
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        .status {
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status.ok {
            background: #d4edda;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
        .summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-card .label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 Sunucu Dosya Kontrolü</h1>
        
        <?php
        // Kontrol edilecek dosyalar
        $files_to_check = [
            'Ana Dizin' => [
                '../config.php',
                '../db.php',
                '../security.php',
                '../.htaccess'
            ],
            'Admin Dizini' => [
                'index.php',
                'login.php',
                'logout.php',
                'istatistikler.php',
                'test.php',
                'create_admin.php'
            ],
            'Admin/Includes' => [
                'includes/header.php',
                'includes/footer.php'
            ],
            'Admin/Assets' => [
                'assets/css/admin-style.css'
            ],
            'Admin/Duyurular' => [
                'duyurular/index.php',
                'duyurular/add.php',
                'duyurular/edit.php',
                'duyurular/delete.php'
            ],
            'API Dizini' => [
                '../api/feedback_submit.php',
                '../api/get_announcements.php',
                '../api/list_feedbacks.php'
            ]
        ];
        
        $total_files = 0;
        $existing_files = 0;
        $missing_files = 0;
        
        foreach ($files_to_check as $section => $files) {
            foreach ($files as $file) {
                $total_files++;
                if (file_exists($file)) {
                    $existing_files++;
                } else {
                    $missing_files++;
                }
            }
        }
        ?>
        
        <div class="summary">
            <div class="stat-card">
                <div class="number"><?php echo $total_files; ?></div>
                <div class="label">Toplam Dosya</div>
            </div>
            <div class="stat-card">
                <div class="number" style="color: #28a745;"><?php echo $existing_files; ?></div>
                <div class="label">Mevcut</div>
            </div>
            <div class="stat-card">
                <div class="number" style="color: #dc3545;"><?php echo $missing_files; ?></div>
                <div class="label">Eksik</div>
            </div>
        </div>
        
        <?php foreach ($files_to_check as $section => $files): ?>
            <div class="section">
                <h2><?php echo $section; ?></h2>
                <?php foreach ($files as $file): ?>
                    <?php 
                    $exists = file_exists($file);
                    $size = $exists ? filesize($file) : 0;
                    ?>
                    <div class="file-check <?php echo $exists ? 'exists' : 'missing'; ?>">
                        <span class="file-name"><?php echo htmlspecialchars($file); ?></span>
                        <span class="status <?php echo $exists ? 'ok' : 'error'; ?>">
                            <?php 
                            if ($exists) {
                                echo '✓ Var (' . number_format($size / 1024, 2) . ' KB)';
                            } else {
                                echo '✗ Yok';
                            }
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="info">
            <strong>🔍 Sunucu Bilgileri:</strong><br>
            📂 Çalışma Dizini: <code><?php echo htmlspecialchars(__DIR__); ?></code><br>
            🖥️ PHP Versiyonu: <code><?php echo PHP_VERSION; ?></code><br>
            🌐 Sunucu: <code><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor'); ?></code><br>
            📍 Domain: <code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Bilinmiyor'); ?></code>
        </div>
        
        <?php if ($missing_files > 0): ?>
            <div class="info" style="border-left-color: #dc3545; background: #f8d7da; color: #721c24;">
                <strong>⚠️ Eksik Dosyalar Bulundu!</strong><br>
                Yukarıda "✗ Yok" işaretli dosyaları sunucuya yüklemeniz gerekiyor.<br>
                <br>
                <strong>Yapılacaklar:</strong><br>
                1. VS Code'da eksik dosyaları bulun<br>
                2. FTP/cPanel File Manager ile sunucuya yükleyin<br>
                3. Bu sayfayı yeniden kontrol edin
            </div>
        <?php else: ?>
            <div class="info" style="border-left-color: #28a745; background: #d4edda; color: #155724;">
                <strong>✅ Tüm Dosyalar Mevcut!</strong><br>
                Artık admin paneline giriş yapabilirsiniz.<br>
                <br>
                <a href="login.php" style="color: #155724; font-weight: bold;">→ Giriş Sayfasına Git</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
