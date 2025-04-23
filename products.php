<?php
require_once 'config/config.php';

$pageTitle = 'Ürünler - ' . SITE_NAME;
$activePage = 'products';

// Erişim kontrolü - sadece admin
if($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$product = new Product();

// Arama işlemi
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = [];

try {
    if(!empty($searchKeyword)) {
        // Arama terimini güvenli hale getir
        $searchKeyword = htmlspecialchars($searchKeyword);
        
        // Debug bilgisi ekle
        error_log("Arama yapılıyor: " . $searchKeyword);
        
        // searchProducts fonksiyonunu try-catch bloğu içinde çağır
        try {
            $products = $product->searchProducts($searchKeyword);
        } catch (Exception $e) {
            // Hata durumunda hata mesajını logla
            error_log("Arama hatası: " . $e->getMessage());
            
            // Geriye dönük uyumluluk için tüm ürünleri getir
            $products = $product->getAllProducts();
            
            // Kullanıcıya bir hata mesajı göster
            $_SESSION['error_message'] = "Arama sırasında bir hata oluştu. Tüm ürünler görüntüleniyor.";
        }
    } else {
        $products = $product->getAllProducts();
    }
} catch (Exception $e) {
    // Genel hata durumunda boş array kullan ve hata mesajını logla
    $products = [];
    error_log("Ürünleri getirme hatası: " . $e->getMessage());
    $_SESSION['error_message'] = "Ürünler yüklenirken bir hata oluştu.";
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Ürünler</h1>
        <a href="add_product.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Yeni Ürün Ekle
        </a>
    </div>
    
    <!-- Hata mesajı gösterimi -->
    <?php if(isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
    </div>
    <?php 
        // Mesajı gösterdikten sonra session'dan kaldır
        unset($_SESSION['error_message']);
    endif; 
    ?>
    
    <!-- Arama Formu -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="products.php" method="get" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($searchKeyword) ?>" placeholder="Ürün kodu, adı veya kategori ile ara...">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Ara</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Ürünler Tablosu -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="productsTable">
                    <thead>
                        <tr>
                            <th>Ürün Kodu</th>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>Birim</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($products) > 0): ?>
                            <?php foreach($products as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['urun_kodu']) ?></td>
                                    <td><?= htmlspecialchars($item['urun_adi']) ?></td>
                                    <td><?= htmlspecialchars($item['kategori_adi'] ?? 'Kategorisiz') ?></td>
                                    <td><?= number_format($item['fiyat'], 2, ',', '.') ?> ₺</td>
                                    <td>
                                        <?php if($item['stok_miktari'] <= $item['kritik_stok_seviyesi']): ?>
                                            <span class="badge bg-danger"><?= $item['stok_miktari'] ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?= $item['stok_miktari'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['birim']) ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-info" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="product_detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary" title="Detay">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="stock_movement.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-secondary" title="Stok Hareketleri">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <?php if(!empty($searchKeyword)): ?>
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-circle me-2"></i> Aramanızla eşleşen ürün bulunamadı.
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i> Henüz ürün eklenmemiş.
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// DataTable özelliklerini ayarla
document.addEventListener('DOMContentLoaded', function() {
    // DataTables kütüphanesinin yüklü olduğunu kontrol et
    if (typeof DataTable !== 'undefined') {
        // Tabloyu seç
        const table = document.getElementById('productsTable');
        
        // Tablo varsa DataTable'ı başlat
        if (table) {
            try {
                // DataTable'ı başlat
                const dataTable = new DataTable(table, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json',
                    },
                    pageLength: 25,
                    order: [], // Default sıralama kapatıldı
                    columnDefs: [
                        { orderable: false, targets: 6 } // İşlemler sütunu için sıralama kapalı
                    ],
                    responsive: true,
                    // Satır sayısına göre paging'i otomatik ayarla
                    paging: (table.querySelectorAll('tbody tr').length > 25)
                });
                
                console.log('DataTable başarıyla başlatıldı');
                
                // Tooltip'leri jQuery olmadan manuel olarak etkinleştir
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                tooltipTriggerList.forEach(el => {
                    // Bootstrap 5 API ile tooltip başlat
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        new bootstrap.Tooltip(el);
                    }
                });
                
            } catch (error) {
                console.error('DataTable başlatma hatası:', error);
            }
        } else {
            console.error('Tablo elementi bulunamadı: #productsTable');
        }
    } else {
        console.error('DataTable kütüphanesi yüklenmemiş!');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
