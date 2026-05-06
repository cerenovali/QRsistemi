<?php
/**
 * src/services/QRKodServisi.php
 * QR kod URL'si oluşturur (harici API kullanılır, Python bağımlılığı yok).
 * Üretim ortamında python-barcode veya PHP kütüphanesiyle değiştirilebilir.
 */
class QRKodServisi {

    /**
     * Ürünün detay sayfası URL'sini QR içeriği olarak döndürür.
     * jsQR kütüphanesi tarayıcıda okur; PHP sadece token üretir.
     */
    public static function tokenUret(string $ad = ''): string {
        $slug  = preg_replace('/[^a-z0-9-]/', '-', strtolower(transliterate($ad)));
        $slug  = trim($slug, '-');
        $hash  = substr(md5(uniqid($ad, true)), 0, 8);
        return $slug . '-' . $hash;
    }

    /**
     * QR içeriğini döndürür (tarayıcı sayfasına URL).
     * XAMPP'te tam URL için sunucu adresine göre oluşturulur.
     */
    public static function qrIcerigi(string $token): string {
        $protokol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protokol . '://' . $host . '/urun.php?token=' . urlencode($token);
    }

    /**
     * Google Charts API ile QR görseli URL'si üretir.
     * İnternetsiz ortam için qrcode.js (ön yüz) kullanılabilir.
     */
    public static function qrGorselUrl(string $token, int $boyut = 200): string {
        $icerik = self::qrIcerigi($token);
        return 'https://api.qrserver.com/v1/create-qr-code/?data='
             . urlencode($icerik)
             . '&size=' . $boyut . 'x' . $boyut
             . '&format=png&ecc=M';
    }
}

/**
 * Türkçe karakterleri ASCII'ye çevirir (slug için)
 */
function transliterate(string $s): string {
    $map = [
        'ç'=>'c','ğ'=>'g','ı'=>'i','ö'=>'o','ş'=>'s','ü'=>'u',
        'Ç'=>'C','Ğ'=>'G','İ'=>'I','Ö'=>'O','Ş'=>'S','Ü'=>'U',
        ' '=>'-',
    ];
    return strtr($s, $map);
}
