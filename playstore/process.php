<?php
// process.php

// Hata raporlamasını etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zaman dilimini GMT+3 olarak ayarla
date_default_timezone_set('Europe/Istanbul'); // GMT+3

// Rate Limiting
session_start();

// Her IP için maksimum izin verilen istek sayısı
$maxRequests = 25;
// Zaman aralığı (saniye cinsinden)
$timeWindow = 3600; // 1 saat

$ip = $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

// İstek zamanlarını temizle
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($timestamp) use ($timeWindow) {
    return ($timestamp + $timeWindow) > time();
});

// Eğer maksimum istek sayısına ulaşıldıysa hata mesajı göster
if (count($_SESSION['requests']) >= $maxRequests) {
    header("Location: index.php?status=rate_limit");
    exit();
}

// Yeni isteği kaydet
$_SESSION['requests'][] = time();

// Formdan gelen e-posta adresini al ve temizle
if (isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $email = strtolower($email); // E-posta adresini küçük harfe çevirerek tutarlılığı sağla

    // E-posta geçerli mi kontrol et
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // E-posta adresini dosyaya ekle
        $file = 'emails.txt';

        // Dosyanın varlığını kontrol et, yoksa oluştur
        if (!file_exists($file)) {
            // Dosya oluştur ve yazma izinlerini ayarla
            $handle = fopen($file, 'w') or die('Dosya oluşturulamadı.');
            fclose($handle);
            chmod($file, 0666); // Gerekirse izinleri ayarlayın
        }

        // Mevcut e-posta adreslerini oku ve diziye ekle
        $existingEmails = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Sadece ilk iki virgülü dikkate alarak bölme yapıyoruz.
            $parts = explode(',', $line, 2);
            if (count($parts) >= 1) {
                $existingEmail = strtolower(trim($parts[0]));
                $existingEmails[] = $existingEmail;
            }
        }

        // E-posta zaten mevcut mu kontrol et
        if (in_array($email, $existingEmails)) {
            // E-posta zaten mevcut, yönlendir ve duplicate durumunu belirt
            header("Location: index.php?status=duplicate");
            exit();
        }

        // Şu anki tarihi al
        $date = date("Y-m-d H:i:s"); // GMT+3 zaman diliminde

        // E-posta adresi ve tarihi CSV formatında ekle
        $entry = $email . "," . $date . PHP_EOL;
        if (file_put_contents($file, $entry, FILE_APPEND | LOCK_EX) !== false) {
            // Başarı durumunda yönlendir
            header("Location: index.php?status=success");
            exit();
        } else {
            // Hata durumunda yönlendir
            header("Location: index.php?status=error");
            exit();
        }
    } else {
        // Geçersiz e-posta adresi
        header("Location: index.php?status=error");
        exit();
    }
} else {
    // E-posta adresi gönderilmemiş
    header("Location: index.php?status=error");
    exit();
}
?>
