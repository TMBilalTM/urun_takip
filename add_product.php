<?php
require_once 'config/config.php';

$pageTitle = 'Ürün Ekle - ' . SITE_NAME;
$activePage = 'add_product';

// Erişim kontrolü - sadece admin
if($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$product = new Product();
$kategoriler = $product->getCategories();

$success = '';
$error = '';

// Form gönderildi mi kontrolü
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al
    $data = [
        'urun_kodu' => trim($_POST['urun_kodu']),
        'urun_adi' => trim($_POST['urun_adi']),
        'aciklama' => trim($_POST['aciklama']),
        'kategori_id' => $_POST['kategori_id'] ?: null,
        'fiyat' => floatval($_POST['fiyat']),
        'stok_miktari' => intval($_POST['stok_miktari']),
        'kritik_stok_seviyesi' => intval($_POST['kritik_stok_seviyesi']),
        'birim' => trim($_POST['birim']),
        'ekleyen_id' => $_SESSION['user_id']
    ];
    
    // Basit doğrulama
    if(empty($data['urun_kodu']) || empty($data['urun_adi']) || empty($data['birim'])) {
        $error = 'Ürün kodu, adı ve birim alanları zorunludur.';
    } else {
        // Ürün ekle
        $productId = $product->addProduct($data);
        
        if($productId) {
            // Stok hareketi ekle
            if($data['stok_miktari'] > 0) {
                $stockData = [
                    'urun_id' => $productId,
                    'kullanici_id' => $_SESSION['user_id'],
                    'islem_turu' => 'giris',
                    'miktar' => $data['stok_miktari'],
                    'aciklama' => 'İlk stok girişi'
                ];
                $product->addStockMovement($stockData);
            }
            
            $success = 'Ürün başarıyla eklendi.';
            // Formu temizle
            $data = [
                'urun_kodu' => '',
                'urun_adi' => '',
                'aciklama' => '',
                'kategori_id' => '',
                'fiyat' => '',
                'stok_miktari' => '',
                'kritik_stok_seviyesi' => '',
                'birim' => ''
            ];
        } else {
            $error = 'Ürün eklenirken bir hata oluştu.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Yeni Ürün Ekle</h1>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Ürünlere Dön
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
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="add_product.php" method="post" class="row g-3">
                <div class="col-md-6">
                    <label for="urun_kodu" class="form-label">Ürün Kodu</label>
                    <input type="text" class="form-control" id="urun_kodu" name="urun_kodu" value="<?= $data['urun_kodu'] ?? '' ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label for="urun_adi" class="form-label">Ürün Adı</label>
                    <input type="text" class="form-control" id="urun_adi" name="urun_adi" value="<?= $data['urun_adi'] ?? '' ?>" required>
                </div>
                
                <div class="col-md-12">
                    <label for="aciklama" class="form-label">Açıklama</label>
                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?= $data['aciklama'] ?? '' ?></textarea>
                </div>
                
                <div class="col-md-4">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Kategori Seçin</option>
                        <?php foreach($kategoriler as $kategori): ?>
                            <option value="<?= $kategori['id'] ?>" <?= (isset($data['kategori_id']) && $data['kategori_id'] == $kategori['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['kategori_adi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label for="fiyat" class="form-label">Fiyat</label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" id="fiyat" name="fiyat" value="<?= $data['fiyat'] ?? '' ?>" required>
                        <span class="input-group-text">₺</span>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label for="birim" class="form-label">Birim</label>
                    <select class="form-select" id="birim" name="birim" required>
                        <option value="">Birim Seçin</option>
                        <option value="Adet" <?= (isset($data['birim']) && $data['birim'] == 'Adet') ? 'selected' : '' ?>>Adet</option>
                        <option value="Kg" <?= (isset($data['birim']) && $data['birim'] == 'Kg') ? 'selected' : '' ?>>Kilogram</option>
                        <option value="Lt" <?= (isset($data['birim']) && $data['birim'] == 'Lt') ? 'selected' : '' ?>>Litre</option>
                        <option value="Metre" <?= (isset($data['birim']) && $data['birim'] == 'Metre') ? 'selected' : '' ?>>Metre</option>
                        <option value="Paket" <?= (isset($data['birim']) && $data['birim'] == 'Paket') ? 'selected' : '' ?>>Paket</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label for="stok_miktari" class="form-label">Başlangıç Stok Miktarı</label>
                    <input type="number" class="form-control" id="stok_miktari" name="stok_miktari" value="<?= $data['stok_miktari'] ?? '0' ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="kritik_stok_seviyesi" class="form-label">Kritik Stok Seviyesi</label>
                    <input type="number" class="form-control" id="kritik_stok_seviyesi" name="kritik_stok_seviyesi" value="<?= $data['kritik_stok_seviyesi'] ?? '10' ?>">
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Ürün Ekle
                    </button>
                    <button type="reset" class="btn btn-secondary ms-2">
                        <i class="fas fa-undo me-1"></i> Temizle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
