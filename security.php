<?php
// Güvenlik fonksiyonları

// CSRF token oluşturma
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// CSRF token doğrulama
function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// XSS koruması için HTML escape
function escapeHtml($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Güvenli session başlatma
function secureSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        
        session_start();
        
        // Session hijacking koruması
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > SESSION_LIFETIME) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // IP ve User Agent kontrolü
        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } else {
            if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || 
                $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
                session_destroy();
                session_start();
                return false;
            }
        }
    }
    return true;
}

// Rate limiting kontrolü
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = [];
    }
    
    // Eski denemeleri temizle
    $_SESSION['rate_limit'][$key] = array_filter(
        $_SESSION['rate_limit'][$key],
        function($timestamp) use ($timeWindow) {
            return ($timestamp + $timeWindow) > time();
        }
    );
    
    if (count($_SESSION['rate_limit'][$key]) >= $maxAttempts) {
        return false;
    }
    
    $_SESSION['rate_limit'][$key][] = time();
    return true;
}

// Input temizleme
function sanitizeInput($input) {
    return trim(strip_tags($input));
}

// JSON response gönderme
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
