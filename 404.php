<?php
require_once 'config/config.php';

$pageTitle = '404 - Sayfa Bulunamadı';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <h1 class="display-1 text-muted"><i class="fas fa-exclamation-circle"></i></h1>
                        <h2 class="mb-4">Sayfa Bulunamadı</h2>
                        <p class="lead mb-4">Aradığınız sayfa mevcut değil veya başka bir yere taşınmış olabilir.</p>
                        
                        <div class="mt-5">
                            <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php' ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-home me-1"></i> Ana Sayfaya Dön
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted">
                    <p><?= SITE_NAME ?> &copy; <?= date('Y') ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
