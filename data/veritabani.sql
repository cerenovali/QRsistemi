-- QR Kod Sistemi - Veritabani
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection=utf8mb4;

USE if0_41821231_qrdb;

CREATE TABLE IF NOT EXISTS kullanicilar (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad      VARCHAR(100)  NOT NULL,
    kullanici_adi VARCHAR(50)   NOT NULL UNIQUE,
    email         VARCHAR(100)  NOT NULL UNIQUE,
    sifre         VARCHAR(255)  NOT NULL,
    rol           ENUM('uye','admin') DEFAULT 'uye',
    kayit_tarihi  DATETIME      DEFAULT CURRENT_TIMESTAMP,
    son_giris     DATETIME      NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kategoriler (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    ad    VARCHAR(100) NOT NULL,
    slug  VARCHAR(100) NOT NULL UNIQUE,
    ikon  VARCHAR(50)  DEFAULT 'bi-box',
    renk  VARCHAR(30)  DEFAULT 'primary'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS urunler (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ad          VARCHAR(200)  NOT NULL,
    aciklama    TEXT          NULL,
    kategori_id INT           NOT NULL,
    fiyat       DECIMAL(10,2) DEFAULT 0.00,
    stok        INT           DEFAULT 100,
    qr_token    VARCHAR(64)   NOT NULL UNIQUE,
    resim       VARCHAR(500)  NULL,
    olusturma   DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategoriler(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS yorumlar (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    urun_id      INT     NOT NULL,
    kullanici_id INT     NOT NULL,
    puan         TINYINT NOT NULL,
    metin        TEXT    NOT NULL,
    tarih        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (urun_id)      REFERENCES urunler(id)      ON DELETE CASCADE,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY tek_yorum (urun_id, kullanici_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sepet (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id  INT NOT NULL,
    urun_id       INT NOT NULL,
    adet          INT NOT NULL DEFAULT 1,
    ekleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_id)      REFERENCES urunler(id)      ON DELETE CASCADE,
    UNIQUE KEY tek_sepet (kullanici_id, urun_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS siparisler (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id   INT           NOT NULL,
    toplam_tutar   DECIMAL(10,2) NOT NULL,
    taksit_sayisi  INT           DEFAULT 1,
    kart_son4      CHAR(4)       NOT NULL,
    durum          ENUM('bekliyor','odendi','iptal') DEFAULT 'odendi',
    siparis_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS siparis_detay (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    siparis_id  INT           NOT NULL,
    urun_id     INT           NOT NULL,
    adet        INT           NOT NULL,
    birim_fiyat DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (siparis_id) REFERENCES siparisler(id) ON DELETE CASCADE,
    FOREIGN KEY (urun_id)    REFERENCES urunler(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO kategoriler (ad, slug, ikon, renk) VALUES
('Gida & Icecek',  'gida',      'bi-cup-straw',   'warning'),
('Teknoloji',      'teknoloji', 'bi-laptop',      'primary'),
('Kiyafet',        'kiyafet',   'bi-bag',         'success'),
('Temizlik',       'temizlik',  'bi-droplet',     'info'),
('Kirtasiye',      'kirtasiye', 'bi-pencil',      'secondary'),
('Spor & Saglik',  'spor',      'bi-heart-pulse', 'danger'),
('Ev & Mutfak',    'ev',        'bi-house',       'dark'),
('Kitap & Dergi',  'kitap',     'bi-book',        'primary');

INSERT INTO urunler (ad, aciklama, kategori_id, fiyat, stok, qr_token, resim) VALUES
('Pinar Su 500ml', 'Pinar dogal kaynak suyu 500ml sise.', 1, 4.50, 999, 'pinar-su-500ml',
 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=300&h=300&fit=crop&auto=format'),
('Dogal Kaynak Suyu 500ml', 'Soguk dag kaynagindan dogal mineralli su.', 1, 3.50, 500, 'su-500ml-001',
 'https://images.unsplash.com/photo-1616118132534-381148898bb4?w=300&h=300&fit=crop&auto=format'),
('Sutlu Cikolata 80g', 'Yumusak sutlu cikolata, yuzde 35 kakao.', 1, 7.90, 300, 'cikolata-sutlu-002',
 'https://images.unsplash.com/photo-1511381939415-e44015466834?w=300&h=300&fit=crop&auto=format'),
('Elma 1kg', 'Taze yerli elma.', 1, 12.50, 200, 'elma-1kg-003',
 'https://images.unsplash.com/photo-1568702846914-96b305d2aaeb?w=300&h=300&fit=crop&auto=format'),
('Tam Bugday Ekmek', '500g tam bugday unu ile pistirilmis.', 1, 14.00, 150, 'ekmek-bugday-004',
 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=300&h=300&fit=crop&auto=format'),
('Turk Kahvesi 100g', 'Orta kavrulmus geleneksel Turk kahvesi.', 1, 38.00, 180, 'kahve-turk-006',
 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=300&h=300&fit=crop&auto=format'),
('Organik Bal 250g', 'Dag ciceklerinden toplanan ham organik bal.', 1, 85.00, 80, 'bal-organik-007',
 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=300&h=300&fit=crop&auto=format'),
('Rize Cayi 500g', 'Karadeniz Rize cayi.', 1, 55.00, 200, 'cay-rize-008',
 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=300&h=300&fit=crop&auto=format'),
('USB-C Sarj Kablosu 1m', '5A hizli sarj destekli orgulu kablo.', 2, 89.00, 100, 'kablo-usbc-009',
 'https://images.unsplash.com/photo-1610792516820-5af31de87679?w=300&h=300&fit=crop&auto=format'),
('Kablosuz Mouse', 'Sessiz tiklama, 2.4GHz, 12 ay pil.', 2, 179.00, 60, 'mouse-kablosuz-010',
 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=300&h=300&fit=crop&auto=format'),
('Powerbank 10000mAh', 'Ince tasarim, cift USB cikis.', 2, 249.00, 45, 'powerbank-10k-011',
 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=300&h=300&fit=crop&auto=format'),
('Bluetooth Kulaklik', 'Kulak ici, 6 saat muzik, mikrofon.', 2, 199.00, 70, 'kulaklik-bt-012',
 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&h=300&fit=crop&auto=format'),
('Beyaz Pamuklu T-Shirt', 'Yuzde 100 pamuk, unisex, S-XL.', 3, 89.00, 200, 'tshirt-beyaz-013',
 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=300&h=300&fit=crop&auto=format'),
('Mavi Denim Sort', 'Slim fit, yuksek bel.', 3, 149.00, 120, 'sort-denim-014',
 'https://images.unsplash.com/photo-1591195853828-11db59a44f43?w=300&h=300&fit=crop&auto=format'),
('Spor Corap 5li Paket', 'Antibakteriyal, ter emici.', 3, 69.00, 250, 'corap-spor-015',
 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?w=300&h=300&fit=crop&auto=format'),
('Polar Sweatshirt', 'Kapusonlu, yuzde 80 pamuk, kislik.', 3, 189.00, 90, 'sweatshirt-polar-016',
 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=300&h=300&fit=crop&auto=format'),
('Sivi El Sabunu 500ml', 'Nemlendiricili, lavanta kokulu.', 4, 22.00, 300, 'sabun-el-017',
 'https://images.unsplash.com/photo-1584305574647-0cc949a2bb9f?w=300&h=300&fit=crop&auto=format'),
('Dis Macunu 75ml', 'Fluorlu beyazlatici, nane aromali.', 4, 25.00, 400, 'macun-dis-019',
 'https://images.unsplash.com/photo-1607613009820-a29f7bb81c04?w=300&h=300&fit=crop&auto=format'),
('Tukenmez Kalem 10lu', 'Mavi murekkep, 0.7mm uc.', 5, 18.00, 500, 'kalem-tukenmez-020',
 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?w=300&h=300&fit=crop&auto=format'),
('Spiralli Defter A5', '120 sayfa, kareli, dayanikli.', 5, 32.00, 200, 'defter-spiralli-022',
 'https://images.unsplash.com/photo-1531346878377-a5be20888e57?w=300&h=300&fit=crop&auto=format'),
('Yoga Mati', '6mm kalinlik, kaymaz yuzey.', 6, 159.00, 60, 'mat-yoga-024',
 'https://images.unsplash.com/photo-1599901860904-17e6ed7083a0?w=300&h=300&fit=crop&auto=format'),
('Su Mataras 750ml', 'BPA-free, sizdirmaz, spor tasarimi.', 6, 79.00, 120, 'matara-spor-025',
 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=300&h=300&fit=crop&auto=format'),
('Cam Saklama Kabi Seti', '3 farkli boyut, hava gecirmez.', 7, 149.00, 70, 'kap-cam-026',
 'https://images.unsplash.com/photo-1466637574441-749b8f19452f?w=300&h=300&fit=crop&auto=format'),
('AA Pil 8li Paket', 'Uzun omurlu alkalin AA pil.', 7, 55.00, 300, 'pil-aa-028',
 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?w=300&h=300&fit=crop&auto=format'),
('Python ile Programlama', 'Sifirdan ileri seviyeye Python, 400 sayfa.', 8, 195.00, 50, 'kitap-python-029',
 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=300&h=300&fit=crop&auto=format'),
('Turkce Sozluk', 'Guncel TDK sozlugu, ciltli baski.', 8, 85.00, 80, 'sozluk-turkce-030',
 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=300&h=300&fit=crop&auto=format');

INSERT INTO kullanicilar (ad_soyad, kullanici_adi, email, sifre, rol) VALUES
('Sistem Yoneticisi', 'admin', 'admin@qrsistemi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Ali Yilmaz',        'ali',   'ali@test.com',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'uye'),
('Ayse Kaya',         'ayse',  'ayse@test.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'uye');

INSERT INTO yorumlar (urun_id, kullanici_id, puan, metin) VALUES
(1, 2, 5, 'Pinar suyu her zaman taze geliyor, cok memnunum!'),
(1, 3, 4, 'Fiyat performans cok iyi, tavsiye ederim.'),
(3, 2, 5, 'Sutlu cikolatay cok sevdim, kaliteli urun.'),
(9, 3, 4, 'USB-C kablo sagalam, hizli sarj ediyor.');
