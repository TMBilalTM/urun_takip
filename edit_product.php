<?php
require_once 'config/config.php';

$pageTitle = 'Ürün Düzenle - ' . SITE_NAME;
$activePage = 'products';

// Erişim kontrolü - sadece admin
if($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Ürün ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = intval($_GET['id']);
$product = new Product();
$categories = $product->getCategories();

// Ürün bilgilerini al
$productData = $product->getProductById($id);
if(!$productData) {
    header('Location: products.php');
    exit;
}

$success = '';
$error = '';

// Form gönderildi mi kontrolü
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al
    $data = [
        'id' => $id,
        'urun_kodu' => trim($_POST['urun_kodu']),
        'urun_adi' => trim($_POST['urun_adi']),
        'aciklama' => trim($_POST['aciklama']),
        'kategori_id' => $_POST['kategori_id'] ?: null,
        'fiyat' => floatval($_POST['fiyat']),
        'kritik_stok_seviyesi' => intval($_POST['kritik_stok_seviyesi']),
        'birim' => trim($_POST['birim'])
    ];
    
    // Basit doğrulama
    if(empty($data['urun_kodu']) || empty($data['urun_adi']) || empty($data['birim'])) {
        $error = 'Ürün kodu, adı ve birim alanları zorunludur.';
    } else {
        // Ürün güncelle
        if($product->updateProduct($data)) {
            $success = 'Ürün başarıyla güncellendi.';
            // Güncel verileri al
            $productData = $product->getProductById($id);
        } else {
            $error = 'Ürün güncellenirken bir hata oluştu.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Ürün Düzenle</h1>
        <div>
            <a href="products.php" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Ürünlere Dön
            </a>
            <a href="product_detail.php?id=<?= $id ?>" class="btn btn-info">
                <i class="fas fa-eye me-1"></i> Ürün Detayı
            </a>
        </div>
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
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="edit_product.php?id=<?= $id ?>" method="post" class="row g-3">
                <div class="col-md-6">
                    <label for="urun_kodu" class="form-label">Ürün Kodu</label>
                    <input type="text" class="form-control" id="urun_kodu" name="urun_kodu" value="<?= htmlspecialchars($productData['urun_kodu']) ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="urun_adi" class="form-label">Ürün Adı</label>
                    <input type="text" class="form-control" id="urun_adi" name="urun_adi" value="<?= htmlspecialchars($productData['urun_adi']) ?>" required>
                </div>
                
                <div class="col-md-12">
                    <label for="aciklama" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?= htmlspecialchars($productData['aciklama']) ?></textarea>
                </div>
                
                <div class="col-md-4">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Kategori Seçin</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= ($productData['kategori_id'] == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['kategori_adi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="fiyat" class="form-label">Fiyat</label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" id="fiyat" name="fiyat" value="<?= $productData['fiyat'] ?>" required>
                        <span class="input-group-text">₺</span>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="birim" class="form-label">Birim</label>
                    <select class="form-select" id="birim" name="birim" required>
                        <option value="">Birim Seçin</option>
                        <option value="Adet" <?= ($productData['birim'] == 'Adet') ? 'selected' : '' ?>>Adet</option>
                        <option value="Kg" <?= ($productData['birim'] == 'Kg') ? 'selected' : '' ?>>Kilogram</option>
                        <option value="Lt" <?= ($productData['birim'] == 'Lt') ? 'selected' : '' ?>>Litre</option>
                        <option value="Metre" <?= ($productData['birim'] == 'Metre') ? 'selected' : '' ?>>Metre</option>
                        <option value="Paket" <?= ($productData['birim'] == 'Paket') ? 'selected' : '' ?>>Paket</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="stok_miktari" class="form-label">Mevcut Stok Miktarı</label>
                    <input type="number" class="form-control" value="<?= $productData['stok_miktari'] ?>" readonly disabled>
                    <div class="form-text">
                        Stok miktarını değiştirmek için <a href="stock_in.php?id=<?= $id ?>">Ürün Giriş</a> veya <a href="stock_out.php?id=<?= $id ?>">Ürün Çıkış</a> işlemi yapın.
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="kritik_stok_seviyesi" class="form-label">Kritik Stok Seviyesi</label>
                    <input type="number" class="form-control" id="kritik_stok_seviyesi" name="kritik_stok_seviyesi" value="<?= $productData['kritik_stok_seviyesi'] ?>">
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Değişiklikleri Kaydet
                    </button>
                    <a href="products.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> İptal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
