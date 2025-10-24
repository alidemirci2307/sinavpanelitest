<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Play Store Test Kullanıcısı Başvurusu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Özel CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Başlık Bölümü -->
    <header class="bg-primary text-white text-center py-5">
        <div class="container">
            <img src="img/traffic_sign.png" alt="Uygulama İkonu" class="img-fluid mb-3" style="max-width: 150px;">
            <h1 class="display-4">Ehliyet Sınav Soruları 2025</h1>
            <p class="lead">Yeni uygulamamızın test kullanıcıları arasında yer almak için hemen başvurun! Davetiye sınırlı olacaktır.</p>
        </div>
    </header>

    <!-- Başvuru Formu Bölümü -->
    <!-- Başvuru Formu Bölümü -->    <section class="py-5">        <div class="container">            <div class="row justify-content-center">                <div class="col-md-6">                    <div class="card shadow">                        <div class="card-body">                            <h3 class="card-title text-center mb-4">Başvurunuzu Yapın</h3>                            <form action="process.php" method="POST">                                <div class="mb-3">                                    <label for="email" class="form-label">E-posta Adresiniz</label>                                    <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>                                </div>                                <button type="submit" class="btn btn-primary w-100">Başvur</button>                            </form>                        </div>                    </div>                    <!-- Başarı/Hata Mesajları -->                    <?php if (isset($_GET['status'])): ?>                        <div class="mt-3">                            <?php if ($_GET['status'] == 'success'): ?>                                <div class="alert alert-success" role="alert">                                    Başvurunuz alındı! Mail kutunuzu kontrol ediniz. Uygulamamızı indirmek için gerekli olan link mail adresinize gönderilecektir. Teşekkür ederiz.                                </div>                            <?php elseif ($_GET['status'] == 'error'): ?>                                <div class="alert alert-danger" role="alert">                                    Bir hata oluştu. Lütfen tekrar deneyin.                                </div>                            <?php elseif ($_GET['status'] == 'rate_limit'): ?>                                <div class="alert alert-warning" role="alert">                                    Çok fazla istek yaptınız. Lütfen biraz sonra tekrar deneyin.                                </div>                            <?php elseif ($_GET['status'] == 'duplicate'): ?>                                <div class="alert alert-info" role="alert">                                    Bu e-posta adresi zaten kayıtlı.                                </div>                            <?php endif; ?>                        </div>                    <?php endif; ?>                </div>            </div>        </div>    </section>

    <!-- Footer Bölümü -->
    <footer class="bg-light text-center py-4">
        <div class="container">
            &copy; <?php echo date("Y"); ?> Play Store Test. Tüm hakları saklıdır.
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
