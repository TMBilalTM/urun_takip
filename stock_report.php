<?php
require_once 'config/config.php';

$pageTitle = 'Stok Raporu - ' . SITE_NAME;
$activePage = 'stock_report';

$product = new Product();

// Filtreleme işlemi
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Filtre seçeneklerine göre ürünleri getir
$products = [];
switch($filter) {
    case 'critical':
        $products = $product->getCriticalStockProducts();
        break;
    case 'category':
        if($categoryId > 0) {
            // Kategori filtreleme işlemi burada yapılmalı
            // Örnek olarak tüm ürünleri getirip PHP tarafında filtreliyoruz
            $allProducts = $product->getAllProducts();
            foreach($allProducts as $prod) {
                if($prod['kategori_id'] == $categoryId) {
                    $products[] = $prod;
                }
            }
        } else {
            $products = $product->getAllProducts();
        }
        break;
    case 'zero':
        // Stok miktarı 0 olan ürünleri getir
        $allProducts = $product->getAllProducts();
        foreach($allProducts as $prod) {
            if($prod['stok_miktari'] == 0) {
                $products[] = $prod;
            }
        }
        break;
    default:
        $products = $product->getAllProducts();
}

// Kategorileri getir
$categories = $product->getCategories();

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Stok Raporu</h1>
        <p class="text-muted">Ürün stok durumlarını incelemek ve filtrelemek için kullanın</p>
    </div>
    <div class="d-flex">
        <a href="stock_in.php" class="btn btn-success me-2">
            <i class="fas fa-arrow-circle-down me-1"></i> Ürün Giriş
        </a>
        <a href="stock_out.php" class="btn btn-danger">
            <i class="fas fa-arrow-circle-up me-1"></i> Ürün Çıkış
        </a>
    </div>
</div>

<!-- Filtre Kartı -->
<div class="card animate mb-4">
    <div class="card-body">
        <form action="stock_report.php" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="filter" class="form-label">Filtrele</label>
                <select class="form-select form-select-lg" id="filter" name="filter" onchange="toggleCategorySelect()">
                    <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Tüm Ürünler</option>
                    <option value="critical" <?= $filter == 'critical' ? 'selected' : '' ?>>Kritik Stok Seviyesi</option>
                    <option value="zero" <?= $filter == 'zero' ? 'selected' : '' ?>>Stok Miktarı 0</option>
                    <option value="category" <?= $filter == 'category' ? 'selected' : '' ?>>Kategoriye Göre</option>
                </select>
            </div>
            
            <div class="col-md-4" id="categorySelectContainer" style="<?= $filter != 'category' ? 'display: none;' : '' ?>">
                <label for="category" class="form-label">Kategori Seçin</label>
                <select class="form-select form-select-lg" id="category" name="category">
                    <option value="0">Tüm Kategoriler</option>
                    <?php foreach($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $categoryId == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['kategori_adi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filtrele
                    </button>
                    <a href="stock_report.php" class="btn btn-outline-secondary">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stok Rapor Tablosu -->
<div class="card animate">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <?php if($filter == 'critical'): ?>
                <i class="fas fa-exclamation-triangle text-danger me-2"></i> Kritik Stok Seviyeleri
            <?php elseif($filter == 'zero'): ?>
                <i class="fas fa-times-circle text-danger me-2"></i> Stok Miktarı 0 Olan Ürünler
            <?php elseif($filter == 'category' && $categoryId > 0): ?>
                <?php 
                $catName = '';
                foreach($categories as $cat) {
                    if($cat['id'] == $categoryId) {
                        $catName = $cat['kategori_adi'];
                        break;
                    }
                }
                ?>
                <i class="fas fa-tag me-2"></i> <?= htmlspecialchars($catName) ?> Kategorisi
            <?php else: ?>
                <i class="fas fa-list me-2"></i> Tüm Ürünler
            <?php endif; ?>
        </h5>
        
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleViewBtn">
                <i class="fas fa-table"></i>
            </button>
            <a href="#" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-excel"></i>
            </a>
            <a href="#" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-pdf"></i>
            </a>
            <a href="#" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-print"></i>
            </a>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover datatable mb-0" id="stockReportTable">
                <thead>
                    <tr>
                        <th>Ürün Kodu</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Kritik Seviye</th>
                        <th>Birim</th>
                        <th>Değer</th>
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
                                <td>
                                    <?php if($item['stok_miktari'] <= $item['kritik_stok_seviyesi']): ?>
                                        <span class="badge bg-danger pulse"><?= $item['stok_miktari'] ?></span>
                                    <?php elseif($item['stok_miktari'] == 0): ?>
                                        <span class="badge bg-danger"><?= $item['stok_miktari'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= $item['stok_miktari'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['kritik_stok_seviyesi'] ?></td>
                                <td><?= htmlspecialchars($item['birim']) ?></td>
                                <td><?= number_format($item['stok_miktari'] * $item['fiyat'], 2, ',', '.') ?> ₺</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="product_detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Detay">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="stock_in.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Stok Giriş">
                                            <i class="fas fa-arrow-circle-down"></i>
                                        </a>
                                        <a href="stock_out.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Stok Çıkış">
                                            <i class="fas fa-arrow-circle-up"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="alert alert-info d-inline-flex align-items-center mb-0 px-4 py-3">
                                    <i class="fas fa-info-circle me-3 fa-2x"></i> 
                                    <div>Seçilen filtreye uygun ürün bulunamadı.</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Toplam değerleri -->
        <?php if(count($products) > 0): ?>
        <div class="bg-light p-4 border-top">
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="fas fa-boxes text-primary fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Toplam Ürün Adedi</div>
                                <div class="h4">
                                    <?php 
                                    $totalStock = 0; 
                                    foreach($products as $item) { 
                                        $totalStock += $item['stok_miktari']; 
                                    }
                                    echo $totalStock;
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="fas fa-shopping-cart text-success fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Ürün Çeşidi</div>
                                <div class="h4">
                                    <?= count($products) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                <i class="fas fa-money-bill-wave text-info fa-2x"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Toplam Stok Değeri</div>
                                <div class="h4">
                                    <?php 
                                    $totalValue = 0; 
                                    foreach($products as $item) { 
                                        $totalValue += $item['stok_miktari'] * $item['fiyat']; 
                                    }
                                    echo number_format($totalValue, 2, ',', '.') . ' ₺';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Grid view (default hidden) -->
<div id="gridView" class="mt-4" style="display:none;">
    <div class="product-grid">
        <?php if(count($products) > 0): ?>
            <?php foreach($products as $item): ?>
                <div class="card product-card animate">
                    <div class="product-img">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($item['urun_adi']) ?></h5>
                        <div class="badge bg-secondary mb-2"><?= htmlspecialchars($item['urun_kodu']) ?></div>
                        <p class="small text-muted"><?= htmlspecialchars($item['kategori_adi'] ?? 'Kategorisiz') ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <div class="small text-muted">Stok</div>
                                <?php if($item['stok_miktari'] <= $item['kritik_stok_seviyesi']): ?>
                                    <span class="badge bg-danger"><?= $item['stok_miktari'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= $item['stok_miktari'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="small text-muted">Değer</div>
                                <div class="fw-bold"><?= number_format($item['stok_miktari'] * $item['fiyat'], 2, ',', '.') ?> ₺</div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-3">
                            <a href="product_detail.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="fas fa-eye me-1"></i> Detay
                            </a>
                            <a href="stock_in.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Stok Giriş">
                                <i class="fas fa-arrow-circle-down"></i>
                            </a>
                            <a href="stock_out.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Stok Çıkış">
                                <i class="fas fa-arrow-circle-up"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info w-100">
                <i class="fas fa-info-circle me-2"></i> Seçilen filtreye uygun ürün bulunamadı.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleCategorySelect() {
    const filterSelect = document.getElementById('filter');
    const categoryContainer = document.getElementById('categorySelectContainer');
    
    if (filterSelect.value === 'category') {
        categoryContainer.style.display = 'block';
    } else {
        categoryContainer.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // DataTables özel ayarları
    if (typeof $.fn.DataTable !== 'undefined') {
        window.dt_stockReportTable_initialized = true;
        $('#stockReportTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/tr.json",
                paginate: {
                    previous: "Önceki",
                    next: "Sonraki"
                },
                search: "Ara:",
                info: "Toplam _TOTAL_ kayıttan _START_ ile _END_ arası gösteriliyor",
                lengthMenu: "Sayfa başına _MENU_ kayıt göster",
                zeroRecords: "Kayıt bulunamadı",
                infoEmpty: "Kayıt bulunamadı",
                infoFiltered: "(_MAX_ kayıt arasından filtrelendi)"
            },
            responsive: true,
            paging: true,
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }
    
    // Görünüm değiştirme (Tablo/Izgara)
    const toggleViewBtn = document.getElementById('toggleViewBtn');
    const tableView = document.querySelector('.table-responsive');
    const gridView = document.getElementById('gridView');
    
    toggleViewBtn.addEventListener('click', function() {
        if (tableView.style.display !== 'none') {
            tableView.style.display = 'none';
            gridView.style.display = 'block';
            toggleViewBtn.innerHTML = '<i class="fas fa-list"></i>';
        } else {
            tableView.style.display = 'block';
            gridView.style.display = 'none';
            toggleViewBtn.innerHTML = '<i class="fas fa-table"></i>';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
