<?php
/**
 * src/modules/KullaniciModel.php
 * Kullanıcı kayıt, giriş ve profil işlemleri.
 * OOP: TemelModel'den kalıtım, Encapsulation.
 */
require_once __DIR__ . '/../core/TemelModel.php';

class KullaniciModel extends TemelModel {
    protected string $tablo = 'kullanicilar';

    // Encapsulation: private alanlar
    private string $adSoyad = '';
    private string $email   = '';

    public function setAdSoyad(string $v): void { $this->adSoyad = trim($v); }
    public function setEmail(string $v): void   { $this->email   = strtolower(trim($v)); }
    public function getAdSoyad(): string         { return $this->adSoyad; }
    public function getEmail(): string           { return $this->email; }

    /** Kayıt */
    public function kayitOl(string $ad, string $kulAdi, string $email, string $sifre): array {
        try {
            if (strlen($sifre) < 6)
                return ['ok' => false, 'mesaj' => 'Şifre en az 6 karakter olmalı.'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                return ['ok' => false, 'mesaj' => 'Geçersiz e-posta.'];

            $kontrol = $this->db->prepare('SELECT id FROM kullanicilar WHERE kullanici_adi=? OR email=?');
            $kontrol->execute([$kulAdi, $email]);
            if ($kontrol->fetch())
                return ['ok' => false, 'mesaj' => 'Bu kullanıcı adı veya e-posta zaten kayıtlı.'];

            $s = $this->db->prepare(
                'INSERT INTO kullanicilar (ad_soyad,kullanici_adi,email,sifre) VALUES (?,?,?,?)'
            );
            $s->execute([$ad, $kulAdi, $email, password_hash($sifre, PASSWORD_BCRYPT)]);
            return ['ok' => true, 'mesaj' => 'Kayıt başarılı! Giriş yapabilirsiniz.'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['ok' => false, 'mesaj' => 'Kayıt sırasında hata oluştu.'];
        }
    }

    /** Giriş */
    public function girisYap(string $kulAdi, string $sifre): array {
        try {
            $s = $this->db->prepare('SELECT * FROM kullanicilar WHERE kullanici_adi=? LIMIT 1');
            $s->execute([$kulAdi]);
            $k = $s->fetch();
            if (!$k || !password_verify($sifre, $k['sifre']))
                return ['ok' => false, 'mesaj' => 'Kullanıcı adı veya şifre hatalı.'];

            $this->db->prepare('UPDATE kullanicilar SET son_giris=NOW() WHERE id=?')->execute([$k['id']]);
            return ['ok' => true, 'kullanici' => $k];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['ok' => false, 'mesaj' => 'Giriş sırasında hata oluştu.'];
        }
    }
}
