# QR Sistemi

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
QRSistemi/
├── docs/                        # Gereksinim Analizi, UML Diyagramları
├── src/
│   ├── core/
│   │   ├── Veritabani.php       # Singleton DB bağlantısı
│   │   ├── TemelModel.php       # Abstract temel model (OOP)
│   │   └── Oturum.php           # Session yönetimi
│   ├── modules/
│   │   ├── KullaniciModel.php   # Kalıtım: TemelModel → Encapsulation
│   │   ├── UrunModel.php        # Kalıtım: TemelModel → Encapsulation
│   │   ├── YorumModel.php       # Kalıtım + Polymorphism (override)
│   │   ├── SepetModel.php       # Sepet işlemleri
│   │   └── SiparisModel.php     # Sipariş & ödeme işlemleri
│   ├── services/
│   │   └── QRKodServisi.php     # QR token ve URL üretimi
│   ├── ui/
│   │   ├── header.php           # Ortak navbar şablonu
│   │   └── footer.php           # Ortak alt şablon
│   └── utils/
│       └── yardimcilar.php      # Yardımcı fonksiyonlar
├── assets/
│   ├── images/
│   └── generated/               # Üretilen QR görselleri
├── data/
│   └── veritabani.sql           # DB kurulum dosyası
├── tests/
│   └── test_siniflari.php
├── index.php                    # Ana sayfa
├── giris.php                    # Giriş
├── kayit.php                    # Kayıt
├── cikis.php                    # Çıkış
├── urunler.php                  # Ürün listesi
├── urun.php                     # Ürün detay + QR + Yorum + Sepet
├── qr_oku.php                   # Kamera QR tarayıcı
├── sepet.php                    # Sepet
├── odeme.php                    # Ödeme (kredi kartı + taksit)
├── siparis.php                  # Sipariş onayı
├── siparisler.php               # Sipariş geçmişi
├── profil.php                   # Profil
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
