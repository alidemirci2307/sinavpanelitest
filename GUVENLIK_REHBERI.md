# Güvenlik Rehberi ve Yapılması Gerekenler

## ✅ Yapılan Güvenlik İyileştirmeleri

### 1. Veritabanı Güvenliği
- ✅ Hardcoded şifreleri config.php dosyasına taşındı
- ✅ PDO prepared statements kullanımı sağlandı
- ✅ SQL Injection koruması eklendi
- ✅ Veritabanı hata mesajları log dosyasına yönlendirildi

### 2. Session Güvenliği
- ✅ Session hijacking koruması eklendi
- ✅ IP ve User Agent kontrolü eklendi
- ✅ Session timeout mekanizması eklendi
- ✅ Session regeneration yapıldı
- ✅ Güvenli cookie ayarları (httpOnly, secure, sameSite)

### 3. CSRF Koruması
- ✅ Token tabanlı CSRF koruması eklendi
- ✅ Tüm formlara CSRF token eklendi
- ✅ POST isteklerinde token doğrulama yapıldı

### 4. XSS Koruması
- ✅ Tüm çıktılar htmlspecialchars ile escape edildi
- ✅ escapeHtml() fonksiyonu oluşturuldu
- ✅ Input sanitization eklendi

### 5. Rate Limiting
- ✅ Login denemelerinde rate limiting
- ✅ API endpoint'lerinde rate limiting
- ✅ Brute force koruması (sleep delay)

### 6. Input Validation
- ✅ Input temizleme fonksiyonları eklendi
- ✅ Uzunluk kontrolü yapıldı
- ✅ Whitelist validation eklendi
- ✅ Email ve URL validation güçlendirildi

### 7. Hata Yönetimi
- ✅ Production modunda hata mesajları gizlendi
- ✅ Hassas bilgi sızıntısı önlendi
- ✅ Error logging sistemi kuruldu

## ⚠️ Yapılması Gerekenler

### 1. KRİTİK - Hemen Yapılmalı

#### config.php Güvenliği
```bash
# Dosya izinlerini ayarlayın
chmod 600 config.php
```

**ÖNEMLİ:** config.php dosyasını mutlaka .gitignore'a ekleyin ve Git repository'den kaldırın:
```bash
git rm --cached config.php
git add .gitignore
git commit -m "Remove config.php from repository"
```

#### Veritabanı Şifresi
- 🔴 **Veritabanı şifresini değiştirin!** Şifre kodda görünür durumdaydı.
- Yeni şifre: En az 16 karakter, büyük/küçük harf, rakam ve özel karakter içermeli
- config.php dosyasını güncelleyin

#### HTTPS Kullanımı
- 🔴 **SSL sertifikası kurun** ve tüm trafiği HTTPS'e yönlendirin
- config.php'de SITE_URL'yi https:// olarak güncelleyin

### 2. YÜKSEK ÖNCELİKLİ

#### Güvenlik Headers
.htaccess veya Apache config'e ekleyin:
```apache
<IfModule mod_headers.c>
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net;"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

#### Admin Paneli IP Kısıtlaması
```apache
# Admin paneline sadece belirli IP'lerden erişim
<Directory "/path/to/admin">
    Order Deny,Allow
    Deny from all
    Allow from 192.168.1.100  # Kendi IP'niz
</Directory>
```

#### 2FA (İki Faktörlü Kimlik Doğrulama)
Admin girişine Google Authenticator veya benzer 2FA ekleyin.

### 3. ORTA ÖNCELİKLİ

#### Log Dosyası Oluşturma
```php
// security.php'ye ekleyin
function logSecurityEvent($event, $details = []) {
    $logFile = __DIR__ . '/logs/security.log';
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $event . ' - ' . json_encode($details) . PHP_EOL;
    error_log($logEntry, 3, $logFile);
}
```

logs/ klasörü oluşturun:
```bash
mkdir logs
chmod 755 logs
chmod 644 logs/security.log
```

#### E-posta Bildirimleri
- Başarısız login denemelerinde admin'e e-posta
- Yeni admin kullanıcısı eklendiğinde bildirim
- Şüpheli aktivitelerde uyarı

#### Veritabanı Yedekleme
Otomatik günlük yedekleme için cron job:
```bash
0 2 * * * mysqldump -u kullanici -p'sifre' veritabani > /yedek/db_$(date +\%Y\%m\%d).sql
```

### 4. DÜŞÜK ÖNCELİKLİ

#### Admin Aktivite Logu
Admin kullanıcılarının tüm işlemlerini kaydedin.

#### API Key Sistemi
Harici API erişimi için API key sistemi ekleyin.

#### Captcha
Login formuna reCAPTCHA ekleyin (çok fazla başarısız denemeden sonra).

## 📝 Kullanım Notları

### Yeni Dosyalar
3 yeni dosya oluşturuldu:
1. **config.php** - Tüm yapılandırma ayarları
2. **db.php** - Veritabanı bağlantı fonksiyonu
3. **security.php** - Güvenlik fonksiyonları

### Güncellenmiş Dosyalar
Aşağıdaki dosyalar güvenli hale getirildi:
- admin/login.php
- admin/index.php
- admin/logout.php
- admin/reply_feedback.php
- admin/duyurular/add.php
- api/feedback_submit.php
- api/get_announcements.php
- api/save_statistics.php
- api/list_feedbacks.php

### Diğer Dosyalar
Aşağıdaki dosyalar da aynı şekilde güncellenmeli:
- admin/get_conversation.php
- admin/update_feedback.php
- admin/istatistikler.php
- admin/duyurular/edit.php
- admin/duyurular/delete.php
- api/feedback_reply.php
- api/feedback_status.php
- api/get_statistics.php

## 🔒 Güvenlik Kontrol Listesi

- [ ] config.php dosyası .gitignore'a eklendi
- [ ] Veritabanı şifresi değiştirildi
- [ ] SSL sertifikası kuruldu
- [ ] HTTPS zorunlu yapıldı
- [ ] Güvenlik headers eklendi
- [ ] Log sistemi kuruldu
- [ ] Dosya izinleri ayarlandı (config.php 600)
- [ ] Admin paneline IP kısıtlaması eklendi
- [ ] Veritabanı yedekleme sistemi kuruldu
- [ ] 2FA eklendi (opsiyonel ama önerilen)
- [ ] Debug mode kapatıldı (config.php'de DEBUG_MODE = false)
- [ ] Tüm admin dosyaları güncellendi
- [ ] Tüm API dosyaları güncellendi

## 🚀 Deployment Öncesi

Production'a deploy etmeden önce:

1. config.php'de DEBUG_MODE = false yapın
2. Tüm şifreleri değiştirin
3. HTTPS aktif olduğundan emin olun
4. Log dosyalarını kontrol edin
5. Güvenlik testleri yapın
6. Yedekleme sistemini test edin

## 📚 Ek Kaynaklar

- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security Guide: https://www.php.net/manual/en/security.php
- PDO Tutorial: https://phpdelusions.net/pdo

## ⚡ Acil Durum

Bir güvenlik ihlali tespit ederseniz:
1. Tüm şifreleri hemen değiştirin
2. Şüpheli IP'leri engelleyin
3. Log dosyalarını inceleyin
4. Veritabanını yedekleyin
5. Kullanıcıları bilgilendirin
