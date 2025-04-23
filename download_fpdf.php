<?php
// FPDF otomatik indirme ve kurulum betiği
$fpdfUrl = "http://www.fpdf.org/en/dl.php?v=184&f=tgz"; // FPDF 1.84 sürümü
$destination = __DIR__ . "/fpdf_download.tar.gz";
$extractPath = __DIR__ . "/lib";

// Klasörlerin var olduğundan emin ol
if (!file_exists($extractPath)) {
    mkdir($extractPath, 0777, true);
}
if (!file_exists($extractPath . "/fpdf")) {
    mkdir($extractPath . "/fpdf", 0777, true);
}

// Dosyayı indir
echo "FPDF indiriliyor...<br>";
$downloaded = file_put_contents($destination, fopen($fpdfUrl, 'r'));
if (!$downloaded) {
    die("FPDF indirilemedi. Manuel olarak indirin: http://www.fpdf.org/");
}

// Dosyayı çıkart
echo "Dosya çıkartılıyor...<br>";
$phar = new PharData($destination);
$phar->extractTo($extractPath . "/fpdf_temp");

// Dosyaları doğru klasöre taşı
echo "Dosyalar düzenleniyor...<br>";
$sourceDir = $extractPath . "/fpdf_temp/fpdf184";
$targetDir = $extractPath . "/fpdf";

// Dosyaları kopyala
$dir = opendir($sourceDir);
while (($file = readdir($dir)) !== false) {
    if ($file != '.' && $file != '..') {
        copy($sourceDir . '/' . $file, $targetDir . '/' . $file);
    }
}
closedir($dir);

// Geçici dosyaları temizle
unlink($destination);
array_map('unlink', glob($extractPath . "/fpdf_temp/fpdf184/*.*"));
rmdir($extractPath . "/fpdf_temp/fpdf184");
rmdir($extractPath . "/fpdf_temp");

echo "FPDF başarıyla kuruldu!<br>";
echo "Şimdi <a href='kritik_stok_pdf.php'>Kritik Stok PDF</a> sayfasını ziyaret edebilirsiniz.";
