<?php
require_once 'config/config.php';

// Oturumu sonlandır
session_unset();
session_destroy();

// Giriş sayfasına yönlendir
header('Location: index.php');
exit;
