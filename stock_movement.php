<?php
require_once 'config/config.php';

$pageTitle = 'Stok Hareketleri - ' . SITE_NAME;
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

// Stok hareketlerini getir
$movements = $product->getStockMovements($id);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Stok Hareketleri</h1>
        <div>
            <a href="stock_in.php?id=<?= $id ?>" class="btn btn-success me-2">
                <i class="fas fa-arrow-circle-down me-1"></i> Stok Giriş
            </a>
            <a href="stock_out.php?id=<?= $id ?>" class="btn btn-danger me-2">
                <i class="fas fa-arrow-circle-up me-1"></i> Stok Çıkış
            </a>
            <a href="product_detail.php?id=<?= $id ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Ürün Detayına Dön
            </a>
        </div>
    </div>
    
    <!-- Ürün Bilgi Kartı -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4><?= htmlspecialchars($productData['urun_adi']) ?></h4>
                    <div class="badge bg-secondary mb-2">Kod: <?= htmlspecialchars($productData['urun_kodu']) ?></div>
                    
                    <div class="mb-1">
                        <strong>Kategori:</strong> 
                        <?= htmlspecialchars($productData['kategori_adi'] ?: 'Kategorisiz') ?>
                    </div>
                    <div class="mb-1">
                        <strong>Birim:</strong> 
                        <?= htmlspecialchars($productData['birim']) ?>
                    </div>
                    <div>
                        <strong>Fiyat:</strong> 
                        <?= number_format($productData['fiyat'], 2, ',', '.') ?> ₺
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="h5 mb-1">Mevcut Stok:</div>
                    <div class="display-5 mb-2">
                        <?= $productData['stok_miktari'] ?> <?= htmlspecialchars($productData['birim']) ?>
                    </div>
                    <div>
                        <?php if($productData['stok_miktari'] <= $productData['kritik_stok_seviyesi']): ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i> Kritik Stok Seviyesi
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i> Stok Durumu İyi
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stok Hareketleri Tablosu -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-history me-1"></i> Stok Hareket Geçmişi
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover datatable mb-0">
                    <thead>
                        <tr>
                            <th>İşlem No</th>
                            <th>İşlem Türü</th>
                            <th>Miktar</th>
                            <th>Açıklama</th>
                            <th>İşlemi Yapan</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($movements) > 0): ?>
                            <?php foreach($movements as $movement): ?>
                                <tr>
                                    <td><?= $movement['id'] ?></td>
                                    <td>
                                        <?php if($movement['islem_turu'] == 'giris'): ?>
                                            <span class="badge bg-success">Giriş</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Çıkış</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $movement['miktar'] ?> <?= htmlspecialchars($productData['birim']) ?></td>
                                    <td><?= htmlspecialchars($movement['aciklama'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($movement['kullanici']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($movement['islem_tarihi'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i> Bu ürüne ait stok hareketi bulunamadı.
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
