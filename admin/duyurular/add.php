<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$host = "localhost";
$db   = "polisask_sinavpaneli";
$user = "polisask_sinavpaneli";
$pass = "Ankara2024++";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;
    $app_package = trim($_POST['app_package'] ?? '');

    // Basit doğrulama
    $validTypes = ['url', 'text', 'dialog', 'info', 'five_stars'];
    $validStatuses = ['active', 'inactive'];
    if(!in_array($type, $validTypes)) {
        $error = "Geçersiz duyuru tipi.";
    } elseif(!in_array($status, $validStatuses)) {
        $error = "Geçersiz duyuru durumu.";
    } elseif(empty($title) || empty($content)) {
        $error = "Başlık ve içerik boş olamaz.";
    } elseif($type === 'url' && empty($url)) {
        $error = "URL tipi için URL alanı zorunludur.";
    } elseif(empty($app_package)) {
        $error = "App Package alanı boş olamaz.";
    } elseif($priority < 0) {
        $error = "Öncelik negatif olamaz.";
    } else {
        // URL doğrulama (isteğe bağlı)
        if($type === 'url' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $error = "Geçerli bir URL giriniz.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO duyurular (type, title, content, url, status, priority, app_package) VALUES (:type, :title, :content, :url, :status, :priority, :app_package)");
                $stmt->execute([
                    ':type' => $type,
                    ':title' => $title,
                    ':content' => $content,
                    ':url' => $url ?: null,
                    ':status' => $status,
                    ':priority' => $priority,
                    ':app_package' => $app_package
                ]);
                $success = "Duyuru başarıyla eklendi.";
                // Formu temizle
                $type = $title = $content = $url = $app_package = '';
                $status = 'active';
                $priority = 0;
            } catch(PDOException $e) {
                $error = "Duyuru eklenirken bir hata oluştu: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Duyuru Ekle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function toggleUrlField() {
            var type = document.getElementById('type').value;
            var urlField = document.getElementById('urlField');
            urlField.style.display = (type === 'url') ? 'block' : 'none';
        }
        window.onload = function() {
            toggleUrlField();
        };
    </script>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="../index.php">Admin Paneli</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="../index.php">Talepler</a></li>
        <li class="nav-item"><a class="nav-link active" href="index.php">Duyurular</a></li>
      </ul>
      <div class="d-flex">
        <a class="btn btn-outline-light btn-sm" href="../logout.php">Çıkış Yap</a>
      </div>
    </div>
  </div>
</nav>

<!-- Form -->
<div class="container mt-4">
    <h3 class="text-center">Yeni Duyuru Ekle</h3>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="type" class="form-label">Duyuru Tipi</label>
            <select name="type" id="type" class="form-select" onchange="toggleUrlField()" required>
                <option value="">Seçiniz</option>
                <option value="url" <?= (isset($type) && $type === 'url') ? 'selected' : '' ?>>URL</option>
                <option value="text" <?= (isset($type) && $type === 'text') ? 'selected' : '' ?>>Metin</option>
                <option value="dialog" <?= (isset($type) && $type === 'dialog') ? 'selected' : '' ?>>Dialog</option>
                <option value="info" <?= (isset($type) && $type === 'info') ? 'selected' : '' ?>>Bilgi</option>
				<option value="five_stars" <?= (isset($type) && $type === 'five_stars') ? 'selected' : '' ?>>5 Yıldız</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Başlık</label>
            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">İçerik</label>
            <textarea name="content" id="content" class="form-control" rows="4" required><?= htmlspecialchars($content ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="mb-3" id="urlField" style="display: none;">
            <label for="url" class="form-label">URL</label>
            <input type="url" name="url" id="url" class="form-control" value="<?= htmlspecialchars($url ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
            <label for="app_package" class="form-label">App Package</label>
            <input type="text" name="app_package" id="app_package" class="form-control" value="<?= htmlspecialchars($app_package ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <small class="form-text text-muted">Duyurunun ait olduğu uygulama paketini belirtin (örneğin: com.example.app).</small>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Durum</label>
            <select name="status" id="status" class="form-select" required>
                <option value="active" <?= (isset($status) && $status === 'active') ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= (isset($status) && $status === 'inactive') ? 'selected' : '' ?>>Pasif</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="priority" class="form-label">Öncelik</label>
            <input type="number" name="priority" id="priority" class="form-control" value="<?= htmlspecialchars($priority ?? 0, ENT_QUOTES, 'UTF-8') ?>" min="0" required>
            <small class="form-text text-muted">Daha yüksek sayılar daha yüksek önceliği temsil eder.</small>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ekle</button>
        <a href="index.php" class="btn btn-secondary w-100 mt-2">İptal</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

