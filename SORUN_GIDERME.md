# Admin Panel Sorun Giderme

## 🔍 Admin Paneline Giriş Yapamıyorum

### Olası Sebepler ve Çözümler

#### 1. Web Sunucusu Çalışmıyor ❌

**Kontrol:**
- XAMPP/WAMP/MAMP çalışıyor mu?
- Apache servisi başlatıldı mı?

**Çözüm:**
- XAMPP Control Panel'i açın
- Apache'yi başlatın
- MySQL'i başlatın

#### 2. Dosya Yolları Yanlış 📁

**Test:**
Tarayıcıda şu adresi açın:
```
http://localhost/sinavpaneli/admin/test.php
```

Bu sayfa size tüm dosyaların doğru yüklenip yüklenmediğini gösterecek.

#### 3. Veritabanı Bağlantısı Hatası 🗄️

**Kontrol:**
`config.php` dosyasındaki bilgiler doğru mu?
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'polisask_sinavpaneli');
define('DB_USER', 'polisask_sinavpaneli');
define('DB_PASS', 'Ankara2024++');
```

**Çözüm:**
- phpMyAdmin'de veritabanının var olduğunu kontrol edin
- Kullanıcı adı ve şifrenin doğru olduğunu kontrol edin

#### 4. Admin Kullanıcısı Yok 👤

**Kontrol:**
phpMyAdmin'de `admin_users` tablosunu açın. Kayıt var mı?

**Çözüm - Admin Kullanıcısı Oluştur:**

**Yöntem 1: SQL ile**
```sql
INSERT INTO admin_users (username, password) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere');
```

**Yöntem 2: PHP ile (password.php kullanarak)**

`admin/create_admin.php` dosyasını oluşturun:
```php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$pdo = getDbConnection();

$username = 'admin';
$password = 'Admin123!'; // İstediğiniz şifreyi yazın
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (:username, :password)");
    $stmt->execute([
        ':username' => $username,
        ':password' => $hashedPassword
    ]);
    echo "Admin kullanıcısı oluşturuldu!<br>";
    echo "Kullanıcı Adı: {$username}<br>";
    echo "Şifre: {$password}<br>";
    echo "<a href='login.php'>Giriş Yap</a>";
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>
```

Sonra tarayıcıda açın:
```
http://localhost/sinavpaneli/admin/create_admin.php
```

#### 5. Session Sorunu 🔒

**Belirtiler:**
- Login oluyor ama hemen logout oluyor
- Sürekli login sayfasına yönleniyor

**Çözüm:**
`security.php` dosyasındaki session kontrolünü geçici olarak devre dışı bırakın.

#### 6. Beyaz Sayfa / Boş Sayfa ⬜

**Sebep:** PHP hatası oluyor ama gösterilmiyor.

**Çözüm:**
`config.php`'de debug modunu açın:
```php
define('DEBUG_MODE', true);
```

#### 7. 404 Not Found 🚫

**Sebep:** Dosya yolu yanlış

**Kontrol:**
Tarayıcıda şu adresi deneyin:
```
http://localhost/sinavpaneli/admin/login.php
```

Eğer bu çalışmazsa:
```
http://localhost/admin/login.php
```

veya
```
http://localhost:8080/sinavpaneli/admin/login.php
```

#### 8. CSS/JS Yüklenmiyor 🎨

**Belirtiler:**
- Sayfa açılıyor ama çirkin görünüyor
- Stil yok

**Çözüm:**
`admin/test.php` dosyasını açın. CSS dosyasının yolunu kontrol edin.

## 📋 Adım Adım Çözüm

### 1. Test Sayfasını Çalıştırın
```
http://localhost/sinavpaneli/admin/test.php
```

Bu sayfa size şunları gösterecek:
- ✓ PHP versiyonu
- ✓ Dosyaların varlığı
- ✓ Veritabanı bağlantısı
- ✓ Admin kullanıcısı

### 2. Admin Kullanıcısı Oluşturun (Gerekirse)
```
http://localhost/sinavpaneli/admin/create_admin.php
```

### 3. Login Olun
```
http://localhost/sinavpaneli/admin/login.php
```

Kullanıcı Adı: `admin`
Şifre: (create_admin.php'de belirlediğiniz)

### 4. Başarılı! 🎉
Admin paneline giriş yaptınız.

## 🐛 Hata Mesajları

### "Veritabanı bağlantı hatası"
- MySQL çalışıyor mu?
- config.php'deki bilgiler doğru mu?
- Veritabanı oluşturuldu mu?

### "Kullanıcı adı veya şifre hatalı"
- Admin kullanıcısı var mı?
- Şifre doğru mu?

### "Güvenlik hatası" / "CSRF hatası"
- Cookie'leri temizleyin
- Sayfayı yenileyin

### "Session hatası"
- PHP session klasörü yazılabilir mi?
- `php.ini`'de session.save_path kontrol edin

## 🔧 Hızlı Düzeltmeler

### Debug Modunu Açma
`config.php`:
```php
define('DEBUG_MODE', true);
```

### Session Kontrolünü Geçici Bypass Etme
`admin/index.php` başına ekleyin:
```php
<?php
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'test';
?>
```

**NOT: Bu sadece test içindir, üretimde kullanmayın!**

## 📞 Hala Çalışmıyor?

1. `test.php` çıktısını kontrol edin
2. Tarayıcı konsolunu (F12) açın, hata var mı bakın
3. PHP error log'unu kontrol edin
4. Apache error log'unu kontrol edin

### Log Dosyaları
- XAMPP: `C:\xampp\apache\logs\error.log`
- WAMP: `C:\wamp64\logs\apache_error.log`
- PHP: `C:\xampp\php\logs\php_error_log.txt`

## ✅ Çözüldü mü?

Login sayfası açılıyorsa ve giriş yapabiliyorsanız, `test.php` ve `create_admin.php` dosyalarını silebilirsiniz:
```
admin/test.php
admin/create_admin.php (eğer oluşturduysanız)
```

## 🎯 Özet Kontrol Listesi

- [ ] XAMPP/WAMP çalışıyor
- [ ] Apache başlatıldı
- [ ] MySQL başlatıldı
- [ ] Veritabanı oluşturuldu
- [ ] config.php doğru yapılandırıldı
- [ ] Admin kullanıcısı var
- [ ] test.php çalışıyor
- [ ] login.php açılıyor
- [ ] Giriş yapılabiliyor
