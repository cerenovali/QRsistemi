<?php
/**
 * src/modules/YorumModel.php
 * Yorum CRUD işlemleri.
 * OOP: TemelModel kalıtım + Polymorphism (hepsiniGetir override)
 */
require_once __DIR__ . '/../core/TemelModel.php';

class YorumModel extends TemelModel {
    protected string $tablo = 'yorumlar';

    /**
     * Polymorphism: hepsiniGetir() override edildi.
     * Kullanıcı adı ve ürün adıyla birlikte getirir.
     */
    public function hepsiniGetir(): array {
        return $this->db->query(
            'SELECT y.*, k.ad_soyad, k.kullanici_adi, u.ad AS urun_adi
             FROM yorumlar y
             JOIN kullanicilar k ON y.kullanici_id = k.id
             JOIN urunler u ON y.urun_id = u.id
             ORDER BY y.tarih DESC'
        )->fetchAll();
    }

    /** Ürüne ait yorumları getir */
    public function urunYorumlari(int $urunId): array {
        $s = $this->db->prepare(
            'SELECT y.*, k.ad_soyad, k.kullanici_adi
             FROM yorumlar y JOIN kullanicilar k ON y.kullanici_id = k.id
             WHERE y.urun_id = ? ORDER BY y.tarih DESC'
        );
        $s->execute([$urunId]);
        return $s->fetchAll();
    }

    /** Yorum ekle */
    public function ekle(int $urunId, int $kulId, int $puan, string $metin): array {
        try {
            if (empty(trim($metin)))
                return ['ok' => false, 'mesaj' => 'Yorum boş olamaz.'];
            if ($puan < 1 || $puan > 5)
                return ['ok' => false, 'mesaj' => 'Puan 1-5 arasında olmalı.'];

            // Daha önce yorum yapıldı mı?
            $kontrol = $this->db->prepare('SELECT id FROM yorumlar WHERE urun_id=? AND kullanici_id=?');
            $kontrol->execute([$urunId, $kulId]);
            if ($kontrol->fetch())
                return ['ok' => false, 'mesaj' => 'Bu ürüne zaten yorum yaptınız.'];

            $s = $this->db->prepare(
                'INSERT INTO yorumlar (urun_id, kullanici_id, puan, metin) VALUES (?,?,?,?)'
            );
            $s->execute([$urunId, $kulId, $puan, htmlspecialchars(trim($metin))]);
            return ['ok' => true, 'mesaj' => 'Yorumunuz eklendi.'];
        } catch (PDOException $e) {
            // UNIQUE constraint — zaten yorum var
            if ($e->getCode() === '23000')
                return ['ok' => false, 'mesaj' => 'Bu ürüne zaten yorum yaptınız.'];
            error_log($e->getMessage());
            return ['ok' => false, 'mesaj' => 'Yorum eklenemedi.'];
        }
    }
}
