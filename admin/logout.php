<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../security.php';

secureSessionStart();

// Cookie'leri temizle
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Session'ı temizle
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;