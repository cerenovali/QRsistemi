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

## Kurulum (XAMPP)

### 1. Gereksinimler
- XAMPP (PHP 8.x + MySQL + Apache)

### 2. Dosyaları Yerleştir
```
C:\xampp\htdocs\QRBarkodSistemi\
```

### 3. Veritabanını Kur
1. XAMPP Control Panel → Apache ve MySQL'i başlat
2. Tarayıcıda: `http://localhost/phpmyadmin`
3. Sol üstte **"Yeni"** → Veritabanı adı: `qrbarkod_db` → Oluştur
4. Oluşturulan veritabanını seç → **İçe Aktar (Import)** sekmesi
5. `data/veritabani.sql` dosyasını seç → **Git**

### 4. Uygulamayı Aç
```
http://localhost/QRBarkodSistemi/
```

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
