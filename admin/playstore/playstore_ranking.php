<?php
require 'simple_html_dom.php';

date_default_timezone_set('Europe/Istanbul'); // GMT+3 saat dilimi

// Anahtar kelimeler ve paket isimleri
$keywords = ["ehliyet sınav soruları 2025", "ehliyet sınav soruları", "ehliyet go"];
$packages = ["ehliyet.sinav.sorulari.app", "com.demirci.ehliyetsinavsorulari", "com.demirci.ehliyetsinavi"]; // Paket isimlerinizi buraya yazın

// Sonuçları kaydedeceğimiz dosya
$dataFile = 'results.json';

function formatTurkishDate($timestamp) {
    setlocale(LC_TIME, 'tr_TR.UTF-8'); // Türkçe dil ayarı
    return strftime('%d.%m.%Y %H:%M %A', strtotime($timestamp)); // Tarihi ve saati biçimlendir
}

// Google Play sıralamasını kontrol eden fonksiyon
function getGooglePlayRanking($keyword, $appId) {
    $url = "https://play.google.com/store/search?q=" . urlencode($keyword) . "&c=apps&hl=tr&gl=TR";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        return null;
    }

    $dom = str_get_html($html);

    $apps = $dom->find('div.VfPpkd-EScbFb-JIbuQc'); // Uygulama kartlarının sınıfı
    $rank = 1;

    foreach ($apps as $app) {
        $appLink = $app->find('a', 0)->href;
        if (strpos($appLink, $appId) !== false) {
            return $rank;
        }
        $rank++;
    }

    return null; // Uygulama bulunamazsa
}

function updateResults($keywords, $packages, $dataFile) {
    $results = [];

    // Mevcut verileri yükle
    if (file_exists($dataFile)) {
        $results = json_decode(file_get_contents($dataFile), true);
    }

    // Tarihleri kontrol etmek için sonuçları günlük olarak gruplama
    $groupedResults = [];
    foreach ($results as $result) {
        $dateKey = substr($result['timestamp'], 0, 10); // Yyyy-mm-dd formatında günlük anahtar
        $groupedResults[$dateKey][$result['keyword']][$result['package']] = $result;
    }

    foreach ($keywords as $keyword) {
        foreach ($packages as $package) {
            $rank = getGooglePlayRanking($keyword, $package);
            $timestamp = date('Y-m-d H:i:s');
            $dateKey = date('Y-m-d'); // Günlük anahtar
            $previousDateKey = date('Y-m-d', strtotime('-1 day')); // Bir önceki günün anahtarı

            $previousRank = $groupedResults[$previousDateKey][$keyword][$package]['rank'] ?? null; // Bir önceki günün sıralaması
            $change = null;

            // Eğer bir önceki gün varsa, değişim hesaplanır
            if ($previousRank !== null && $rank !== null) {
                $change = $rank - $previousRank;
            }

            // Günlük kayıtlar arasında yenile veya ekle
            $groupedResults[$dateKey][$keyword][$package] = [
                'timestamp' => $timestamp,
                'keyword' => $keyword,
                'package' => $package,
                'rank' => $rank,
                'previousRank' => $previousRank,
                'change' => $change,
            ];
        }
    }

    // Tüm günlük sonuçları tek bir listeye dönüştür
    $filteredResults = [];
    foreach ($groupedResults as $date => $dailyKeywords) {
        foreach ($dailyKeywords as $keyword => $packages) {
            foreach ($packages as $package => $data) {
                $filteredResults[] = $data;
            }
        }
    }

    // Son 30 günle sınırlayın
    usort($filteredResults, function ($a, $b) {
        $dateComparison = strtotime($b['timestamp']) - strtotime($a['timestamp']);
        if ($dateComparison !== 0) {
            return $dateComparison; // Önce tarihe göre sıralama
        }
        return strcmp($a['package'], $b['package']); // Tarih eşitse uygulama adına göre sıralama
    });

    $filteredResults = array_slice($filteredResults, 0, 30);

    // Veriyi dosyaya kaydet
    file_put_contents($dataFile, json_encode($filteredResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}





// Sorguyu başlat
updateResults($keywords, $packages, $dataFile);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Play Sıralama Takibi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .badge-rank {
            font-size: 1rem;
        }
        .rank-badge {
            font-size: 0.9rem;
            padding: 0.4em 0.7em;
        }
        .rank-badge-1 { background-color: #28a745; color: white; }
        .rank-badge-2 { background-color: #007bff; color: white; }
        .rank-badge-3 { background-color: #ffc107; color: black; }
        .rank-badge-default { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="text-center mb-4">📊 Google Play Sıralama Takibi</h1>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <input id="filterKeyword" type="text" class="form-control" placeholder="Anahtar kelimeye göre filtrele...">
        </div>
        <div class="col-12 col-md-6">
            <select id="filterPackage" class="form-select" multiple>
                <?php
                $packages = ["ehliyet.sinav.sorulari.app", "com.demirci.ehliyetsinavsorulari", "com.demirci.ehliyetsinavi"];
                foreach ($packages as $package) {
                    echo "<option value='$package'>$package</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <table class="table table-striped table-bordered mt-4">
        <thead class="table-dark">
            <tr>
                <th>Tarih (GMT+3)</th>
                <th>Anahtar Kelime</th>
                <th>Paket İsmi</th>
                <th>Sıralama</th>
            </tr>
        </thead>
<tbody id="resultsTable">
    <?php
    $dataFile = 'results.json';
    if (file_exists($dataFile)) {
        $results = json_decode(file_get_contents($dataFile), true);

        foreach ($results as $result) {
            $formattedDate = formatTurkishDate($result['timestamp']); // Türkçe tarih formatı
            $rank = $result['rank'] ?? 'Bulunamadı';
            $previousRank = $result['previousRank'] ?? null;
            $change = $result['change'] ?? null;
            $rankClass = 'rank-badge-default';

            // Renk ve sınıf belirleme
            if (is_numeric($rank)) {
                if ($rank == 1) $rankClass = 'rank-badge-1';
                elseif ($rank == 2) $rankClass = 'rank-badge-2';
                elseif ($rank == 3) $rankClass = 'rank-badge-3';
            }

            // Değişim okları
            $changeIcon = '';
            if ($previousRank !== null) {
                if ($change < 0) {
                    $changeIcon = "<span style='color: green;'>&uarr;</span>"; // Yükselme
                } elseif ($change > 0) {
                    $changeIcon = "<span style='color: red;'>&darr;</span>"; // Düşme
                }
            }

            echo "<tr>
                <td>{$formattedDate}</td>
                <td>{$result['keyword']}</td>
                <td>{$result['package']}</td>
                <td>
                    <span class='badge rank-badge {$rankClass}'>" . htmlspecialchars($rank) . "</span> $changeIcon
                </td>
            </tr>";
        }
    }
    ?>
</tbody>






    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const filterTable = () => {
        const keywordInput = document.getElementById("filterKeyword").value.toLowerCase();
        const selectedPackages = Array.from(document.getElementById("filterPackage").selectedOptions).map(option => option.value);
        const tableRows = document.querySelectorAll("#resultsTable tr");

        tableRows.forEach(row => {
            const keyword = row.cells[1].innerText.toLowerCase();
            const packageName = row.cells[2].innerText;

            const matchesKeyword = keyword.includes(keywordInput);
            const matchesPackage = selectedPackages.length === 0 || selectedPackages.includes(packageName);

            if (matchesKeyword && matchesPackage) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    };

    document.getElementById("filterKeyword").addEventListener("input", filterTable);
    document.getElementById("filterPackage").addEventListener("change", filterTable);
</script>
</body>
</html>


