<?php
// Oturum kontrolü
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user = new User();
$currentUser = $user->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? SITE_NAME ?></title>
    <!-- Google Fonts - Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- İkon hizalama düzeltmesi için özel CSS -->
    <style>
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav .nav-item {
            margin-bottom: 5px;
        }
        
        /* Mobil görünüm için ek stil */
        @media (max-width: 992px) {
            .sidebar .nav-link {
                padding: 0.75rem 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <h3>
                    <i class="fas fa-boxes"></i> <?= SITE_NAME ?>
                </h3>
                <button class="btn btn-icon d-lg-none" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage == 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Ana Sayfa</span>
                        </a>
                    </li>
                    
                    <?php if($_SESSION['user_role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage == 'products' ? 'active' : '' ?>" href="products.php">
                                <i class="fas fa-boxes"></i>
                                <span>Ürünler</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage == 'add_product' ? 'active' : '' ?>" href="add_product.php">
                                <i class="fas fa-plus-circle"></i>
                                <span>Ürün Ekle</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage == 'stock_in' ? 'active' : '' ?>" href="stock_in.php">
                            <i class="fas fa-arrow-circle-down"></i>
                            <span>Ürün Giriş</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage == 'stock_out' ? 'active' : '' ?>" href="stock_out.php">
                            <i class="fas fa-arrow-circle-up"></i>
                            <span>Ürün Çıkış</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage == 'stock_report' ? 'active' : '' ?>" href="stock_report.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Stok Raporu</span>
                        </a>
                    </li>
                    <li class="nav-item mt-auto">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="main">
            <nav class="navbar">
                <div class="container-fluid px-0">
                    <button id="sidebarToggle" class="btn btn-icon">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex align-items-center">
                        <div class="me-3 d-none d-md-block">
                            <div class="text-end">
                                <div class="small text-muted">Hoş geldiniz</div>
                                <div class="fw-bold"><?= htmlspecialchars($currentUser['ad_soyad']) ?></div>
                            </div>
                        </div>
                        <div class="dropdown">
                            <a class="btn btn-icon" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px">
                                    <i class="fas fa-user-circle text-primary"></i>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user me-2"></i> Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i> Çıkış
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <div class="container-fluid px-0 py-4">
