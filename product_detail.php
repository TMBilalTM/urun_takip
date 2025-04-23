<?php
require_once 'config/config.php';

$pageTitle = 'Ürün Detayı - ' . SITE_NAME;
$activePage = 'products';

// Ürün ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = intval($_GET['id']);
$product = new Product();

// Ürün bilgilerini al
$productData = $product->getProductById($id);
if(!$productData) {
    header('Location: products.php');
    exit;
}

// Son hareketleri al (en son 5 kayıt)
$movements = $product->getStockMovements($id);
$movements = array_slice($movements, 0, 5);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Ürün Detayı</h1>
        <div>
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <a href="edit_product.php?id=<?= $id ?>" class="btn btn-primary me-2">
                    <i class="fas fa-edit me-1"></i> Düzenle
                </a>
            <?php endif; ?>
            <a href="stock_in.php?id=<?= $id ?>" class="btn btn-success me-2">
                <i class="fas fa-arrow-circle-down me-1"></i> Stok Giriş
            </a>
            <a href="stock_out.php?id=<?= $id ?>" class="btn btn-danger me-2">
                <i class="fas fa-arrow-circle-up me-1"></i> Stok Çıkış
            </a>
            <a href="<?= $_SESSION['user_role'] == 'admin' ? 'products.php' : 'dashboard.php' ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Geri
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Ürün Detay Kartı -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i> Ürün Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4 class="mb-3"><?= htmlspecialchars($productData['urun_adi']) ?></h4>
                            <div class="badge bg-secondary mb-3">Ürün Kodu: <?= htmlspecialchars($productData['urun_kodu']) ?></div>
                            
                            <p class="text-muted mb-3">
                                <?= htmlspecialchars($productData['aciklama'] ?: 'Bu ürün için açıklama girilmemiş.') ?>
                            </p>
                            
                            <div class="mb-2">
                                <strong>Kategori:</strong> 
                                <?= htmlspecialchars($productData['kategori_adi'] ?: 'Kategorisiz') ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong>Birim:</strong> 
                                <?= htmlspecialchars($productData['birim']) ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Fiyat:</strong> 
                                <?= number_format($productData['fiyat'], 2, ',', '.') ?> ₺
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <div class="display-3 fw-bold mb-2">
                                        <?= $productData['stok_miktari'] ?>
                                    </div>
                                    <div class="text-muted mb-3">Mevcut Stok (<?= htmlspecialchars($productData['birim']) ?>)</div>
                                    
                                    <?php if($productData['stok_miktari'] <= $productData['kritik_stok_seviyesi']): ?>
                                        <div class="alert alert-danger mb-0 text-center w-100">
                                            <i class="fas fa-exclamation-triangle me-1"></i> 
                                            <strong>Kritik Stok Seviyesi!</strong><br>
                                            (Minimum: <?= $productData['kritik_stok_seviyesi'] ?> <?= $productData['birim'] ?>)
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success mb-0 text-center w-100">
                                            <i class="fas fa-check-circle me-1"></i> 
                                            Stok durumu iyi<br>
                                            (Minimum: <?= $productData['kritik_stok_seviyesi'] ?> <?= $productData['birim'] ?>)
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <div class="mb-1"><strong>Eklenme Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($productData['eklenme_tarihi'])) ?></div>
                                <div><strong>Son Güncelleme:</strong> <?= date('d.m.Y H:i', strtotime($productData['guncelleme_tarihi'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Son Hareketler -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-1"></i> Son Stok Hareketleri
                        </h5>
                        <a href="stock_movement.php?id=<?= $id ?>" class="btn btn-sm btn-light">
                            Tümünü Gör
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if(count($movements) > 0): ?>
                            <?php foreach($movements as $movement): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if($movement['islem_turu'] == 'giris'): ?>
                                                <span class="badge bg-success me-1">+ <?= $movement['miktar'] ?></span>
                                                <span class="text-success">Giriş</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger me-1">- <?= $movement['miktar'] ?></span>
                                                <span class="text-danger">Çıkış</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('d.m.Y H:i', strtotime($movement['islem_tarihi'])) ?>
                                        </small>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?= htmlspecialchars($movement['aciklama'] ?: 'Açıklama yok') ?>
                                    </div>
                                    <div class="small">
                                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($movement['kullanici']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center py-4">
                                <i class="fas fa-info-circle me-1"></i> Bu ürüne ait hareket kaydı bulunamadı.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
