<?php
require_once 'config/config.php';

// Giriş yapmış kullanıcı kontrolü
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$debug = '';

// Form gönderildi mi kontrolü
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Basit validasyon
    if(empty($username) || empty($password)) {
        $error = 'Lütfen kullanıcı adı ve şifre giriniz.';
    } else {
        $user = new User();
        
        // Sorun giderme için kullanıcı adını kontrol et
        $userExists = $user->userExists($username);
        if(!$userExists) {
            $error = 'Kullanıcı adı bulunamadı.';
            $debug = 'Girilen kullanıcı adı: ' . $username;
        } else {
            $loggedInUser = $user->login($username, $password);
            
            if($loggedInUser) {
                // Oturum değişkenlerini ayarla
                $_SESSION['user_id'] = $loggedInUser['id'];
                $_SESSION['user_name'] = $loggedInUser['ad_soyad'];
                $_SESSION['user_role'] = $loggedInUser['rol'];
                
                // Yönlendirme
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Kullanıcı adı veya şifre hatalı.';
                $debug = 'Kullanıcı adı doğru, ancak şifre yanlış olabilir.';
            }
        }
    }
}

// Geliştirme modunda hata ayıklama
$showDebug = false; // Geliştirme bittikten sonra false yapın
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - <?= SITE_NAME ?></title>
    <!-- Google Fonts - Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --login-bg-color: linear-gradient(45deg, #5046e5, #8c85ff);
            --card-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--login-bg-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
            overflow-x: hidden;
        }
        
        .login-card {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            padding: 40px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        
        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, #5046e5, #8c85ff, #5046e5);
            background-size: 200% 200%;
            animation: gradientAnimation 3s ease infinite;
        }
        
        @keyframes gradientAnimation {
            0% {background-position: 0% 50%}
            50% {background-position: 100% 50%}
            100% {background-position: 0% 50%}
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }
        
        .logo-container .logo-icon {
            width: 90px;
            height: 90px;
            background: var(--login-bg-color);
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 40px;
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(80, 70, 229, 0.3);
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .form-control {
            height: 55px;
            padding: 12px 20px 12px 45px;
            font-size: 16px;
            border: 2px solid #e1e5eb;
            border-radius: 12px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.7);
        }
        
        .form-control:focus {
            border-color: #5046e5;
            box-shadow: 0 0 0 0.25rem rgba(80, 70, 229, 0.25);
            background-color: white;
        }
        
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #b0b7c3;
            transition: all 0.3s ease;
        }
        
        .form-control:focus + .form-icon {
            color: #5046e5;
        }
        
        .login-btn {
            height: 55px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 15px rgba(80, 70, 229, 0.3);
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(80, 70, 229, 0.4);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
        }
        
        .form-floating > label {
            padding: 1rem 1.25rem 1rem 3rem;
        }
        
        .forgot-link {
            display: inline-block;
            margin-top: 5px;
            color: #5046e5;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .forgot-link:hover {
            color: #3c34b6;
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 25px;
            border: none;
        }
        
        .alert-danger {
            background-color: rgba(255, 68, 113, 0.1);
            color: #ff4471;
        }
        
        @media (max-width: 768px) {
            .login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card animate">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <h1 class="h3 mb-2"><?= SITE_NAME ?></h1>
            <p class="text-muted small mb-0">Stok yönetim sistemine hoş geldiniz</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-3 fa-lg"></i>
                    <div><?= $error ?></div>
                </div>
            </div>
            <?php if($showDebug && $debug): ?>
                <div class="alert alert-warning small">
                    <strong>Hata ayıklama bilgisi:</strong> <?= $debug ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <form action="index.php" method="post">
            <div class="form-group">
                <input type="text" class="form-control" id="username" name="username" placeholder="Kullanıcı adı" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
                <i class="fas fa-user form-icon"></i>
            </div>
            
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Şifre" required>
                <i class="fas fa-lock form-icon"></i>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label text-muted small" for="remember">
                        Beni hatırla
                    </label>
                </div>
                <a href="#" class="forgot-link">Şifremi unuttum</a>
            </div>
            
            <button type="submit" class="btn btn-primary login-btn w-100">
                <i class="fas fa-sign-in-alt me-2"></i> Giriş Yap
            </button>
        </form>
        
        <div class="login-footer">
            <p class="text-muted small mb-0">
                <?= SITE_NAME ?> &copy; <?= date('Y') ?> | Tüm hakları saklıdır
            </p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form alanlarına otomatik odaklanma animasyonu
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            control.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
            
            // Değer varsa başlangıçta sınıf ekle
            if (control.value) {
                control.parentElement.classList.add('focused');
            }
        });
        
        // Sayfa yüklendiğinde giriş kartı animasyonu
        setTimeout(function() {
            document.querySelector('.login-card').classList.add('loaded');
        }, 100);
    });
    </script>
</body>
</html>
