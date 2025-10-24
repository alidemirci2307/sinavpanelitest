# Sınav Paneli - Güvenlik Güncellemesi

## ⚠️ ÖNEMLİ UYARILAR

**Bu proje güvenlik güncellemesi aldı!** Canlıya almadan önce mutlaka aşağıdaki adımları takip edin.

### 🔴 Kritik - Hemen Yapılması Gerekenler

1. **Veritabanı şifresini değiştirin**
2. **config.php dosyasını Git'ten kaldırın**
3. **Admin şifresini yenileyin**
4. **SSL sertifikası kurun**
5. **Debug modunu kapatın**

Detaylı bilgi için: `GUVENLIK_REHBERI.md` ve `DEGISIKLIKLER.md`

## 📁 Proje Yapısı

```
sinavpaneli/
├── admin/                  # Admin paneli
│   ├── duyurular/         # Duyuru yönetimi
│   ├── login.php          # ✅ Güncellendi
│   ├── index.php          # ✅ Güncellendi
│   ├── logout.php         # ✅ Güncellendi
│   └── ...
├── api/                    # API endpoint'leri
│   ├── feedback_submit.php # ✅ Güncellendi
│   ├── get_announcements.php # ✅ Güncellendi
│   └── ...                 # ✅ 7/7 API dosyası güncellendi
├── playstore/             # Play Store işlemleri
├── logs/                  # Log dosyaları (yeni)
├── config.php             # ⚠️ Merkezi yapılandırma (yeni)
├── db.php                 # ⚠️ Veritabanı bağlantısı (yeni)
├── security.php           # ⚠️ Güvenlik fonksiyonları (yeni)
├── .htaccess              # ⚠️ Apache güvenlik (yeni)
├── .gitignore             # ⚠️ Git güvenlik (yeni)
├── GUVENLIK_REHBERI.md    # 📖 Güvenlik kılavuzu
└── DEGISIKLIKLER.md       # 📝 Değişiklik listesi
```

## 🔧 Kurulum

### 1. Dosyaları Sunucuya Yükleyin

```bash
# Git kullanıyorsanız
git pull origin main

# FTP kullanıyorsanız
# Tüm dosyaları sunucuya yükleyin
```

### 2. Yapılandırma

**config.php** dosyasını düzenleyin:

```php
// Veritabanı bilgilerinizi girin
define('DB_HOST', 'localhost');
define('DB_NAME', 'veritabani_adi');
define('DB_USER', 'kullanici_adi');
define('DB_PASS', 'guclu_sifre_123!');

// Debug modunu kapatın (production)
define('DEBUG_MODE', false);

// Site URL'inizi girin (HTTPS ile)
define('SITE_URL', 'https://siteniz.com');
```

### 3. Dosya İzinleri (Linux/Mac)

```bash
chmod 600 config.php
chmod 644 .htaccess
chmod 755 logs/
chmod 644 logs/*.log
```

### 4. Veritabanı Şifresini Değiştirin

```sql
ALTER USER 'kullanici'@'localhost' IDENTIFIED BY 'YeniGüçlüŞifre!@#123';
FLUSH PRIVILEGES;
```

### 5. Admin Şifresini Yenileyin

```bash
# Yeni şifre hash'i oluştur
php -r "echo password_hash('YeniAdminSifrem123!', PASSWORD_BCRYPT);"
```

Çıktıyı alın ve veritabanında:

```sql
UPDATE admin_users SET password = 'hash_buraya' WHERE username = 'admin';
```

### 6. SSL Sertifikası Kurun

**Let's Encrypt (Ücretsiz):**
```bash
sudo certbot --apache -d siteniz.com -d www.siteniz.com
```

Sonra `.htaccess`'te HTTPS yönlendirmesini aktif edin.

## 🛡️ Güvenlik Özellikleri

### ✅ Uygulanan Korumalar

- **SQL Injection Koruması** - Prepared statements
- **XSS Koruması** - HTML escaping
- **CSRF Koruması** - Token tabanlı
- **Session Güvenliği** - Hijacking koruması
- **Rate Limiting** - Brute force önleme
- **Input Validation** - Veri doğrulama
- **Secure Headers** - XSS, Clickjacking koruması
- **Error Handling** - Hassas bilgi gizleme

### 📊 Güvenlik Seviyeleri

| Özellik | Öncesi | Sonrası |
|---------|--------|---------|
| SQL Injection | 🔴 Savunmasız | ✅ Korumalı |
| XSS | 🔴 Savunmasız | ✅ Korumalı |
| CSRF | 🔴 Yok | ✅ Var |
| Session | 🟡 Zayıf | ✅ Güçlü |
| Rate Limit | 🔴 Yok | ✅ Var |
| Şifre Hash | ✅ Var | ✅ Geliştirildi |

## 🧪 Test Etme

### Manuel Güvenlik Testleri

```bash
# 1. SQL Injection Testi
# Login formuna deneyin: ' OR '1'='1
# Sonuç: Giriş başarısız olmalı

# 2. XSS Testi
# Form alanına: <script>alert('XSS')</script>
# Sonuç: Script çalışmamalı

# 3. Rate Limiting Testi
# 6 kez yanlış şifre girin
# Sonuç: 5'ten sonra bloklanmalı
```

## 📝 API Kullanımı

### Örnek: Feedback Gönderme

```javascript
fetch('api/feedback_submit.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        subject: 'Test',
        message: 'Mesaj içeriği',
        device_id: 'abc123',
        app_package: 'com.example.app'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Örnek: Duyuruları Getirme

```javascript
fetch('api/get_announcements.php?app_package=com.example.app')
.then(response => response.json())
.then(data => console.log(data.data));
```

## 🚀 Production Checklist

Canlıya almadan önce:

- [ ] config.php güvenlik ayarları yapıldı
- [ ] Veritabanı şifresi değiştirildi
- [ ] Admin şifresi değiştirildi
- [ ] DEBUG_MODE = false yapıldı
- [ ] SSL sertifikası kuruldu
- [ ] HTTPS yönlendirmesi aktif
- [ ] .htaccess yüklendi
- [ ] Dosya izinleri ayarlandı
- [ ] Log klasörü oluşturuldu
- [ ] Güvenlik testleri yapıldı
- [ ] Yedekleme sistemi kuruldu
- [ ] Monitoring sistemi kuruldu

## 📖 Dokümantasyon

- **GUVENLIK_REHBERI.md** - Detaylı güvenlik kılavuzu
- **DEGISIKLIKLER.md** - Yapılan tüm değişiklikler
- **README.md** - Bu dosya

## 🆘 Sorun Giderme

### "Veritabanı bağlantı hatası"
- config.php'deki veritabanı bilgilerini kontrol edin
- MySQL servisinin çalıştığından emin olun

### "Session hatası"
- PHP session dizininin yazılabilir olduğunu kontrol edin
- session.save_path ayarını kontrol edin

### "500 Internal Server Error"
- logs/error.log dosyasını kontrol edin
- .htaccess dosyasını geçici olarak devre dışı bırakın
- PHP error_log'a bakın

## 📞 İletişim

Güvenlik sorunları için:
- Detaylı log kayıtlarını inceleyin
- GUVENLIK_REHBERI.md'yi okuyun
- OWASP kaynaklarına başvurun

## 📄 Lisans

Proje telif hakları ve lisans bilgileri...

## 🙏 Teşekkürler

Bu güvenlik güncellemesi OWASP Top 10 ve PHP Security Best Practices'e göre yapılmıştır.

---

**Son Güncelleme:** 24 Ekim 2025
**Versiyon:** 2.0 (Güvenlik Güncellemesi)
