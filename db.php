<?php
// Veritabanı bağlantı fonksiyonu
require_once __DIR__ . '/config.php';

function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Üretimde hassas bilgi vermeyin
            if (DEBUG_MODE) {
                die("Veritabanı bağlantı hatası: " . $e->getMessage());
            } else {
                error_log("Database connection error: " . $e->getMessage());
                die("Bir hata oluştu. Lütfen daha sonra tekrar deneyin.");
            }
        }
    }
    
    return $pdo;
}
?>
