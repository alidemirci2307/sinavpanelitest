<?php
// Veritabanı yapılandırma dosyası
// Bu dosyayı mutlaka .gitignore'a ekleyin!

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'polisask_sinavpaneli');
define('DB_USER', 'polisask_sinavpaneli');
define('DB_PASS', 'Ankara2024++');
define('DB_CHARSET', 'utf8mb4');

// Güvenlik ayarları
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 3600); // 1 saat
define('COOKIE_LIFETIME', 604800); // 7 gün (168 saat)

// Site ayarları
define('SITE_URL', 'http://localhost'); // Üretimde gerçek URL kullanın

// Hata raporlama (üretimde kapatın)
define('DEBUG_MODE', false);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Zaman dilimi ayarı
date_default_timezone_set('Europe/Istanbul');
?>
