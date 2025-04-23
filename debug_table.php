<?php
require_once 'config/config.php';

$pageTitle = 'Tablo Hata Ayıklama - ' . SITE_NAME;
$activePage = '';

// Oturum kontrolü
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Sadece admin erişimi
if($_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Tablo Hata Ayıklama</h1>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Geri
        </a>
    </div>
    
    <div class="alert alert-info">
        <p><strong>Kullanım:</strong> Bu sayfa DataTables hatası yaşadığınız tabloların sütun sayılarını kontrol etmenize yardımcı olur.</p>
        <p>Aşağıdaki kod örneğini kontrol etmek istediğiniz sayfaya ekleyin:</p>
        <pre><code>&lt;?php 
// Tablo hata ayıklama
if (isset($_GET['debug_table'])) {
    echo '&lt;div class="alert alert-warning"&gt;';
    echo '&lt;h5&gt;Tablo Sütun Sayıları&lt;/h5&gt;';
    echo '&lt;p&gt;THEAD Sütunları: ' . $theadCount . '&lt;/p&gt;';
    echo '&lt;p&gt;TBODY İlk Satır Sütunları: ' . $tbodyFirstRowCount . '&lt;/p&gt;';
    echo '&lt;/div&gt;';
}
?&gt;</code></pre>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Test Tablosu</h5>
        </div>
        <div class="card-body">
            <p>Bu tablo sütun sayıları doğru şekilde eşleşecek şekilde tasarlanmıştır:</p>
            
            <div class="table-responsive">
                <table class="table table-striped" id="debugTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ad</th>
                            <th>Açıklama</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Test 1</td>
                            <td>Bu bir test verisidir</td>
                            <td>
                                <button class="btn btn-sm btn-primary">Düzenle</button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Test 2</td>
                            <td>Bu bir test verisidir</td>
                            <td>
                                <button class="btn btn-sm btn-primary">Düzenle</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <p><strong>THEAD Sütun Sayısı:</strong> <span id="theadCount">0</span></p>
                <p><strong>TBODY İlk Satır Sütun Sayısı:</strong> <span id="tbodyCount">0</span></p>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Sütun sayılarını kontrol et
                    const table = document.getElementById('debugTable');
                    const theadCols = table.querySelectorAll('thead th').length;
                    const tbodyFirstRowCols = table.querySelector('tbody tr')?.querySelectorAll('td').length || 0;
                    
                    document.getElementById('theadCount').textContent = theadCols;
                    document.getElementById('tbodyCount').textContent = tbodyFirstRowCols;
                    
                    // Eşleşme kontrolü
                    if (theadCols === tbodyFirstRowCols) {
                        document.getElementById('theadCount').classList.add('text-success');
                        document.getElementById('tbodyCount').classList.add('text-success');
                    } else {
                        document.getElementById('theadCount').classList.add('text-danger');
                        document.getElementById('tbodyCount').classList.add('text-danger');
                    }
                });
            </script>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Öneriler</h5>
        </div>
        <div class="card-body">
            <h6>DataTables sütun sayısı hatalarını çözmek için:</h6>
            <ol>
                <li>Tablo başlığındaki (thead) sütun sayısı ile tablo gövdesindeki (tbody) satırların hücre sayısının eşleştiğinden emin olun.</li>
                <li>Veri olmadığında gösterilen "Veri yok" mesajının colspan değerinin, thead'deki sütun sayısına eşit olduğundan emin olun.</li>
                <li>Tablonun footer (tfoot) bölümünde colspan değerlerinin doğru olduğundan emin olun.</li>
                <li>Sorunu görmek için tarayıcınızın geliştirici konsolunu açarak DataTables hata mesajlarını kontrol edin.</li>
            </ol>
            
            <div class="alert alert-success mt-3">
                <strong>İpucu:</strong> DataTables başlatırken aşağıdaki kodu kullanabilirsiniz:
                <pre><code>$('.datatable').DataTable({
    language: {
        url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/tr.json"
    },
    responsive: true,
    "columnDefs": [
        { "orderable": false, "targets": -1 }
    ]
});</code></pre>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
