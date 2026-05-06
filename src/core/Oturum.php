<?php
/**
 * src/core/Oturum.php
 * Kullanıcı oturumunu yönetir.
 */
class Oturum {

    public static function baslat(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('QRBARKOD');
            session_start();
        }
    }

    public static function girisYapildiMi(): bool {
        self::baslat();
        return !empty($_SESSION['kullanici_id']);
    }

    public static function adminMi(): bool {
        return self::girisYapildiMi() && ($_SESSION['rol'] ?? '') === 'admin';
    }

    public static function kullaniciyiKaydet(array $k): void {
        self::baslat();
        $_SESSION['kullanici_id']  = $k['id'];
        $_SESSION['kullanici_adi'] = $k['kullanici_adi'];
        $_SESSION['ad_soyad']      = $k['ad_soyad'];
        $_SESSION['rol']           = $k['rol'];
    }

    public static function getId(): ?int {
        return $_SESSION['kullanici_id'] ?? null;
    }

    public static function getAd(): string {
        return $_SESSION['ad_soyad'] ?? '';
    }

    public static function getKullaniciAdi(): string {
        return $_SESSION['kullanici_adi'] ?? '';
    }

    public static function cikisYap(): void {
        self::baslat();
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
    }

    public static function flash(string $tur, string $mesaj): void {
        self::baslat();
        $_SESSION['flash'][] = ['tur' => $tur, 'mesaj' => $mesaj];
    }

    public static function flashlariAl(): array {
        self::baslat();
        $f = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $f;
    }

    /** Giriş zorunlu sayfalar için */
    public static function girisGerekli(): void {
        if (!self::girisYapildiMi()) {
            header('Location: /giris.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }
}
