# Ürün Takip Sistemi

Bu proje, PHP ve MySQL kullanarak geliştirilen bir ürün ve stok takip uygulamasıdır.

## Özellikler

- Kullanıcı girişi ve yetkilendirme (Admin/Kullanıcı rolleri)
- Ürün yönetimi (Ekleme, düzenleme, listeleme)
- Stok giriş/çıkış işlemleri
- Stok raporları ve istatistikler
- Kategorilere göre ürün filtreleme
- Kritik stok seviyesi uyarıları
- Stok hareket geçmişi

## Kurulum

1. Proje dosyalarını web sunucunuzun ilgili dizinine yükleyin.
2. `database` klasöründeki `db.sql` dosyasını MySQL veritabanınızda çalıştırın.
3. `config/config.php` dosyasını veritabanı bağlantı bilgilerinize göre düzenleyin.

## Oturum Açma Bilgileri

**Admin Kullanıcı**
- Kullanıcı adı: Bilal
- Şifre: 123456

**Normal Kullanıcı**
- Kullanıcı adı: kullanici
- Şifre: 123456

**Not:** İlk girişten sonra şifreler otomatik olarak hash'lenir. Eğer giriş sorunları yaşıyorsanız, veritabanındaki şifreleri sıfırlamak için aşağıdaki SQL komutunu kullanabilirsiniz:
```sql
UPDATE kullanicilar SET sifre = '123456' WHERE kullanici_adi IN ('Bilal', 'kullanici');
```

## Teknolojik Altyapı

- PHP 7.4+
- MySQL 5.7+
- PDO (PHP Data Objects)
- Bootstrap 5
- Font Awesome 6
- DataTables
- jQuery

## Güvenlik Özellikleri

- Parola hashleme (password_hash)
- PDO prepared statements ile SQL injection koruması
- XSS koruması için HTML escaping
- Oturum güvenliği
- Erişim kontrolü

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.
