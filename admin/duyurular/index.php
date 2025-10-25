<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../security.php';

secureSessionStart();

$pdo = getDbConnection();
$page_title = "Duyurular";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duyuru_ids']) && isset($_POST['csrf_token'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Guvenlik hatasi!";
    } else {
        $duyuru_ids = array_map('intval', $_POST['duyuru_ids']);
        $action = $_POST['action'];
        $status = ($action === 'activate') ? 'active' : (($action === 'deactivate') ? 'inactive' : null);

        if($status && !empty($duyuru_ids)) {
            try {
                $placeholders = str_repeat('?,', count($duyuru_ids) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE duyurular SET status = ? WHERE id IN (" . $placeholders . ")");
                $params = array_merge(array($status), $duyuru_ids);
                $stmt->execute($params);
                header('Location: index.php?success=1');
                exit;
            } catch(PDOException $e) {
                $error = "Hata: " . $e->getMessage();
            }
        }
    }
}

$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterPackage = isset($_GET['package']) ? $_GET['package'] : '';
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$whereConditions = array();
$params = array();

if($filterType) {
    $whereConditions[] = "type = ?";
    $params[] = $filterType;
}
if($filterStatus) {
    $whereConditions[] = "status = ?";
    $params[] = $filterStatus;
}
if($filterPackage) {
    $whereConditions[] = "app_package = ?";
    $params[] = $filterPackage;
}
if($filterDateFrom) {
    $whereConditions[] = "DATE(created_at) >= ?";
    $params[] = $filterDateFrom;
}
if($filterDateTo) {
    $whereConditions[] = "DATE(created_at) <= ?";
    $params[] = $filterDateTo;
}

$whereSQL = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$stmt_stats = $pdo->query("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive FROM duyurular");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$query = "SELECT * FROM duyurular " . $whereSQL . " ORDER BY priority DESC, created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$duyurular = $stmt->fetchAll(PDO::FETCH_ASSOC);

$packagesStmt = $pdo->query("SELECT DISTINCT app_package FROM duyurular WHERE app_package IS NOT NULL ORDER BY app_package");
$packages = $packagesStmt->fetchAll(PDO::FETCH_COLUMN);

$typesStmt = $pdo->query("SELECT DISTINCT type FROM duyurular ORDER BY type");
$types = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<h1 class="mb-4">Duyurular</h1>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>Basarili!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-card-title"><i class="bi bi-megaphone-fill me-2"></i>Toplam</div>
            <div class="stat-card-value"><?php echo $stats['total']; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="border-left-color: var(--success-color);">
            <div class="stat-card-title"><i class="bi bi-check-circle-fill me-2"></i>Aktif</div>
            <div class="stat-card-value text-success"><?php echo $stats['active']; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="border-left-color: var(--secondary-color);">
            <div class="stat-card-title"><i class="bi bi-pause-circle-fill me-2"></i>Pasif</div>
            <div class="stat-card-value"><?php echo $stats['inactive']; ?></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5><i class="bi bi-funnel"></i> Filtrele</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Tur</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tumu</option>
                    <?php foreach($types as $t): ?>
                    <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $filterType === $t ? 'selected' : ''; ?>><?php echo htmlspecialchars($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tumu</option>
                    <option value="active" <?php echo $filterStatus === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="inactive" <?php echo $filterStatus === 'inactive' ? 'selected' : ''; ?>>Pasif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Uygulama</label>
                <select name="package" class="form-select form-select-sm">
                    <option value="">Tumu</option>
                    <?php foreach($packages as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $filterPackage === $p ? 'selected' : ''; ?>><?php echo htmlspecialchars($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Baslangic</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterDateFrom); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Bitis</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterDateTo); ?>">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <?php if($filterType || $filterStatus || $filterPackage || $filterDateFrom || $filterDateTo): ?>
        <div class="mt-3">
            <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Filtreyi Temizle</a>
            <span class="text-muted ms-2"><?php echo count($duyurular); ?> sonuc bulundu</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="table-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Tum Duyurular</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDuyuruModal">
            <i class="bi bi-plus-lg"></i> Yeni Duyuru
        </button>
    </div>
    
    <?php if(count($duyurular) > 0): ?>
    <form method="POST" id="bulkForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
        <input type="hidden" name="action" id="bulkAction">
        <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdate('activate')">
                <i class="bi bi-check-circle"></i> Aktif Et
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="bulkUpdate('deactivate')">
                <i class="bi bi-pause-circle"></i> Pasif Et
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                        <th>ID</th>
                        <th>Tur</th>
                        <th>Baslik</th>
                        <th>Uygulama</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>Islem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($duyurular as $d): ?>
                    <tr>
                        <td><input type="checkbox" name="duyuru_ids[]" value="<?php echo $d['id']; ?>" class="row-checkbox"></td>
                        <td><span class="badge bg-secondary">#<?php echo $d['id']; ?></span></td>
                        <td><?php echo htmlspecialchars($d['type']); ?></td>
                        <td><strong><?php echo htmlspecialchars($d['title']); ?></strong></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars($d['app_package']); ?></small></td>
                        <td>
                            <?php if($d['status'] === 'active'): ?>
                                <span class="badge badge-success"><i class="bi bi-check-circle"></i> Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><i class="bi bi-pause-circle"></i> Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d.m.Y', strtotime($d['created_at'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="openEditModal(<?php echo $d['id']; ?>)" 
                                    data-bs-toggle="tooltip" title="Duzenle">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istiyor musunuz?')" data-bs-toggle="tooltip" title="Sil">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon"></div>
        <div class="empty-state-title">Henuz duyuru yok</div>
        <div class="empty-state-text">Ilk duyuruyu ekleyerek baslayabilirsiniz.</div>
        <a href="add.php" class="btn btn-primary mt-3"><i class="bi bi-plus-lg"></i> Ilk Duyuruyu Ekle</a>
    </div>
    <?php endif; ?>
</div>

<!-- Yeni Duyuru Modal -->
<div class="modal fade" id="addDuyuruModal" tabindex="-1" aria-labelledby="addDuyuruModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDuyuruModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Yeni Duyuru Ekle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addDuyuruForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Başlık <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">İçerik <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tür <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Seçiniz</option>
                                <option value="text">Metin</option>
                                <option value="url">URL</option>
                                <option value="dialog">Dialog</option>
                                <option value="info">Bilgi</option>
                                <option value="five_stars">5 Yıldız</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Öncelik <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="priority" name="priority" value="5" min="1" max="100" required>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="urlField" style="display: none;">
                        <label class="form-label fw-bold">URL</label>
                        <input type="url" class="form-control" id="url" name="url" placeholder="https://...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Uygulama Paketleri <span class="text-danger">*</span></label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="packageMode" id="packageModeAll" value="all" checked>
                            <label class="form-check-label" for="packageModeAll">
                                Tüm Uygulamalar
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="packageMode" id="packageModeManual" value="manual">
                            <label class="form-check-label" for="packageModeManual">
                                Manuel Giriş
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="packageMode" id="packageModeSelect" value="select">
                            <label class="form-check-label" for="packageModeSelect">
                                Listeden Seç
                            </label>
                        </div>
                        
                        <div id="manualPackageInput" style="display: none;">
                            <input type="text" class="form-control mb-2" id="manualPackage" placeholder="com.example.app">
                            <small class="text-muted">Örnek: com.example.myapp</small>
                        </div>
                        
                        <div id="selectPackageList" style="display: none;">
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach($packages as $p): ?>
                                <div class="form-check">
                                    <input class="form-check-input package-checkbox" type="checkbox" value="<?php echo htmlspecialchars($p); ?>" id="pkg_<?php echo md5($p); ?>">
                                    <label class="form-check-label" for="pkg_<?php echo md5($p); ?>">
                                        <?php echo htmlspecialchars($p); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Durum</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                            <label class="form-check-label" for="status">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> İptal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Düzenle Modal -->
<div class="modal fade" id="editDuyuruModal" tabindex="-1" aria-labelledby="editDuyuruModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDuyuruModalLabel">
                    <i class="bi bi-pencil me-2"></i>Duyuru Düzenle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDuyuruForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Başlık <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">İçerik <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tür <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_type" name="type" required>
                                <option value="">Seçiniz</option>
                                <option value="text">Metin</option>
                                <option value="url">URL</option>
                                <option value="dialog">Dialog</option>
                                <option value="info">Bilgi</option>
                                <option value="five_stars">5 Yıldız</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Öncelik <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_priority" name="priority" value="5" min="1" max="100" required>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="edit_urlField" style="display: none;">
                        <label class="form-label fw-bold">URL</label>
                        <input type="url" class="form-control" id="edit_url" name="url" placeholder="https://...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Uygulama Paketi</label>
                        <input type="text" class="form-control" id="edit_app_package" name="app_package" readonly>
                        <small class="text-muted">Paket değiştirilemez. Yeni duyuru oluşturun.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Durum</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_status" name="status">
                            <label class="form-check-label" for="edit_status">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> İptal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extra_js = <<<'EOD'
<script>
function toggleAll(cb){
    var boxes = document.querySelectorAll('.row-checkbox');
    for(var i = 0; i < boxes.length; i++) {
        boxes[i].checked = cb.checked;
    }
}

function bulkUpdate(action){
    var checked = document.querySelectorAll('.row-checkbox:checked');
    if(checked.length === 0){
        alert('Lutfen secim yapin!');
        return;
    }
    if(confirm('Islem yapilsin mi?')){
        document.getElementById('bulkAction').value = action;
        document.getElementById('bulkForm').submit();
    }
}

// Modal içindeki paket modu değişimi
document.querySelectorAll('input[name="packageMode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('manualPackageInput').style.display = 'none';
        document.getElementById('selectPackageList').style.display = 'none';
        
        if (this.value === 'manual') {
            document.getElementById('manualPackageInput').style.display = 'block';
        } else if (this.value === 'select') {
            document.getElementById('selectPackageList').style.display = 'block';
        }
    });
});

// Tür değişimi
document.getElementById('type').addEventListener('change', function() {
    if (this.value === 'url') {
        document.getElementById('urlField').style.display = 'block';
        document.getElementById('url').required = true;
    } else {
        document.getElementById('urlField').style.display = 'none';
        document.getElementById('url').required = false;
    }
});

// Form gönderimi
document.getElementById('addDuyuruForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        title: document.getElementById('title').value,
        content: document.getElementById('content').value,
        type: document.getElementById('type').value,
        priority: document.getElementById('priority').value,
        url: document.getElementById('url').value,
        status: document.getElementById('status').checked ? 'active' : 'inactive',
        csrf_token: '<?php echo htmlspecialchars(generateCSRFToken()); ?>'
    };
    
    // Paket seçimini belirle
    const packageMode = document.querySelector('input[name="packageMode"]:checked').value;
    if (packageMode === 'all') {
        formData.app_package = 'all';
    } else if (packageMode === 'manual') {
        const manualPackage = document.getElementById('manualPackage').value.trim();
        if (!manualPackage) {
            alert('Lütfen paket adını girin!');
            return;
        }
        formData.app_package = manualPackage;
    } else if (packageMode === 'select') {
        const selectedPackages = Array.from(document.querySelectorAll('.package-checkbox:checked'))
            .map(cb => cb.value);
        if (selectedPackages.length === 0) {
            alert('Lütfen en az bir paket seçin!');
            return;
        }
        formData.app_packages = selectedPackages;
    }
    
    // Gönder butonu
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Kaydediliyor...';
    
    fetch('save_duyuru.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Duyuru başarıyla eklendi!');
            location.reload();
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Kaydet';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Kaydet';
    });
});

// Edit Modal - Tür değişimi
document.getElementById('edit_type').addEventListener('change', function() {
    if (this.value === 'url') {
        document.getElementById('edit_urlField').style.display = 'block';
        document.getElementById('edit_url').required = true;
    } else {
        document.getElementById('edit_urlField').style.display = 'none';
        document.getElementById('edit_url').required = false;
    }
});

// Düzenleme modalını aç
function openEditModal(id) {
    // Duyuru bilgilerini fetch ile al
    fetch('get_duyuru.php?id=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const duyuru = data.data;
            
            // Form alanlarını doldur
            document.getElementById('edit_id').value = duyuru.id;
            document.getElementById('edit_title').value = duyuru.title;
            document.getElementById('edit_content').value = duyuru.content;
            document.getElementById('edit_type').value = duyuru.type;
            document.getElementById('edit_priority').value = duyuru.priority;
            document.getElementById('edit_url').value = duyuru.url || '';
            document.getElementById('edit_app_package').value = duyuru.app_package;
            document.getElementById('edit_status').checked = duyuru.status === 'active';
            
            // URL alanını göster/gizle
            if (duyuru.type === 'url') {
                document.getElementById('edit_urlField').style.display = 'block';
                document.getElementById('edit_url').required = true;
            } else {
                document.getElementById('edit_urlField').style.display = 'none';
                document.getElementById('edit_url').required = false;
            }
            
            // Modalı aç
            const modal = new bootstrap.Modal(document.getElementById('editDuyuruModal'));
            modal.show();
        } else {
            alert('Duyuru bilgileri yüklenemedi: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu!');
    });
}

// Düzenleme formu gönderimi
document.getElementById('editDuyuruForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        id: document.getElementById('edit_id').value,
        title: document.getElementById('edit_title').value,
        content: document.getElementById('edit_content').value,
        type: document.getElementById('edit_type').value,
        priority: document.getElementById('edit_priority').value,
        url: document.getElementById('edit_url').value,
        app_package: document.getElementById('edit_app_package').value,
        status: document.getElementById('edit_status').checked ? 'active' : 'inactive',
        csrf_token: '<?php echo htmlspecialchars(generateCSRFToken()); ?>'
    };
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Güncelleniyor...';
    
    fetch('update_duyuru.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Duyuru başarıyla güncellendi!');
            location.reload();
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Güncelle';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Güncelle';
    });
});
</script>
EOD;

include __DIR__ . '/../includes/footer.php';
?>