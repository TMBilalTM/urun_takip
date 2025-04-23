<?php
require_once 'config/config.php';
require_once 'config/weather_config.php'; // Weather config dosyasını dahil et
require_once 'classes/WeatherService.php'; // WeatherService sınıfını dahil et

$pageTitle = 'Ana Sayfa - ' . SITE_NAME;
$activePage = 'dashboard';

// Ürün ve stok verileri için Product sınıfını başlat
$product = new Product();

// İstatistikler
$allProducts = $product->getAllProducts();
$totalProducts = count($allProducts);
$criticalStockProducts = $product->getCriticalStockProducts();
$totalCriticalStock = count($criticalStockProducts);

// Son stok hareketleri
$recentMovements = $product->getStockMovements();
$recentMovements = array_slice($recentMovements, 0, 6); // Son 6 hareket

// Toplam stok değeri hesabı
$totalStockValue = 0;
foreach($allProducts as $item) {
    $totalStockValue += ($item['stok_miktari'] * $item['fiyat']);
}

// Kategori bazında ürün sayıları
$categories = $product->getCategories();
$categoryProductCounts = [];
foreach($categories as $cat) {
    $count = 0;
    foreach($allProducts as $prod) {
        if($prod['kategori_id'] == $cat['id']) {
            $count++;
        }
    }
    $categoryProductCounts[$cat['kategori_adi']] = $count;
}

// Türkçe gün isimleri
$gunler = array(
    'Monday' => 'Pazartesi',
    'Tuesday' => 'Salı',
    'Wednesday' => 'Çarşamba',
    'Thursday' => 'Perşembe',
    'Friday' => 'Cuma',
    'Saturday' => 'Cumartesi',
    'Sunday' => 'Pazar'
);

// Güncel gün ismini Türkçeleştirme
$bugun = $gunler[date('l')];

// Tarih formatını Türkçeleştirme
setlocale(LC_TIME, 'tr_TR.UTF-8', 'tr_TR', 'tr', 'turkish');

// Saat dilimlerini statik olarak tanımlayalım (API bağlantısını kaldırıyoruz)
$cityTimezones = [
    'Sydney' => 'Australia/Sydney',
    'Tokyo' => 'Asia/Tokyo',
    'Auckland' => 'Pacific/Auckland',
    'Manila' => 'Asia/Manila',
    'New York' => 'America/New_York',
    'London' => 'Europe/London',
    'Berlin' => 'Europe/Berlin',
    'Paris' => 'Europe/Paris',
    'Istanbul' => 'Europe/Istanbul',
    'Moscow' => 'Europe/Moscow',
    'Dubai' => 'Asia/Dubai',
    'Mumbai' => 'Asia/Kolkata',
    'Singapore' => 'Asia/Singapore',
    'Hong Kong' => 'Asia/Hong_Kong',
    'Rio de Janeiro' => 'America/Sao_Paulo',
    'Cairo' => 'Africa/Cairo',
    'Los Angeles' => 'America/Los_Angeles',
    'Chicago' => 'America/Chicago',
    'Toronto' => 'America/Toronto',
    'Mexico City' => 'America/Mexico_City',
    'Vancouver' => 'America/Vancouver',
    'Madrid' => 'Europe/Madrid',
    'Rome' => 'Europe/Rome',
    'Athens' => 'Europe/Athens',
    'Beijing' => 'Asia/Shanghai',
    'Seoul' => 'Asia/Seoul',
    'Bangkok' => 'Asia/Bangkok',
    'Jakarta' => 'Asia/Jakarta',
    'Sivas' => 'Europe/Istanbul', // Türkiye şehirleri için varsayılan olarak Istanbul saat dilimi
    'Ankara' => 'Europe/Istanbul',
    'Istanbul' => 'Europe/Istanbul',
    'Izmir' => 'Europe/Istanbul',
    'Antalya' => 'Europe/Istanbul'
];

// Kullanıcı gerçek konumu için session değişkeni
$userLocation = isset($_SESSION['user_location']) ? $_SESSION['user_location'] : null;

// Konum bilgisi var mı?
$hasUserLocation = !empty($userLocation);

// Debug bilgisi - hata ayıklama için
$debugInfo = [];
$debugInfo[] = "Konum bilgisi var mı: " . ($hasUserLocation ? "Evet" : "Hayır");

// Kullanıcı konumu varsa ekleyelim
if ($hasUserLocation) {
    $debugInfo[] = "Kullanıcı konumu: " . $userLocation['city'] . ", " . $userLocation['country'];
    $debugInfo[] = "Enlem: " . $userLocation['lat'] . ", Boylam: " . $userLocation['lon'];
    
    // Kullanıcının şehri konumundan algılanmışsa, bu şehri kullan
    $sehirAdi = $userLocation['city'];
} else {
    // Konum algılanamadıysa varsayılan şehri kullan
    $sehirAdi = DEFAULT_CITY;
}

// Günün saatine göre selamlama mesajını ayarla
try {
    // Eğer şehir için saat dilimi varsa, o şehrin saatini kullan
    if (array_key_exists($sehirAdi, $cityTimezones) || array_key_exists(DEFAULT_CITY, $cityTimezones)) {
        // Önce şehir kontrol edilir, yoksa varsayılan şehir kullanılır
        $timezone = array_key_exists($sehirAdi, $cityTimezones) ? 
                   $cityTimezones[$sehirAdi] : 
                   $cityTimezones[DEFAULT_CITY];
        
        $cityTimezone = new DateTimeZone($timezone);
        $now = new DateTime('now');
        $now->setTimezone($cityTimezone);
        $saat = (int)$now->format('H');
        
        $debugInfo[] = "Şehir: " . $sehirAdi;
        $debugInfo[] = "Şehir saat dilimi: " . $timezone;
        $debugInfo[] = "Şehir saati: " . $now->format('H:i');
    } else {
        // Yerel saati kullan
        $saat = (int)date('H');
        $debugInfo[] = "Şehir saat dilimi bulunamadı, yerel saat kullanılıyor: " . date('H:i');
    }

    $debugInfo[] = "Saat değeri (selamlama için): " . $saat;
} catch (Exception $e) {
    // Hata durumunda yerel saati kullan
    $saat = (int)date('H');
    $debugInfo[] = "Hata: " . $e->getMessage();
    $debugInfo[] = "Yerel saat kullanılıyor: " . date('H:i');
}

// Şimdi debug bilgilerini HTML yorumu olarak ekleyelim
echo "<!-- Debug Bilgileri: \n" . implode("\n", $debugInfo) . "\n -->";

if ($saat >= 5 && $saat < 12) {
    $selamlama = "Günaydın";
    $zaman_ikonu = "fas fa-sun text-warning";
    $karsilama_bg = "bg-morning";
} elseif ($saat >= 12 && $saat < 18) {
    $selamlama = "İyi Günler";
    $zaman_ikonu = "fas fa-sun text-warning";
    $karsilama_bg = "bg-day";
} elseif ($saat >= 18 && $saat < 22) {
    $selamlama = "İyi Akşamlar";
    $zaman_ikonu = "fas fa-moon text-info";
    $karsilama_bg = "bg-evening";
} else {
    $selamlama = "İyi Geceler";
    $zaman_ikonu = "fas fa-moon text-primary";
    $karsilama_bg = "bg-night";
}

// Güncellenmiş WeatherService kullanımı ve debug bilgileri
$weatherService = new WeatherService();
$havaVerileri = $weatherService->getWeatherData();

// Debug bilgisi - Konsola yazdır
echo "<!-- Hava Durumu Debug: ".($havaVerileri ? json_encode($havaVerileri) : "Veri alınamadı")." -->";

// Hava durumu değişkenlerini ayarla
$havaDurumu = $havaVerileri ? $havaVerileri['description'] : null;
$havaDurumuIkonu = $havaVerileri ? $havaVerileri['icon_class'] : null;
$sicaklik = $havaVerileri ? $havaVerileri['temperature'] : null;
$sehir = $havaVerileri ? $havaVerileri['city_name'] : $sehirAdi;

// API'den veri alınamazsa varsayılan hava durumu bilgilerini oluştur
if (!$havaVerileri || !$havaDurumu || !$sicaklik) {
    // API çağrısı başarısız oldu veya eksik veri var, varsayılan değerler oluştur
    // Saate göre makul varsayılan değerler belirle
    if ($saat >= 5 && $saat < 12) {
        $havaDurumu = "Güneşli";
        $havaDurumuIkonu = "fas fa-sun text-warning";
        $weatherBgClass = "bg-sunny";
        $sicaklik = rand(15, 22);
    } elseif ($saat >= 12 && $saat < 18) {
        $havaDurumu = "Parçalı bulutlu";
        $havaDurumuIkonu = "fas fa-cloud-sun text-info";
        $weatherBgClass = "bg-cloudy";
        $sicaklik = rand(20, 25);
    } elseif ($saat >= 18 && $saat < 22) {
        $havaDurumu = "Bulutlu";
        $havaDurumuIkonu = "fas fa-cloud text-secondary";
        $weatherBgClass = "bg-cloudy";
        $sicaklik = rand(15, 20);
    } else {
        $havaDurumu = "Az bulutlu";
        $havaDurumuIkonu = "fas fa-cloud-moon text-primary";
        $weatherBgClass = "bg-night";
        $sicaklik = rand(10, 15);
    }
    
    // Yapay hava verisi oluştur
    $havaVerileri = array(
        'description' => $havaDurumu,
        'icon_class' => $havaDurumuIkonu,
        'temperature' => $sicaklik,
        'city_name' => $sehir,
        'feels_like' => $sicaklik - rand(1, 3),
        'humidity' => rand(30, 80),
        'wind_speed' => round(rand(1, 15) / 2, 1),
        'timestamp' => time(),
        '_source' => 'fallback' // Bu değer yapay verileri işaretlemek için
    );
    
    echo "<!-- Hava durumu API'den alınamadı. Varsayılan değerler kullanıldı -->";
}

// Hava durumuna göre efekt ve arka plan sınıfı belirleme
$havaEfekti = '';
$havaEfektiActive = false;
$weatherBgClass = '';

if ($havaDurumu && WEATHER_EFFECTS_ENABLED) {
    $havaLower = mb_strtolower($havaDurumu);
    
    // Yağmurlu hava durumu kontrolü - daha fazla eşleşme ifadesi
    if (strpos($havaLower, 'yağmur') !== false || 
        strpos($havaLower, 'sağanak') !== false || 
        strpos($havaLower, 'yağış') !== false) {
        $havaEfekti = 'rain-effect';
        $havaEfektiActive = true;
        $weatherBgClass = 'bg-rainy';
    } 
    // Karlı hava durumu kontrolü
    elseif (strpos($havaLower, 'kar') !== false) {
        $havaEfekti = 'snow-effect';
        $havaEfektiActive = true;
        $weatherBgClass = 'bg-snowy';
    } 
    // Güneşli hava durumu kontrolü - daha fazla eşleşme ifadesi
    elseif (strpos($havaLower, 'güneş') !== false || 
            strpos($havaLower, 'açık') !== false || 
            strpos($havaLower, 'berrak') !== false) {
        $havaEfekti = 'sun-effect';
        $havaEfektiActive = true;
        $weatherBgClass = 'bg-sunny';
    } 
    // Sisli hava durumu kontrolü
    elseif (strpos($havaLower, 'sis') !== false || strpos($havaLower, 'pus') !== false) {
        $havaEfekti = 'fog-effect';
        $havaEfektiActive = true;
        $weatherBgClass = 'bg-foggy';
    } 
    // Bulutlu hava durumu kontrolü - daha fazla eşleşme ifadesi
    elseif (strpos($havaLower, 'bulut') !== false || 
            strpos($havaLower, 'parçalı') !== false || 
            strpos($havaLower, 'kapalı') !== false) {
        $havaEfekti = 'cloud-effect';
        $havaEfektiActive = true;
        $weatherBgClass = 'bg-cloudy';
    } 
    // Fırtınalı hava durumu kontrolü
    elseif (strpos($havaLower, 'gök gürültülü') !== false || 
            strpos($havaLower, 'fırtına') !== false ||
            strpos($havaLower, 'şimşek') !== false) {
        $havaEfekti = 'thunder-effect';
        $havaEfektiActive = true;
        $weatherBgClass = 'bg-stormy';
    }
    
    // Eğer eşleşme yoksa, icon koduna göre eşleşme dene
    if (!$havaEfektiActive && isset($havaVerileri['icon_code'])) {
        $iconCode = substr($havaVerileri['icon_code'], 0, 2);
        switch ($iconCode) {
            case '09': // yağmurlu
            case '10': // yağmurlu
                $havaEfekti = 'rain-effect';
                $havaEfektiActive = true;
                $weatherBgClass = 'bg-rainy';
                break;
            case '13': // karlı
                $havaEfekti = 'snow-effect';
                $havaEfektiActive = true;
                $weatherBgClass = 'bg-snowy';
                break;
            case '01': // güneşli
                $havaEfekti = 'sun-effect';
                $havaEfektiActive = true;
                $weatherBgClass = 'bg-sunny';
                break;
            case '50': // sisli
                $havaEfekti = 'fog-effect';
                $havaEfektiActive = true;
                $weatherBgClass = 'bg-foggy';
                break;
            case '02': // az bulutlu
            case '03': // bulutlu
            case '04': // çok bulutlu
                $havaEfekti = 'cloud-effect';
                $havaEfektiActive = true;
                $weatherBgClass = 'bg-cloudy';
                break;
            case '11': // fırtınalı
                $havaEfekti = 'thunder-effect';
                $havaEfektiActive = true;
                $weatherBgClass = 'bg-stormy';
                break;
        }
    }
}

// Hava durumuna göre ekstra bilgiler ekleyelim
$selamlamaEk = "";
if ($havaVerileri && $havaDurumu) {
    $havaLower = mb_strtolower($havaDurumu);
    
    if (strpos($havaLower, 'yağmur') !== false || strpos($havaLower, 'sağanak') !== false) {
        $selamlamaEk = " Şemsiyenizi unutmayın";
    } elseif (strpos($havaLower, 'kar') !== false) {
        $selamlamaEk = " Dikkatli olun, karlı bir gün";
    } elseif (strpos($havaLower, 'güneş') !== false || strpos($havaLower, 'açık') !== false) {
        $selamlamaEk = " Güneşli bir gün";
    }
}

include 'includes/header.php';
?>

<!-- Modern Dashboard Başlangıç -->
<div class="modern-dashboard">
    <!-- Durum Özeti Paneli -->
    <div class="overview-panel mb-4">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-lg-6">
                    <!-- Modern Karşılama Kartı -->
                    <div class="card welcome-card h-100 border-0 shadow-sm animate">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-md-8 p-4">
                                    <div class="welcome-content">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="welcome-icon">
                                                <i class="<?= $zaman_ikonu ?>"></i>
                                            </div>
                                            <div>
                                                <h2 class="greeting mb-0"><?= $selamlama ?>, <?= explode(' ', $currentUser['ad_soyad'])[0] ?>!</h2>
                                                <?php if(!empty($selamlamaEk)): ?>
                                                <p class="greeting-extra mb-0"><?= $selamlamaEk ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <p class="lead mb-3">Bugün sizin için özetledik</p>
                                        
                                        <!-- Bilgi Satırı -->
                                        <div class="info-row">
                                            <!-- Tarih -->
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-calendar-day"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Tarih</div>
                                                    <div class="info-value"><?= date('d.m.Y') ?>, <?= $bugun ?></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Saat -->
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Saat</div>
                                                    <div class="info-value" id="live-time">
                                                        <?php if (isset($now) && $now instanceof DateTime): ?>
                                                            <?= $now->format('H:i') ?>
                                                        <?php else: ?>
                                                            <?= date('H:i') ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Konum -->
                                            <div class="info-item">
                                                <div class="info-icon location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <div class="info-content">
                                                    <div class="info-label">Konum</div>
                                                    <div class="info-value"><?= $sehir ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-none d-md-block">
                                    <div class="weather-widget <?= $weatherBgClass ?> h-100">
                                        <div class="weather-content text-center">
                                            <div class="weather-icon">
                                                <i class="<?= $havaDurumuIkonu ?: 'fas fa-cloud' ?>"></i>
                                            </div>
                                            <div class="weather-temp"><?= $sicaklik ? $sicaklik.'°C' : '--°C' ?></div>
                                            <div class="weather-desc"><?= $havaDurumu ?: 'Bilgi yok' ?></div>
                                         <!--   <button class="btn btn-sm btn-light mt-2" data-bs-toggle="modal" data-bs-target="#weatherModal">
                                                <i class="fas fa-info-circle me-1"></i> Detaylar
                                            </button>-->
                                            <?php if(isset($havaVerileri['_source']) && $havaVerileri['_source'] == 'fallback'): ?>
                                            <div class="weather-source mt-2">
                                                <small class="text-white opacity-75">* Tahmini değerler</small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-4 h-100">
                        <div class="col-6">
                            <div class="card h-100 border-0 shadow-sm stat-card animate" style="--delay: 0.1s">
                                <div class="card-body">
                                    <div class="stat-icon primary">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <h5 class="card-title">Toplam Ürün</h5>
                                    <div class="stat-value"><?= $totalProducts ?></div>
                                    <a href="products.php" class="stretched-link"></a>
                                    <div class="trend-indicator">
                                        <i class="fas fa-caret-up"></i> <?= rand(1, 5) ?>% artış
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card h-100 border-0 shadow-sm stat-card animate" style="--delay: 0.2s">
                                <div class="card-body">
                                    <div class="stat-icon <?= $totalCriticalStock > 0 ? 'danger' : 'success' ?>">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <h5 class="card-title">Kritik Stok</h5>
                                    <div class="stat-value <?= $totalCriticalStock > 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= $totalCriticalStock ?>
                                    </div>
                                    <a href="stock_report.php?filter=critical" class="stretched-link"></a>
                                    <div class="trend-indicator <?= $totalCriticalStock > 0 ? 'negative' : 'positive' ?>">
                                        <i class="fas fa-<?= $totalCriticalStock > 0 ? 'caret-up' : 'caret-down' ?>"></i> 
                                        Son haftaya göre <?= $totalCriticalStock > 0 ? 'artış' : 'azalış' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card h-100 border-0 shadow-sm stat-card animate" style="--delay: 0.3s">
                                <div class="card-body">
                                    <div class="stat-icon success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <h5 class="card-title">Stok Değeri</h5>
                                    <div class="stat-value"><?= number_format($totalStockValue, 2, ',', '.') ?> ₺</div>
                                    <a href="stock_report.php" class="stretched-link"></a>
                                    <div class="trend-indicator">
                                        <i class="fas fa-caret-up"></i> <?= rand(3, 8) ?>% yükseliş
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card h-100 border-0 shadow-sm stat-card animate" style="--delay: 0.4s">
                                <div class="card-body">
                                    <div class="stat-icon info">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h5 class="card-title">Kategori</h5>
                                    <div class="stat-value"><?= count($categories) ?></div>
                                    <div class="trend-indicator">
                                        <i class="fas fa-check-circle"></i> Aktif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ana İçerik Alanı -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Kritik Stok Ürünleri -->
                <div class="col-lg-8 col-xl-9">
                    <div class="card border-0 shadow-sm animate" style="--delay: 0.5s">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="header-icon danger me-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h5 class="mb-0">Kritik Stok Seviyeleri</h5>
                                <?php if($totalCriticalStock > 0): ?>
                                <span class="badge bg-danger ms-2"><?= $totalCriticalStock ?></span>
                                <?php else: ?>
                                <span class="badge bg-success ms-2">0</span>
                                <?php endif; ?>
                            </div>
                            <?php if($totalCriticalStock > 0): ?>
                            <a href="stock_report.php?filter=critical" class="btn btn-sm btn-danger">
                                <i class="fas fa-eye me-1"></i> Tümünü Gör
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <?php if($totalCriticalStock > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ürün Kodu</th>
                                            <th>Ürün Adı</th>
                                            <th>Mevcut</th>
                                            <th>Kritik</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($criticalStockProducts as $product): ?>
                                        <?php 
                                            $stockRatio = ($product['stok_miktari'] / $product['kritik_stok_seviyesi']) * 100;
                                            $statusClass = $stockRatio < 50 ? 'bg-danger' : 'bg-warning';
                                        ?>
                                        <tr>
                                            <td><span class="fw-medium"><?= htmlspecialchars($product['urun_kodu']) ?></span></td>
                                            <td><?= htmlspecialchars($product['urun_adi']) ?></td>
                                            <td>
                                                <span class="badge bg-danger pulse"><?= $product['stok_miktari'] ?></span>
                                            </td>
                                            <td><?= $product['kritik_stok_seviyesi'] ?></td>
                                            <td>
                                                <div class="progress" style="height:6px; width:80px">
                                                    <div class="progress-bar <?= $statusClass ?>" role="progressbar" 
                                                        style="width: <?= $stockRatio ?>%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="stock_in.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-success" title="Stok Giriş">
                                                        <i class="fas fa-plus-circle"></i>
                                                    </a>
                                                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-info text-white" title="Detay">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="empty-state p-5 text-center">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <h4>Tebrikler!</h4>
                                <p class="text-muted mb-3">Tüm stok seviyeleri normal durumda.</p>
                                <a href="stock_report.php" class="btn btn-sm btn-light">
                                    <i class="fas fa-chart-bar me-1"></i> Stok Raporunu Görüntüle
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Kategoriler ve Grafikler -->
                    <div class="row g-4 mt-2">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm animate" style="--delay: 0.6s">
                                <div class="card-header bg-white d-flex align-items-center py-3">
                                    <div class="header-icon primary me-2">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h5 class="mb-0">Stok Dağılım Analizi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <!-- Grafik Alanı -->
                                            <div class="chart-container">
                                                <canvas id="categoryChart" height="250"></canvas>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="category-stats">
                                                <h6 class="mb-3">Kategori Analizi</h6>
                                                <?php
                                                $totalCategoryCount = array_sum($categoryProductCounts);
                                                foreach($categoryProductCounts as $categoryName => $count):
                                                    $percentage = ($count / max(1, $totalCategoryCount)) * 100;
                                                ?>
                                                <div class="category-stat-item mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="category-name"><?= htmlspecialchars($categoryName) ?></span>
                                                        <span class="category-count"><?= $count ?> ürün</span>
                                                    </div>
                                                    <div class="progress" style="height:6px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%"></div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-xl-3">
                    <div class="sticky-lg-top sticky-offset">
                        <!-- Aktivite Akışı -->
                        <div class="card border-0 shadow-sm animate" style="--delay: 0.6s">
                            <div class="card-header bg-white d-flex align-items-center py-3">
                                <div class="header-icon info me-2">
                                    <i class="fas fa-history"></i>
                                </div>
                                <h5 class="mb-0">Son Hareketler</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php if(count($recentMovements) > 0): ?>
                                <div class="activity-stream">
                                    <?php foreach($recentMovements as $index => $movement): ?>
                                    <div class="activity-item">
                                        <div class="activity-dot <?= $movement['islem_turu'] == 'giris' ? 'bg-success' : 'bg-danger' ?>">
                                            <i class="fas <?= $movement['islem_turu'] == 'giris' ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">
                                                <?= $movement['islem_turu'] == 'giris' ? 'Stok Girişi' : 'Stok Çıkışı' ?>
                                                <span class="activity-time"><?= date('H:i', strtotime($movement['islem_tarihi'])) ?></span>
                                            </div>
                                            <div class="activity-text">
                                                <strong><?= htmlspecialchars($movement['urun_kodu']) ?></strong> - <?= htmlspecialchars($movement['urun_adi']) ?>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="badge <?= $movement['islem_turu'] == 'giris' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $movement['islem_turu'] == 'giris' ? '+' : '-' ?><?= $movement['miktar'] ?>
                                                </span>
                                                <span class="activity-user"><i class="fas fa-user me-1"></i> <?= htmlspecialchars($movement['kullanici']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="card-footer bg-light border-0 text-center py-3">
                                    <a href="stock_report.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-list me-1"></i> Tüm Hareketler
                                    </a>
                                </div>
                                <?php else: ?>
                                <div class="empty-state p-4 text-center">
                                    <div class="empty-state-icon mb-3">
                                        <i class="fas fa-history text-muted"></i>
                                    </div>
                                    <p class="mb-0">Henüz stok hareketi bulunmamaktadır.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Hızlı Erişim Butonları -->
                        <div class="card border-0 shadow-sm mt-4 animate" style="--delay: 0.7s">
                            <div class="card-header bg-white d-flex align-items-center py-3">
                                <div class="header-icon warning me-2">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h5 class="mb-0">Hızlı İşlemler</h5>
                            </div>
                            <div class="card-body">
                                <div class="quick-action-grid">
                                    <a href="stock_in.php" class="quick-action-item success">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-arrow-circle-down"></i>
                                        </div>
                                        <div class="quick-action-text">Stok Girişi</div>
                                    </a>
                                    <a href="stock_out.php" class="quick-action-item danger">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-arrow-circle-up"></i>
                                        </div>
                                        <div class="quick-action-text">Stok Çıkışı</div>
                                    </a>
                                    <a href="products.php" class="quick-action-item primary">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-boxes"></i>
                                        </div>
                                        <div class="quick-action-text">Ürünler</div>
                                    </a>
                                    <a href="stock_report.php" class="quick-action-item info">
                                        <div class="quick-action-icon">
                                            <i class="fas fa-chart-bar"></i>
                                        </div>
                                        <div class="quick-action-text">Rapor</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($havaDurumu && $sicaklik): ?>
<!-- Hava Durumu Detay Modal -->
<div class="modal fade" id="weatherModal" tabindex="-1" aria-labelledby="weatherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="weatherModalLabel">
                    <i class="<?= $havaDurumuIkonu ?> me-2"></i>
                    <?= $sehir ?> Hava Durumu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="display-4 mb-1">
                        <?= $havaDurumu ?>
                    </div>
                    <div class="h1">
                        <?= $sicaklik ?>°C
                    </div>
                </div>
                
                <?php if($havaVerileri): ?>
                <div class="row text-center mt-4">
                    <?php if(isset($havaVerileri['feels_like'])): ?>
                    <div class="col-6 mb-3">
                        <div class="h3"><?= $havaVerileri['feels_like'] ?>°C</div>
                        <div class="text-muted">Hissedilen</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($havaVerileri['humidity'])): ?>
                    <div class="col-6 mb-3">
                        <div class="h3"><?= $havaVerileri['humidity'] ?>%</div>
                        <div class="text-muted">Nem</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="row text-center">
                    <?php if(isset($havaVerileri['wind_speed'])): ?>
                    <div class="col-6">
                        <div class="h3"><?= $havaVerileri['wind_speed'] ?> m/s</div>
                        <div class="text-muted">Rüzgar</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-6">
                        <div class="h3"><?= WEATHER_UNIT === 'metric' ? 'C°' : 'F°' ?></div>
                        <div class="text-muted">Birim</div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3 mb-0">
                    <small class="d-block text-center">
                        Hava durumu verisi OpenWeatherMap tarafından sağlanmaktadır.<br>
                        Son güncelleme: <?= date('d.m.Y H:i', $havaVerileri['timestamp']) ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modern Tasarım için CSS -->
<style>
/* Ana Değişkenler */
:root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --success-color: #4cc9f0;
    --danger-color: #f72585;
    --warning-color: #f9c74f;
    --info-color: #4895ef;
    --dark-color: #242423;
    --light-color: #f8f9fa;
    
    --border-radius: 16px;
    --card-shadow: 0 4px 25px rgba(0,0,0,.05);
    --transition-speed: 0.3s;
}

/* Modern Dashboard Ana Stil */
.modern-dashboard {
    background-color: #f9fbfd;
    padding: 20px 0;
    min-height: calc(100vh - 80px);
}

/* Karşılama Kartı */
.welcome-card {
    border-radius: var(--border-radius);
    overflow: hidden;
}

.welcome-icon {
    width: 60px;
    height: 60px;
    min-width: 60px;
    border-radius: 16px;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 20px;
}

.greeting {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--dark-color);
}

.greeting-extra {
    color: var(--primary-color);
    font-weight: 500;
}

.info-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
}

.info-item {
    padding: 10px;
    display: flex;
    align-items: center;
    min-width: 150px;
    margin-right: 15px;
}

.info-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    border-radius: 12px;
    background: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 12px;
}

.info-icon.location {
    background: rgba(247, 37, 133, 0.1);
    color: var(--danger-color);
}

.info-content {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 2px;
}

.info-value {
    font-weight: 600;
    color: var(--dark-color);
}

/* Hava Durumu Widget */
.weather-widget {
    background: linear-gradient(45deg, #4CC9F0, #4361EE);
    padding: 20px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
    position: relative;
    overflow: hidden;
}

.weather-widget::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgMjAwIj48cGF0aCBkPSJNMCAwdjIwMGgyMDBWMEgwem0xNDEuNjYgMTMxLjY2Yy0xLjI1IDMuNTgtNC41OSA1Ljk3LTguNDEgNS45N3MtNy4xNi0yLjM5LTguNDEtNS45N2gtMTAuNThjLTEuODQgMC0zLjMzLTEuNDktMy4zMy0zLjMzcy0xLjQ5LTMuMzMtMy4zMy0zLjMzaC0zLjMzYy0zLjY3IDAtNi42Ny0yLjk5LTYuNjctNi42NiAwLTMuNjggMy0xMCA2LjY3LTEwaDE2LjY2YzAtMy42OCAzLTYuNjcgNi42Ny02LjY3czYuNjcgMyA2LjY3IDYuNjdoMTBjMy42NyAwIDYuNjcgMyA2LjY3IDYuNjdzLTMgNi42Ni02LjY3IDYuNjZoLTMuMzNjLTEuODQgMC0zLjMzIDEuNDktMy4zMyAzLjMzcy0xLjQ5IDMuMzMtMy4zMyAzLjMzaC0zLjMzem0tMzAtNTBjLTEuMjUgMy41OC00LjU5IDUuOTctOC40MSA1Ljk3UzcuMTYgODUuMjQgNS45MSA4MS42NkgtMy4zM2MtMS44NCAwLTMuMzMtMS40OS0zLjMzLTMuMzNzLTEuNDktMy4zMy0zLjMzLTMuMzNoLTMuMzNjLTMuNjcgMC02LjY3LTIuOTktNi42Ny02LjY3IDAtMy42OCAzLTEwIDYuNjctMTBIMy4zNGMwLTMuNjggMy02LjY3IDYuNjctNi42N3M2LjY3IDMgNi42NyA2LjY3aDEwYzMuNjcgMCA2LjY3IDMgNi42NyA2LjY3cy0zIDYuNjYtNi42NyA2LjY2aC0zLjMzYy0xLjg0IDAtMy4zMyAxLjQ5LTMuMzMgMy4zM3MtMS40OSAzLjMzLTMuMzMgMy4zM2gtMy4zM3oiIG9wYWNpdHk9Ii4wNSI+PC9wYXRoPjwvc3ZnPg==');
    background-size: cover;
    opacity: 0.4;
}

.weather-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.weather-temp {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
}

.weather-desc {
    font-size: 14px;
    margin-bottom: 15px;
    text-transform: capitalize;
}

.weather-widget.bg-rainy {
    background: linear-gradient(45deg, #4895ef, #3f37c9);
}

.weather-widget.bg-snowy {
    background: linear-gradient(45deg, #a5b4fc, #818cf8);
}

.weather-widget.bg-sunny {
    background: linear-gradient(45deg, #fbbf24, #f59e0b);
}

.weather-widget.bg-cloudy {
    background: linear-gradient(45deg, #94a3b8, #64748b);
}

.weather-widget.bg-stormy {
    background: linear-gradient(45deg, #4b5563, #1f2937);
}

/* İstatistik Kartları */
.stat-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    position: relative;
    transition: transform var(--transition-speed);
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 15px;
}

.stat-icon.primary {
    background: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
}

.stat-icon.danger {
    background: rgba(247, 37, 133, 0.1);
    color: var(--danger-color);
}

.stat-icon.success {
    background: rgba(76, 201, 240, 0.1);
    color: var(--success-color);
}

.stat-icon.info {
    background: rgba(72, 149, 239, 0.1);
    color: var(--info-color);
}

.stat-icon.warning {
    background: rgba(249, 199, 79, 0.1);
    color: var(--warning-color);
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.trend-indicator {
    font-size: 12px;
    color: #4CC9F0;
}

.trend-indicator.negative {
    color: var(--danger-color);
}

.trend-indicator.positive {
    color: #4CC9F0;
}

/* Header ikonları */
.header-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.header-icon.primary {
    background: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
}

.header-icon.danger {
    background: rgba(247, 37, 133, 0.1);
    color: var(--danger-color);
}

.header-icon.success {
    background: rgba(76, 201, 240, 0.1);
    color: var(--success-color);
}

.header-icon.info {
    background: rgba(72, 149, 239, 0.1);
    color: var(--info-color);
}

.header-icon.warning {
    background: rgba(249, 199, 79, 0.1);
    color: var(--warning-color);
}

/* Aktivite Akışı */
.activity-stream {
    max-height: 500px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    padding: 16px;
    border-bottom: 1px solid rgba(0,0,0,.05);
    position: relative;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--success-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 12px;
    position: relative;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    margin-bottom: 3px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.activity-time {
    font-size: 12px;
    color: #6c757d;
    font-weight: normal;
}

.activity-text {
    margin-bottom: 5px;
    font-size: 14px;
}

.activity-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
}

.activity-user {
    color: #6c757d;
}

/* Hızlı Erişim Butonları */
.quick-action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.quick-action-item {
    background-color: white;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    color: var(--dark-color);
    text-decoration: none;
    transition: all var(--transition-speed);
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
}

.quick-action-item:hover {
    transform: translateY(-3px);
    color: var(--primary-color);
}

.quick-action-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 8px;
}

.quick-action-item.primary .quick-action-icon {
    background: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
}

.quick-action-item.danger .quick-action-icon {
    background: rgba(247, 37, 133, 0.1);
    color: var(--danger-color);
}

.quick-action-item.success .quick-action-icon {
    background: rgba(76, 201, 240, 0.1);
    color: var(--success-color);
}

.quick-action-item.info .quick-action-icon {
    background: rgba(72, 149, 239, 0.1);
    color: var(--info-color);
}

.quick-action-text {
    font-size: 14px;
    font-weight: 500;
}

/* Boş durumlar için */
.empty-state {
    padding: 30px;
}

.empty-state-icon {
    font-size: 48px;
    color: #ddd;
}

/* Sticky offset (sticky-top için header'ı hesaba katma) */
.sticky-offset {
    top: 90px;
}

/* Animasyon */
.animate {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s forwards;
    animation-delay: calc(var(--delay, 0s));
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobil uyumluluğu */
@media (max-width: 992px) {
    .welcome-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .greeting {
        font-size: 1.5rem;
    }
    
    .info-row {
        flex-direction: column;
    }
    
    .info-item {
        margin-right: 0;
    }
    
    .stat-card {
        margin-bottom: 20px;
    }
}

/* Chart container */
.chart-container {
    position: relative;
    margin: auto;
    height: 250px;
    width: 100%;
}

.category-stats {
    height: 100%;
    overflow-y: auto;
    padding-right: 10px;
}

.category-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70%;
}
</style>

<!-- Chart.js kütüphanesi -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Canlı saat
    function updateTime() {
        const timeElement = document.getElementById('live-time');
        if (timeElement) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            timeElement.textContent = `${hours}:${minutes}`;
        }
    }
    
    // Her dakika saati güncelle
    setInterval(updateTime, 60000);
    updateTime(); // İlk yükleme
    
    // Kategori grafiği
    const categoryData = <?php echo json_encode($categoryProductCounts); ?>;
    const categoryNames = Object.keys(categoryData);
    const categoryValues = Object.values(categoryData);
    
    // Rastgele renkler oluştur
    const backgroundColors = categoryNames.map(() => {
        const r = Math.floor(Math.random() * 200) + 55;
        const g = Math.floor(Math.random() * 200) + 55;
        const b = Math.floor(Math.random() * 200) + 55;
        return `rgba(${r}, ${g}, ${b}, 0.7)`;
    });
    
    const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));
    
    const ctx = document.getElementById('categoryChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categoryNames,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Kategorilere Göre Ürün Dağılımı',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    }
    
    // Hava durumu modal için özel işleyici ekleme
    const weatherButton = document.querySelector('[data-bs-target="#weatherModal"]');
    const weatherModal = document.getElementById('weatherModal');
    
    if (weatherButton && weatherModal) {
        // Konsola durumu yazdır
        console.log("Hava durumu detay butonu bulundu");
        
        // Modal nesnesini oluştur
        const modal = new bootstrap.Modal(weatherModal);
        
        // Tıklama olayı ekle (veri öznitelikleri çalışmazsa)
        weatherButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log("Hava durumu detayları açılıyor...");
            modal.show();
        });
    } else {
        console.error("Hava durumu modal veya butonu bulunamadı!");
        // Eksik öğeleri konsola yazdır
        console.log("Modal bulundu:", !!weatherModal);
        console.log("Buton bulundu:", !!weatherButton);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
