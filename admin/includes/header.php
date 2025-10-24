<?php
// Header dosyası - Tüm admin sayfalarında kullanılacak

// Session kontrolü
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/duyurular/') !== false ? '../' : '') . 'login.php');
    exit;
}

// Aktif sayfa tespiti
$current_page = basename($_SERVER['PHP_SELF']);
$is_duyurular = strpos($_SERVER['PHP_SELF'], '/duyurular/') !== false;
$base_path = $is_duyurular ? '../' : '';

// Admin kullanıcı bilgisi
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin Paneli</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/admin-style.css">
    
    <?php if(isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?= $base_path ?>index.php">Admin Panel</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'index.php' && !$is_duyurular) ? 'active' : '' ?>" 
                       href="<?= $base_path ?>index.php">
                        <i class="bi bi-inbox-fill me-2"></i>Talepler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $is_duyurular ? 'active' : '' ?>" 
                       href="<?= $base_path ?>duyurular/index.php">
                        <i class="bi bi-megaphone-fill me-2"></i>Duyurular
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'istatistikler.php' ? 'active' : '' ?>" 
                       href="<?= $base_path ?>istatistikler.php">
                        <i class="bi bi-bar-chart-fill me-2"></i>İstatistikler
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'playstore_ranking.php' ? 'active' : '' ?>" 
                       href="<?= $base_path ?>playstore_ranking.php">
                        <i class="bi bi-star-fill me-2"></i>Play Store
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="user-info d-none d-md-block"><?= escapeHtml($admin_username) ?></span>
                <a class="btn btn-outline-light btn-sm" href="<?= $base_path ?>logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>Çıkış Yap
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Ana İçerik Konteyneri -->
<div class="container-fluid px-4">
    <div class="content-wrapper">
