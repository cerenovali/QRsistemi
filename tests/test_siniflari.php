<?php
/**
 * tests/test_siniflari.php
 * OOP sınıflarını test eder.
 * Çalıştır: php tests/test_siniflari.php
 */
require_once __DIR__ . '/../src/core/Veritabani.php';
require_once __DIR__ . '/../src/core/TemelModel.php';
require_once __DIR__ . '/../src/modules/KullaniciModel.php';
require_once __DIR__ . '/../src/modules/UrunModel.php';
require_once __DIR__ . '/../src/modules/YorumModel.php';
require_once __DIR__ . '/../src/utils/yardimcilar.php';

$gecti = $kaldi = 0;

function test(string $ad, bool $kosul): void {
    global $gecti, $kaldi;
    if ($kosul) { echo "\033[32m✔ GEÇTİ\033[0m: $ad\n"; $gecti++; }
    else        { echo "\033[31m✘ KALDI\033[0m: $ad\n"; $kaldi++; }
}

echo "\n=== QR Kod Sistemi — Sınıf Testleri ===\n\n";

// Kalıtım testleri
echo "--- OOP: Kalıtım ---\n";
test('KullaniciModel → TemelModel',  (new KullaniciModel()) instanceof TemelModel);
test('UrunModel → TemelModel',       (new UrunModel()) instanceof TemelModel);
test('YorumModel → TemelModel',      (new YorumModel()) instanceof TemelModel);
test('TemelModel abstract sınıf',    (new ReflectionClass('TemelModel'))->isAbstract());

// Encapsulation testleri
echo "\n--- Encapsulation: Getter/Setter ---\n";
$k = new KullaniciModel();
$k->setAdSoyad('Test Kullanıcı');
test('KullaniciModel setAdSoyad/getAdSoyad', $k->getAdSoyad() === 'Test Kullanıcı');
$k->setEmail('TEST@MAIL.COM');
test('KullaniciModel setEmail küçük harfe çevirir', $k->getEmail() === 'test@mail.com');

$u = new UrunModel();
$u->setFiyat(-50.0);
test('UrunModel setFiyat negatifi sıfırlar', $u->getFiyat() === 0.0);
$u->setStok(-3);
test('UrunModel setStok negatifi sıfırlar', $u->getStok() === 0);

// Yardımcı fonksiyonlar
echo "\n--- Yardımcı Fonksiyonlar ---\n";
test('temizle() XSS koruması', temizle('<script>') === '&lt;script&gt;');
test('fiyat() formatı doğru', fiyat(1500.00) === '1.500,00 ₺');

// Polymorphism
echo "\n--- Polymorphism ---\n";
$y = new YorumModel();
$ref = new ReflectionMethod($y, 'hepsiniGetir');
test('YorumModel::hepsiniGetir() override edilmiş', $ref->getDeclaringClass()->getName() === 'YorumModel');

echo "\n=== Sonuç: $gecti geçti, $kaldi kaldı ===\n\n";
exit($kaldi > 0 ? 1 : 0);
