<?php
require_once 'config/config.php';

$pageTitle = 'Ürün Çıkış - ' . SITE_NAME;
$activePage = 'stock_out';

$product = new Product();
$selectedProduct = null;

// Bir ürün seçilip seçilmediğini kontrol et
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $selectedProduct = $product->getProductById($id);
}

$success = '';
$error = '';

// Form gönderildi mi kontrolü
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $urunId = intval($_POST['urun_id']);
    $miktar = intval($_POST['miktar']);
    $aciklama = trim($_POST['aciklama']);
    
    // Basit doğrulama
    if($urunId <= 0 || $miktar <= 0) {
        $error = 'Geçerli bir ürün ve miktar girmelisiniz.';
    } else {
        // Ürün bilgilerini kontrol et
        $currentProduct = $product->getProductById($urunId);
        
        // Stok kontrolü
        if($currentProduct && $currentProduct['stok_miktari'] >= $miktar) {
            // Stok güncelleme
            if($product->updateStock($urunId, $miktar, 'cikis')) {
                // Stok hareketi ekle
                $stockData = [
                    'urun_id' => $urunId,
                    'kullanici_id' => $_SESSION['user_id'],
                    'islem_turu' => 'cikis',
                    'miktar' => $miktar,
                    'aciklama' => $aciklama
                ];
                
                if($product->addStockMovement($stockData)) {
                    $success = 'Stok çıkışı başarıyla gerçekleştirildi.';
                    // Eğer ürün detay sayfasından geldiyse, seçili ürünün bilgilerini güncelle
                    if($selectedProduct && $selectedProduct['id'] == $urunId) {
                        $selectedProduct = $product->getProductById($urunId);
                    }
                } else {
                    $error = 'Stok hareketi kaydedilirken bir hata oluştu.';
                }
            } else {
                $error = 'Stok güncellenirken bir hata oluştu.';
            }
        } else {
            $error = 'Yetersiz stok! Mevcut stok: ' . $currentProduct['stok_miktari'];
        }
    }
}

// Tüm ürünleri getir
$allProducts = $product->getAllProducts();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Ürün Çıkış İşlemi</h1>
        <a href="stock_report.php" class="btn btn-secondary">
            <i class="fas fa-chart-bar me-1"></i> Stok Raporu
        </a>
    </div>
    
    <?php if($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Stok Çıkış Formu -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-arrow-circle-up me-1"></i> Ürün Çıkış Formu
                    </h5>
                </div>
                <div class="card-body">
                    <form action="stock_out.php<?= $selectedProduct ? '?id='.$selectedProduct['id'] : '' ?>" method="post">
                        <div class="mb-3">
                            <label for="urun_id" class="form-label">Ürün Seçin</label>
                            <select class="form-select" id="urun_id" name="urun_id" required <?= $selectedProduct ? 'disabled' : '' ?>>
                                <option value="">-- Ürün Seçin --</option>
                                <?php foreach($allProducts as $item): ?>
                                    <option value="<?= $item['id'] ?>" <?= ($selectedProduct && $selectedProduct['id'] == $item['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['urun_kodu'] . ' - ' . $item['urun_adi']) ?>
                                        (Stok: <?= $item['stok_miktari'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($selectedProduct): ?>
                                <input type="hidden" name="urun_id" value="<?= $selectedProduct['id'] ?>">
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="miktar" class="form-label">Çıkış Miktarı</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="miktar" name="miktar" min="1" 
                                       max="<?= $selectedProduct ? $selectedProduct['stok_miktari'] : '' ?>" required>
                                <span class="input-group-text">
                                    <?= $selectedProduct ? htmlspecialchars($selectedProduct['birim']) : 'Birim' ?>
                                </span>
                            </div>
                            <?php if($selectedProduct): ?>
                                <div class="form-text">
                                    Maksimum çıkış miktarı: <?= $selectedProduct['stok_miktari'] ?> <?= htmlspecialchars($selectedProduct['birim']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3" placeholder="Çıkış sebebi veya ek açıklama girebilirsiniz"></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save me-1"></i> Stok Çıkışı Yap
                            </button>
                            <?php if($selectedProduct): ?>
                                <a href="product_detail.php?id=<?= $selectedProduct['id'] ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Ürün Detayına Dön
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Seçili Ürün Bilgileri -->
        <?php if($selectedProduct): ?>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i> Seçili Ürün Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <h4><?= htmlspecialchars($selectedProduct['urun_adi']) ?></h4>
                    <div class="badge bg-secondary mb-3">
                        Kod: <?= htmlspecialchars($selectedProduct['urun_kodu']) ?>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Kategori:</strong> 
                                <?= htmlspecialchars($selectedProduct['kategori_adi'] ?: 'Kategorisiz') ?>
                            </div>
                            <div class="mb-2">
                                <strong>Birim:</strong> 
                                <?= htmlspecialchars($selectedProduct['birim']) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Fiyat:</strong> 
                                <?= number_format($selectedProduct['fiyat'], 2, ',', '.') ?> ₺
                            </div>
                            <div>
                                <strong>Kritik Stok Seviyesi:</strong> 
                                <?= $selectedProduct['kritik_stok_seviyesi'] ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert <?= $selectedProduct['stok_miktari'] <= $selectedProduct['kritik_stok_seviyesi'] ? 'alert-danger' : 'alert-success' ?>">
                        <div class="d-flex align-items-center">
                            <div class="display-5 me-3">
                                <?= $selectedProduct['stok_miktari'] ?>
                            </div>
                            <div>
                                <strong>Mevcut Stok Miktarı</strong><br>
                                <?= htmlspecialchars($selectedProduct['birim']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
