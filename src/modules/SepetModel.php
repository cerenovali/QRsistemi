<?php
/**
 * src/modules/SepetModel.php
 * Sepet işlemlerini yönetir.
 * OOP: TemelModel kalıtım.
 */
require_once __DIR__ . '/../core/TemelModel.php';

class SepetModel extends TemelModel {
    protected string $tablo = 'sepet';

    /** Kullanıcının sepet içeriğini getir */
    public function sepetimGetir(int $kulId): array {
        $s = $this->db->prepare(
            'SELECT s.*, u.ad, u.fiyat, u.stok, u.qr_token,
                    k.ad AS kategori_adi,
                    s.beden,
                    (s.adet * u.fiyat) AS ara_toplam
             FROM sepet s
             JOIN urunler u ON s.urun_id = u.id
             JOIN kategoriler k ON u.kategori_id = k.id
             WHERE s.kullanici_id = ?
             ORDER BY s.ekleme_tarihi DESC'
        );
        $s->execute([$kulId]);
        return $s->fetchAll();
    }

    /** Sepete ürün ekle veya adedi artır */
    public function ekle(int $kulId, int $urunId, int $adet = 1, string $beden = ''): array {
        try {
            // Stok kontrolü
            $stokS = $this->db->prepare('SELECT stok FROM urunler WHERE id=?');
            $stokS->execute([$urunId]);
            $stok = (int)$stokS->fetchColumn();
            if ($stok < 1)
                return ['ok' => false, 'mesaj' => 'Bu ürün stokta yok.'];

            // Zaten sepette var mı?
            $mevcut = $this->db->prepare('SELECT id, adet FROM sepet WHERE kullanici_id=? AND urun_id=? AND beden=?');
            $mevcut->execute([$kulId, $urunId, $beden]);
            $kayit = $mevcut->fetch();

            if ($kayit) {
                $yeniAdet = $kayit['adet'] + $adet;
                if ($yeniAdet > $stok)
                    return ['ok' => false, 'mesaj' => 'Stok yetersiz.'];
                $this->db->prepare('UPDATE sepet SET adet=? WHERE id=?')
                         ->execute([$yeniAdet, $kayit['id']]);
            } else {
                $this->db->prepare('INSERT INTO sepet (kullanici_id,urun_id,adet,beden) VALUES (?,?,?,?)')
                         ->execute([$kulId, $urunId, $adet, $beden]);
            }
            return ['ok' => true, 'mesaj' => 'Sepete eklendi.'];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return ['ok' => false, 'mesaj' => 'İşlem başarısız.'];
        }
    }

    /** Adet güncelle (0 ise sil) */
    public function adetGuncelle(int $kulId, int $urunId, int $adet): bool {
        if ($adet <= 0) return $this->cikar($kulId, $urunId);
        $s = $this->db->prepare('UPDATE sepet SET adet=? WHERE kullanici_id=? AND urun_id=?');
        return $s->execute([$adet, $kulId, $urunId]);
    }

    /** Sepetten çıkar */
    public function cikar(int $kulId, int $urunId): bool {
        $s = $this->db->prepare('DELETE FROM sepet WHERE kullanici_id=? AND urun_id=?');
        return $s->execute([$kulId, $urunId]);
    }

    /** Sepeti boşalt */
    public function bosalt(int $kulId): bool {
        return $this->db->prepare('DELETE FROM sepet WHERE kullanici_id=?')->execute([$kulId]);
    }

    /** Toplam tutar */
    public function toplamTutar(int $kulId): float {
        $s = $this->db->prepare(
            'SELECT SUM(s.adet * u.fiyat) FROM sepet s JOIN urunler u ON s.urun_id=u.id WHERE s.kullanici_id=?'
        );
        $s->execute([$kulId]);
        return (float)$s->fetchColumn();
    }

    /** Sepet ürün sayısı (badge için) */
    public function urunSayisi(int $kulId): int {
        $s = $this->db->prepare('SELECT SUM(adet) FROM sepet WHERE kullanici_id=?');
        $s->execute([$kulId]);
        return (int)$s->fetchColumn();
    }
}
