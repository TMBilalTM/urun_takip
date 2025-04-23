<?php
require_once 'config/config.php';

$pageTitle = 'Profil - ' . SITE_NAME;
$activePage = '';

$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

$success = '';
$error = '';

// Form gönderildi mi kontrolü
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Şifre değişikliği işlemleri
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Basit doğrulama
    if(empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Tüm alanların doldurulması zorunludur.';
    } elseif($newPassword !== $confirmPassword) {
        $error = 'Yeni şifre ve tekrarı eşleşmiyor.';
    } elseif(strlen($newPassword) < 6) {
        $error = 'Yeni şifre en az 6 karakter olmalıdır.';
    } else {
        // Mevcut şifreyi kontrol et ve şifreyi güncelle
        if($user->updatePassword($_SESSION['user_id'], $currentPassword, $newPassword)) {
            $success = 'Şifreniz başarıyla güncellendi.';
        } else {
            $error = 'Mevcut şifre yanlış veya şifre değiştirme sırasında bir hata oluştu.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Profil Bilgileri</h1>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Geri
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
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-1"></i> Kullanıcı Bilgileri
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4 text-center">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px">
                            <i class="fas fa-user-circle text-primary" style="font-size: 64px"></i>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($userData['ad_soyad']) ?></h4>
                        <div class="badge bg-<?= $userData['rol'] == 'admin' ? 'danger' : 'info' ?>">
                            <?= $userData['rol'] == 'admin' ? 'Yönetici' : 'Kullanıcı' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($userData['kullanici_adi']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">E-posta Adresi</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($userData['email']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Son Giriş</label>
                        <input type="text" class="form-control" value="<?= $userData['son_giris'] ? date('d.m.Y H:i', strtotime($userData['son_giris'])) : '-' ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kayıt Tarihi</label>
                        <input type="text" class="form-control" value="<?= date('d.m.Y H:i', strtotime($userData['kayit_tarihi'])) ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-1"></i> Şifre Değiştir
                    </h5>
                </div>
                <div class="card-body">
                    <form action="profile.php" method="post">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Şifreniz en az 6 karakter uzunluğunda olmalıdır.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Şifremi Değiştir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
