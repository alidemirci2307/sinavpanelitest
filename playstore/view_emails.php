<?php
// view_emails.php

// Giriş Kontrolü (Opsiyonel)
session_start();

// Basit bir giriş kontrolü ekleyerek bu sayfaya erişimi sınırlayabilirsiniz.
// Aşağıdaki örnek, sadece oturum açmış kullanıcıların erişebilmesini sağlar.
// Gerçek bir projede, daha güvenli bir kimlik doğrulama sistemi kullanmanız önerilir.

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php"); // Giriş yapma sayfasına yönlendir
    exit();
}

// emails.txt dosyasını oku
$file = 'emails.txt';
$emails = [];

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Sadece ilk iki virgülü dikkate alarak bölme yapıyoruz.
        $parts = explode(',', $line, 2);
        if (count($parts) == 2) {
            list($email, $date) = $parts;
            $emails[] = ['email' => $email, 'date' => $date];
        }
    }
} else {
    $error = "E-posta listesi bulunamadı.";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Toplanan E-postalar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Özel CSS -->
    <style>
        body {
            padding-top: 50px;
        }
        .container {
            max-width: 900px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-center">Toplanan E-postalar</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (empty($emails)): ?>
            <div class="alert alert-info" role="alert">
                Henüz kayıtlı e-posta adresi bulunmamaktadır.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>E-posta Adresi</th>
                            <th>Başvuru Tarihi (GMT+3)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emails as $index => $entry): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($entry['email']); ?></td>
                                <td><?php echo htmlspecialchars($entry['date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Geri Dön</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
