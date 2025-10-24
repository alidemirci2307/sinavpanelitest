<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Duyurular</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card {
            margin-bottom: 15px;
        }
        .high-priority {
            border: 2px solid #ffc107;
        }
    </style>
</head>
<body class="bg-light">
<?php
// Örneğin, app_package değerini kullanıcıdan GET parametresi ile alıyoruz
$app_package = isset($_GET['app_package']) ? trim($_GET['app_package']) : '';

if(empty($app_package)) {
    echo '<div class="container mt-4"><div class="alert alert-danger">app_package bilgisi eksik.</div></div>';
    exit;
}
?>
<div class="container mt-4">
    <h3>Duyurular</h3>
    <div id="duyurularList" class="mt-3">
        <!-- Duyurular burada gösterilecek -->
    </div>
</div>

<script>
    // app_package değerini PHP'den JavaScript'e aktar
    const appPackage = "<?= htmlspecialchars($app_package, ENT_QUOTES, 'UTF-8') ?>";

    fetch(`/api/list_duyurular.php?app_package=${encodeURIComponent(appPackage)}`)
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            const duyurular = data.data;
            const listDiv = document.getElementById('duyurularList');
            if(duyurular.length === 0) {
                listDiv.innerHTML = '<p>Henüz duyuru yok.</p>';
            } else {
                duyurular.forEach(duyuru => {
                    let duyuruDiv = document.createElement('div');
                    duyuruDiv.className = 'card';
                    if(duyuru.priority >= 10) { // Örneğin, öncelik 10 ve üzeri
                        duyuruDiv.classList.add('high-priority');
                    }
                    duyuruDiv.innerHTML = `
                        <div class="card-body">
                            <h5 class="card-title">${duyuru.title}</h5>
                            <p class="card-text">${duyuru.content.replace(/\n/g, '<br>')}</p>
                            ${duyuru.type === 'url' && duyuru.url ? `<a href="${duyuru.url}" target="_blank" class="btn btn-primary">İlgili Link</a>` : ''}
                        </div>
                    `;
                    listDiv.appendChild(duyuruDiv);
                });
            }
        } else {
            console.error('Duyurular çekilirken bir hata oluştu:', data.message);
        }
    })
    .catch(error => {
        console.error('Hata:', error);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
