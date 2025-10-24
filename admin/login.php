<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

$pdo = getDbConnection();
$error = '';

// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF koruması
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrfToken)) {
        $error = "Güvenlik hatası. Lütfen tekrar deneyin.";
    } else {
        // Rate limiting
        if (!checkRateLimit('login_attempt', 5, 300)) {
            $error = "Çok fazla deneme. 5 dakika sonra tekrar deneyin.";
        } else {
            $username = sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            if (empty($username) || empty($password)) {
                $error = "Kullanıcı adı ve şifre gereklidir.";
            } else {
                $stmt = $pdo->prepare("SELECT id, username, password FROM admin_users WHERE username = :username LIMIT 1");
                $stmt->execute([':username' => $username]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData && password_verify($password, $userData['password'])) {
                    // Session regenerate
                    session_regenerate_id(true);
                    
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $userData['id'];
                    $_SESSION['admin_username'] = $userData['username'];

                    if ($remember) {
                        // Güvenli cookie ayarları
                        $cookieToken = bin2hex(random_bytes(32));
                        $_SESSION['cookie_token'] = $cookieToken;
                        
                        setcookie(
                            'admin_remember',
                            $cookieToken,
                            time() + COOKIE_LIFETIME,
                            '/',
                            '',
                            isset($_SERVER['HTTPS']),
                            true
                        );
                    }

                    header('Location: index.php');
                    exit;
                } else {
                    $error = "Kullanıcı adı veya şifre hatalı.";
                    sleep(1); // Brute force koruması
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Giriş</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Hafif gri bir arka plan */
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Hafif gölge */
        }
        .card-header {
            background-color: #007bff; /* Mavi ton başlık */
            color: white; /* Beyaz başlık yazısı */
        }
        .btn-primary {
            background-color: #007bff; /* Varsayılan Bootstrap buton rengi */
            border: none;
        }
        @media (max-width: 576px) {
            .card {
                margin: 0 15px; /* Küçük ekranlarda kenar boşlukları */
            }
            .card-header h4 {
                font-size: 18px; /* Başlık boyutunu küçült */
            }
            .btn-primary {
                font-size: 14px; /* Buton yazısını küçült */
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Admin Giriş</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= escapeHtml($error); ?></div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="hidden" name="csrf_token" value="<?= escapeHtml(generateCSRFToken()); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" name="username" id="username" class="form-control" required autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">168 Saat (1 Hafta) Beni Hatırla</label>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Giriş Yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
