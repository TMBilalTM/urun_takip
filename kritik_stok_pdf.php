<?php
require_once 'config/config.php';

// Erişim kontrolü
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Sayfa başlığını ve aktif sayfayı ayarla
$pageTitle = 'Kritik Stok Raporu - ' . SITE_NAME;
$activePage = 'products'; // Ürünler menüsünü aktif yap

// Ürün verilerini al
$product = new Product();
$criticalStockProducts = $product->getCriticalStockProducts();

// PDF yerine direkt HTML çıktısı
header('Content-Type: text/html; charset=utf-8');

// Çıktı modunu kontrol et
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'html';
$printMode = ($mode === 'print');

if ($printMode) {
    // Yazdırma modunda sadece içerik göster
    $pageTitle = 'Kritik Stok Raporu';
} else {
    include 'includes/header.php';
}
?>

<?php if ($printMode): ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kritik Stok Raporu - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- HTML2PDF Kütüphanesi -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #d9534f;
        }
        .report-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #d9534f;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .stock-critical {
            color: #d9534f;
            font-weight: bold;
        }
        .stock-warning {
            color: #f0ad4e;
        }
        .report-footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            .no-print { display: none; }
            a { text-decoration: none; color: inherit; }
        }
    </style>
</head>
<body>
<?php endif; ?>

<div class="container-fluid <?= $printMode ? '' : 'mt-4' ?>">
    <?php if (!$printMode): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Kritik Stok Raporu</h1>
        <div>
            <a href="kritik_stok_pdf.php?mode=print" target="_blank" class="btn btn-secondary me-2">
                <i class="fas fa-print me-1"></i> Yazdır
            </a>
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Geri Dön
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- PDF olarak oluşturulacak içerik -->
    <div id="pdf-content">
        <div class="card border-0 <?= $printMode ? '' : 'shadow-sm' ?>">
            <?php if ($printMode): ?>
            <div class="report-header">
                <div class="report-title">KRİTİK STOK DURUMUNDAKI ÜRÜNLER</div>
                <div class="report-date">Rapor Tarihi: <?= date('d.m.Y H:i') ?></div>
            </div>
            <?php else: ?>
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Kritik Stok Listesi</h5>
            </div>
            <?php endif; ?>

            <div class="<?= $printMode ? '' : 'card-body' ?>">
                <div class="alert <?= $printMode ? 'alert-light' : 'alert-danger' ?> mb-4">
                    <strong>Toplam Kritik Stok Ürünü:</strong> <?= count($criticalStockProducts) ?>
                </div>
                
                <?php if (count($criticalStockProducts) > 0): ?>
                <div class="table-responsive">
                    <table class="table <?= $printMode ? '' : 'table-striped table-hover table-bordered' ?>">
                        <thead>
                            <tr>
                                <th>Ürün Kodu</th>
                                <th>Ürün Adı</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Kritik</th>
                                <th>Birim</th>
                                <th>Fiyat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($criticalStockProducts as $item): ?>
                            <?php 
                                $criticalClass = ($item['stok_miktari'] <= $item['kritik_stok_seviyesi'] * 0.5) ? 'stock-critical' : 'stock-warning'; 
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['urun_kodu']) ?></td>
                                <td><?= htmlspecialchars($item['urun_adi']) ?></td>
                                <td><?= htmlspecialchars($item['kategori_adi'] ?? 'Kategorisiz') ?></td>
                                <td class="<?= $criticalClass ?>"><?= $item['stok_miktari'] ?></td>
                                <td><?= $item['kritik_stok_seviyesi'] ?></td>
                                <td><?= htmlspecialchars($item['birim']) ?></td>
                                <td><?= number_format($item['fiyat'], 2, ',', '.') ?> ₺</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> Kritik stok seviyesinde ürün bulunmamaktadır.
                </div>
                <?php endif; ?>

                <?php if ($printMode): ?>
                <div class="report-footer">
                    Bu rapor <?= SITE_NAME ?> tarafından <?= date('d.m.Y H:i') ?> tarihinde oluşturulmuştur.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if ($printMode): ?>
    <div class="text-center mt-4 mb-4 no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Yazdır
        </button>
        <button class="btn btn-danger ms-2" onclick="downloadPDF()">
            <i class="fas fa-file-pdf me-1"></i> PDF İndir
        </button>
        <a href="kritik_stok_pdf.php" class="btn btn-secondary ms-2">
            <i class="fas fa-eye me-1"></i> Normal Görünüm
        </a>
    </div>

    <script>
        // PDF İndirme Fonksiyonu
        function downloadPDF() {
            // PDF indirme işlemi başladı mesajı
            const downloadBtn = document.querySelector('button.btn-danger');
            const originalText = downloadBtn.innerHTML;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Hazırlanıyor...';
            downloadBtn.disabled = true;
            
            // PDF seçenekleri
            const options = {
                margin: 10,
                filename: 'Kritik_Stok_Raporu_<?= date("Y-m-d") ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // PDF'i oluştur ve indir
            const content = document.getElementById('pdf-content');
            
            html2pdf().from(content).set(options).save()
                .then(() => {
                    // İşlem tamamlandı
                    downloadBtn.innerHTML = originalText;
                    downloadBtn.disabled = false;
                })
                .catch(err => {
                    console.error('PDF indirme hatası:', err);
                    downloadBtn.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Hata!';
                    downloadBtn.disabled = false;
                    alert('PDF indirme sırasında bir hata oluştu: ' + err.message);
                });
        }
        
        // Sayfaya girildiğinde otomatik yazdırma diyaloğunu göster
        window.addEventListener('load', function() {
            // 1 saniye gecikmeyle yazdırma diyaloğunu aç (sayfanın tam yüklenmesini bekle)
            setTimeout(function() {
                // window.print();
            }, 1000);
        });
    </script>
    <?php endif; ?>
</div>

<?php if ($printMode): ?>
</body>
</html>
<?php else: ?>
<?php include 'includes/footer.php'; ?>
<?php endif; ?>
