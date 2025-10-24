<?php
/**
 * Admin Kullanıcısı Oluşturma Sayfası
 * GÜVENLİK: Admin oluşturduktan sonra bu dosyayı SİLİN!
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Zaten admin varsa bu sayfayı devre dışı bırak
$pdo = getDbConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
$adminCount = $stmt->fetchColumn();

if ($adminCount > 0 && !isset($_GET['force'])) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Zaten Var</title>
        <style>
            body { font-family: Arial; margin: 50px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; }
            .warning { color: #ffc107; background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; }
            a { color: #007bff; text-decoration: none; font-weight: bold; }
            a:hover { text-decoration: underline; }
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; border-radius: 5px; margin-top: 15px; }
            .btn:hover { background: #0056b3; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>⚠️ Admin Kullanıcısı Zaten Var</h1>
            <div class="warning">
                <strong>Güvenlik:</strong> Veritabanında zaten ' . $adminCount . ' admin kullanıcısı var.
                <br><br>
                Yeni admin oluşturmak istiyorsanız önce mevcut adminleri kontrol edin.
            </div>
            <p><a href="login.php" class="btn">Giriş Sayfasına Git →</a></p>
            <hr>
            <p><small>Yine de admin oluşturmak istiyorsanız: <a href="?force=1">Zorla Oluştur</a></small></p>
        </div>
    </body>
    </html>
    ');
}

$success = false;
$error = '';
$username = '';
$generatedPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? 'admin');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gerekli!';
    } elseif (strlen($password) < 6) {
        $error = 'Şifre en az 6 karakter olmalı!';
    } else {
        try {
            // Kullanıcı adı kontrolü
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Bu kullanıcı adı zaten kullanılıyor!';
            } else {
                // Admin kullanıcısı oluştur
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (:username, :password)");
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword
                ]);
                
                $success = true;
                $generatedPassword = $password;
            }
        } catch (PDOException $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kullanıcısı Oluştur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success h2 {
            margin-bottom: 15px;
            color: #155724;
        }
        
        .credentials {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 2px dashed #28a745;
            margin: 15px 0;
        }
        
        .credentials strong {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .credentials .value {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }
        
        .link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .link:hover {
            text-decoration: underline;
        }
        
        .info {
            background: #d1ecf1;
            border-left: 4px solid #0c5460;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .random-btn {
            background: #28a745;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .random-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="success">
                <h2>✅ Admin Kullanıcısı Oluşturuldu!</h2>
                <p>Giriş bilgilerinizi kaydedin:</p>
                
                <div class="credentials">
                    <strong>Kullanıcı Adı:</strong>
                    <div class="value"><?php echo htmlspecialchars($username); ?></div>
                </div>
                
                <div class="credentials">
                    <strong>Şifre:</strong>
                    <div class="value"><?php echo htmlspecialchars($generatedPassword); ?></div>
                </div>
                
                <div class="info">
                    ⚠️ <strong>ÖNEMLİ GÜVENLİK UYARISI:</strong><br>
                    Şimdi aşağıdaki adımları yapın:<br>
                    1. Yukarıdaki şifreyi güvenli bir yere kaydedin<br>
                    2. <code>create_admin.php</code> dosyasını SİLİN<br>
                    3. Giriş sayfasına gidin ve login olun
                </div>
                
                <a href="login.php" class="btn">Giriş Sayfasına Git →</a>
            </div>
        <?php else: ?>
            <h1>🔐 Admin Kullanıcısı Oluştur</h1>
            <p class="subtitle">Yeni bir admin hesabı oluşturun</p>
            
            <div class="warning">
                ⚠️ <strong>Güvenlik Uyarısı:</strong> Bu sayfa sadece ilk admin kullanıcısını oluşturmak içindir.
                Admin oluşturduktan sonra bu dosyayı mutlaka silin!
            </div>
            
            <?php if ($error): ?>
                <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Kullanıcı Adı:</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($username ?: 'admin'); ?>" 
                        required
                        autocomplete="off"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Şifre (en az 6 karakter):</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        autocomplete="new-password"
                    >
                    <button type="button" class="random-btn" onclick="generatePassword()">🎲 Rastgele Şifre Oluştur</button>
                </div>
                
                <button type="submit" class="btn">Admin Oluştur</button>
                <a href="login.php" class="btn btn-secondary">İptal</a>
            </form>
            
            <a href="test.php" class="link">Sistem Testi →</a>
        <?php endif; ?>
    </div>
    
    <script>
        function generatePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
            document.getElementById('password').type = 'text';
            
            // 3 saniye sonra gizle
            setTimeout(() => {
                document.getElementById('password').type = 'password';
            }, 3000);
        }
    </script>
</body>
</html>
