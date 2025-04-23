<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'urun_takip');
define('SITE_URL', 'http://localhost/urun_takip/');
define('SITE_NAME', 'BilalTM Ürün Takip Sistemi');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Otomatik yükleme fonksiyonu
function autoload($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

spl_autoload_register('autoload');
