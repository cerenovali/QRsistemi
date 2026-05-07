# QR Kod Okuyucu ve Oluşturucu

**BGT 132 — Yazılım Geliştirme Teknolojileri | Final Projesi**

---

## Proje Amacı
Ürünlere QR kod atayan, kamera ile QR taranabilen, kullanıcıların giriş yapıp ürünlere yorum bırakabildiği, sepete ekleyip kredi kartıyla ödeme yapabildiği tam kapsamlı PHP web uygulaması.

---

## Özellikler

- ✅ Kullanıcı kayıt / giriş / çıkış (bcrypt şifreleme)
- ✅ 30+ ürün, 8 kategori (Gıda, Teknoloji, Kıyafet, Temizlik vb.)
- ✅ Kamera ile gerçek zamanlı QR kod okuma (jsQR)
- ✅ Her ürün için QR kod oluşturma ve indirme
- ✅ Ürün yorumu ve 5 yıldız değerlendirme
- ✅ Sepet (ekle / çıkar / adet güncelle)
- ✅ Ödeme sayfası (kredi kartı + taksit seçeneği)
- ✅ Sipariş geçmişi
- ✅ Admin paneli

---
## Kurulum (İnfinityFree)
### 1. Projeyi yükleme
- Bu repoyu ZIP olarak indirin.
- Dosyaları çıkarın.
- InfinityFree paneline girin.
- Tüm dosyaları htdocs klasörüne yükleyin.

### 2. Veritabanı oluşturma
- Control Panel → MySQL Databases.
- Yeni bir veritabanı oluşturun.
- Size verilen bilgileri not edin:
Host (örn: sqlXXX.epizy.com), Database adı, Kullanıcı adı, Şifre.

### 3. Veritabanını ekleme
- phpMyAdmin’e girin.
- Oluşturduğunuz veritabanını seçin.
- Projedeki .sql dosyasını Import edin.

### 4. Veritabanı bağlantısını ayarlama
- Projede bulunan config.php veya db.php dosyasını açın ve şu bilgileri güncelleyin:
- $host = "sqlXXX.epizy.com";
- $user = "epiz_xxxxxx";
- $password = "şifreniz";
- $database = "epiz_xxxxxx_db";
  
⚠️ Not: localhost kullanmayın, InfinityFree’nin verdiği host adresini yazın.

### 5. Çalıştırma
- Tarayıcıdan sitenizi açın:
- http://siteniz.infinityfreeapp.com

---

## Kurulum (XAMPP)
### 1. Dosyaları İndir ve Yerleştir
- GitHub'dan projeyi ZIP olarak indir veya klonla:
- bashgit clone https://github.com/cerenovali/QRsistemi.git
- Klasörü aşağıdaki konuma taşı:
- C:\xampp\htdocs\QRsistemi\

### 2. Veritabanı Bağlantısını Güncelle
- src/core/Veritabani.php dosyasını aç ve şu satırları XAMPP ayarlarına göre düzenle:
- phpprivate static string $host = 'localhost';
- private static string $db   = 'qrdb';
- private static string $user = 'root';
- private static string $pass = '';   // XAMPP varsayılanı boştur.
  
⚠️ Orijinal dosyada uzak sunucu (InfinityFree) bilgileri yazılıdır, bunları yukarıdakiyle değiştirmezsen bağlantı kurulamaz.


### 3. Veritabanını Oluştur ve İçe Aktar
- XAMPP Control Panel'i aç → Apache ve MySQL'i başlat.
- Tarayıcıda şu adrese git: http://localhost/phpmyadmin
- Sol üstte "Yeni" butonuna tıkla.
- Veritabanı adını qrdb yaz → Oluştur
- Oluşturulan veritabanını seçili hâldeyken üstteki İçe Aktar (Import) sekmesine geç
- Dosya Seç butonuyla projeden data/veritabani.sql dosyasını seç.
- Sayfanın altındaki Git butonuna tıkla.
📌 SQL dosyasının en üstündeki USE if0_41821231_qrdb; satırı sorun çıkarırsa onu da USE qrdb; olarak değiştir ya da içe aktarmadan önce o satırı sil.


### 4. Uygulamayı Çalıştır
- Tarayıcıda şu adrese git:
- http://localhost/QRsistemi/src/pages/index.php

---

## Klasör Yapısı
```
QRsistemi/
├── docs/
│   ├── Gereksinim_Analizi.pdf
│   └── UML_Diyagramlari.pdf
├── src/
│   ├── core/
│   │   ├── Veritabani.php
│   │   ├── TemelModel.php
│   │   └── Oturum.php
│   ├── modules/
│   │   ├── admin/
│   │   │   └── panel.php
│   │   ├── KullaniciModel.php
│   │   ├── UrunModel.php
│   │   ├── YorumModel.php
│   │   ├── SepetModel.php
│   │   └── SiparisModel.php
│   ├── pages/
│   │   ├── index.php
│   │   ├── giris.php
│   │   ├── kayit.php
│   │   ├── cikis.php
│   │   ├── urunler.php
│   │   ├── urun.php
│   │   ├── qr_olustur.php
│   │   ├── qr_oku.php
│   │   ├── sepet.php
│   │   ├── odeme.php
│   │   ├── siparis.php
│   │   ├── siparisler.php
│   │   └── profil.php
│   ├── services/
│   │   ├── api/
│   │   │   └── qr_uret.php
│   │   └── QRKodServisi.php
│   ├── ui/
│   │   ├── header.php
│   │   └── footer.php
│   └── utils/
│       └── yardimcilar.php
├── assets/
│   ├── images/
│   ├── icons/
│   ├── sounds/
│   └── js/
│       ├── bootstrap.bundle.min.js
│       ├── jsQR.js
│       └── qrcode.min.js
├── data/
│   └── veritabani.sql
├── tests/
│   └── test_siniflari.php
├── README.md
└── .gitignore
```

---

## OOP Mimarisi

| İlke | Uygulama |
|------|----------|
| **Inheritance** | `KullaniciModel`, `UrunModel`, `YorumModel`, `SepetModel`, `SiparisModel` → `TemelModel`'den miras |
| **Polymorphism** | `YorumModel::hepsiniGetir()` → `TemelModel::hepsiniGetir()` override |
| **Encapsulation** | `KullaniciModel` ve `UrunModel`'de private alanlar + getter/setter |
| **Abstraction** | `TemelModel` abstract sınıf |
