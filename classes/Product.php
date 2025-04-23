<?php
class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Tüm ürünleri getir
    public function getAllProducts() {
        $this->db->query('
            SELECT u.*, k.kategori_adi 
            FROM urunler u
            LEFT JOIN kategoriler k ON u.kategori_id = k.id
            ORDER BY u.eklenme_tarihi DESC
        ');
        
        return $this->db->resultSet();
    }
    
    // Ürün detaylarını getir
    public function getProductById($id) {
        $this->db->query('
            SELECT u.*, k.kategori_adi 
            FROM urunler u 
            LEFT JOIN kategoriler k ON u.kategori_id = k.id
            WHERE u.id = :id
        ');
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }
    
    // Ürün ekle
    public function addProduct($data) {
        $this->db->query('
            INSERT INTO urunler (urun_kodu, urun_adi, aciklama, kategori_id, fiyat, stok_miktari, kritik_stok_seviyesi, birim, ekleyen_id)
            VALUES (:urun_kodu, :urun_adi, :aciklama, :kategori_id, :fiyat, :stok_miktari, :kritik_stok_seviyesi, :birim, :ekleyen_id)
        ');
        
        // Parametreleri bağlama
        $this->db->bind(':urun_kodu', $data['urun_kodu']);
        $this->db->bind(':urun_adi', $data['urun_adi']);
        $this->db->bind(':aciklama', $data['aciklama']);
        $this->db->bind(':kategori_id', $data['kategori_id']);
        $this->db->bind(':fiyat', $data['fiyat']);
        $this->db->bind(':stok_miktari', $data['stok_miktari']);
        $this->db->bind(':kritik_stok_seviyesi', $data['kritik_stok_seviyesi']);
        $this->db->bind(':birim', $data['birim']);
        $this->db->bind(':ekleyen_id', $data['ekleyen_id']);
        
        // Çalıştır ve sonucu döndür
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Ürün güncelle
    public function updateProduct($data) {
        $this->db->query('
            UPDATE urunler 
            SET urun_kodu = :urun_kodu, urun_adi = :urun_adi, aciklama = :aciklama,
                kategori_id = :kategori_id, fiyat = :fiyat, kritik_stok_seviyesi = :kritik_stok_seviyesi,
                birim = :birim
            WHERE id = :id
        ');
        
        // Parametreleri bağlama
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':urun_kodu', $data['urun_kodu']);
        $this->db->bind(':urun_adi', $data['urun_adi']);
        $this->db->bind(':aciklama', $data['aciklama']);
        $this->db->bind(':kategori_id', $data['kategori_id']);
        $this->db->bind(':fiyat', $data['fiyat']);
        $this->db->bind(':kritik_stok_seviyesi', $data['kritik_stok_seviyesi']);
        $this->db->bind(':birim', $data['birim']);
        
        // Çalıştır ve sonucu döndür
        return $this->db->execute();
    }
    
    // Stok güncelleme (giriş/çıkış)
    public function updateStock($id, $amount, $type) {
        if($type == 'giris') {
            $this->db->query('UPDATE urunler SET stok_miktari = stok_miktari + :amount WHERE id = :id');
        } else {
            $this->db->query('UPDATE urunler SET stok_miktari = stok_miktari - :amount WHERE id = :id');
        }
        
        $this->db->bind(':id', $id);
        $this->db->bind(':amount', $amount);
        
        return $this->db->execute();
    }
    
    // Stok hareketi ekle
    public function addStockMovement($data) {
        $this->db->query('
            INSERT INTO stok_hareketleri (urun_id, kullanici_id, islem_turu, miktar, aciklama)
            VALUES (:urun_id, :kullanici_id, :islem_turu, :miktar, :aciklama)
        ');
        
        $this->db->bind(':urun_id', $data['urun_id']);
        $this->db->bind(':kullanici_id', $data['kullanici_id']);
        $this->db->bind(':islem_turu', $data['islem_turu']);
        $this->db->bind(':miktar', $data['miktar']);
        $this->db->bind(':aciklama', $data['aciklama']);
        
        return $this->db->execute();
    }
    
    // Ürün arama
    public function searchProducts($keyword) {
        $this->db->query('
            SELECT u.*, k.kategori_adi 
            FROM urunler u
            LEFT JOIN kategoriler k ON u.kategori_id = k.id
            WHERE u.urun_kodu LIKE :keyword OR u.urun_adi LIKE :keyword OR k.kategori_adi LIKE :keyword
            ORDER BY u.eklenme_tarihi DESC
        ');
        
        $this->db->bind(':keyword', '%' . $keyword . '%');
        
        return $this->db->resultSet();
    }
    
    // Kategorileri getir
    public function getCategories() {
        $this->db->query('SELECT * FROM kategoriler ORDER BY kategori_adi');
        return $this->db->resultSet();
    }
    
    // Kritik stok seviyesindeki ürünler
    public function getCriticalStockProducts() {
        $this->db->query('
            SELECT u.*, k.kategori_adi 
            FROM urunler u
            LEFT JOIN kategoriler k ON u.kategori_id = k.id
            WHERE u.stok_miktari <= u.kritik_stok_seviyesi
            ORDER BY u.stok_miktari ASC
        ');
        
        return $this->db->resultSet();
    }
    
    // Stok hareketlerini getir
    public function getStockMovements($productId = null) {
        if($productId) {
            $this->db->query('
                SELECT sh.*, u.urun_kodu, u.urun_adi, k.ad_soyad as kullanici
                FROM stok_hareketleri sh
                JOIN urunler u ON sh.urun_id = u.id
                LEFT JOIN kullanicilar k ON sh.kullanici_id = k.id
                WHERE sh.urun_id = :urun_id
                ORDER BY sh.islem_tarihi DESC
            ');
            $this->db->bind(':urun_id', $productId);
        } else {
            $this->db->query('
                SELECT sh.*, u.urun_kodu, u.urun_adi, k.ad_soyad as kullanici
                FROM stok_hareketleri sh
                JOIN urunler u ON sh.urun_id = u.id
                LEFT JOIN kullanicilar k ON sh.kullanici_id = k.id
                ORDER BY sh.islem_tarihi DESC
            ');
        }
        
        return $this->db->resultSet();
    }
}
