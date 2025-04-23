-- Ürün Takip Sistemi Veritabanı

CREATE DATABASE IF NOT EXISTS urun_takip CHARACTER SET utf8 COLLATE utf8_turkish_ci;
USE urun_takip;

-- Kullanıcılar tablosu
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    rol ENUM('admin', 'kullanici') NOT NULL DEFAULT 'kullanici',
    son_giris DATETIME,
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aktif TINYINT(1) DEFAULT 1
);

-- Ürünler tablosu
CREATE TABLE IF NOT EXISTS urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_kodu VARCHAR(50) NOT NULL UNIQUE,
    urun_adi VARCHAR(100) NOT NULL,
    aciklama TEXT,
    kategori_id INT,
    fiyat DECIMAL(10,2) NOT NULL,
    stok_miktari INT NOT NULL DEFAULT 0,
    kritik_stok_seviyesi INT DEFAULT 10,
    birim VARCHAR(20) NOT NULL,
    ekleyen_id INT,
    eklenme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ekleyen_id) REFERENCES kullanicilar(id) ON DELETE SET NULL
);

-- Kategoriler tablosu
CREATE TABLE IF NOT EXISTS kategoriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_adi VARCHAR(50) NOT NULL UNIQUE,
    aciklama TEXT
);

-- Stok hareketleri tablosu
CREATE TABLE IF NOT EXISTS stok_hareketleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_id INT NOT NULL,
    kullanici_id INT,
    islem_turu ENUM('giris', 'cikis') NOT NULL,
    miktar INT NOT NULL,
    aciklama TEXT,
    islem_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE SET NULL
);

-- Örnek veriler - Şifreleri direkt 123456 olarak saklayalım (sonradan hash'lenecek)
INSERT INTO kullanicilar (kullanici_adi, sifre, ad_soyad, email, rol) VALUES
('admin', '123456', 'Admin Kullanıcı', 'admin@example.com', 'admin'),
('kullanici', '123456', 'Test Kullanıcı', 'user@example.com', 'kullanici');

INSERT INTO kategoriler (kategori_adi, aciklama) VALUES
('Elektronik', 'Elektronik ürünler'),
('Giyim', 'Giyim ürünleri'),
('Gıda', 'Gıda ürünleri');

INSERT INTO urunler (urun_kodu, urun_adi, aciklama, kategori_id, fiyat, stok_miktari, kritik_stok_seviyesi, birim, ekleyen_id) VALUES
('ELK001', 'Laptop', '15.6 inç Laptop', 1, 15000.00, 20, 5, 'Adet', 1),
('GYM001', 'T-Shirt', 'Pamuklu T-Shirt', 2, 150.00, 100, 15, 'Adet', 1),
('GDA001', 'Çikolata', 'Sütlü Çikolata', 3, 25.00, 200, 30, 'Adet', 1);
