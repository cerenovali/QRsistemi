<?php
/**
 * src/modules/SiparisModel.php
 * Sipariş oluşturma ve listeleme.
 * OOP: TemelModel kalıtım.
 */
require_once __DIR__ . '/../core/TemelModel.php';
require_once __DIR__ . '/SepetModel.php';

class SiparisModel extends TemelModel {
    protected string $tablo = 'siparisler';

    /**
     * Siparişi oluştur ve sepetle birlikte işler.
     * Gerçek ödeme işlemi yapmaz; kart numarasını saklamaz.
     */
    public function olustur(int $kulId, string $kartSon4, int $taksit, string $durum = 'odendi'): array {
        try {
            $sepetModel = new SepetModel();
            $sepet      = $sepetModel->sepetimGetir($kulId);

            if (empty($sepet))
                return ['ok' => false, 'mesaj' => 'Sepetiniz boş.'];

            $toplam = $sepetModel->toplamTutar($kulId);
            if ($toplam <= 0)
                return ['ok' => false, 'mesaj' => 'Geçersiz tutar.'];

            $this->db->beginTransaction();

            // Sipariş başlığı
            $s = $this->db->prepare(
                'INSERT INTO siparisler (kullanici_id, toplam_tutar, taksit_sayisi, kart_son4, durum)
                 VALUES (?,?,?,?,?)'
            );
            $s->execute([$kulId, $toplam, $taksit, $kartSon4, $durum]);
            $siparisId = $this->db->lastInsertId();

            // Detaylar
            $d = $this->db->prepare(
                'INSERT INTO siparis_detay (siparis_id, urun_id, adet, birim_fiyat) VALUES (?,?,?,?)'
            );
            foreach ($sepet as $kalem) {
                $d->execute([$siparisId, $kalem['urun_id'], $kalem['adet'], $kalem['fiyat']]);
                // Stok düş
                $this->db->prepare('UPDATE urunler SET stok = stok - ? WHERE id=?')
                         ->execute([$kalem['adet'], $kalem['urun_id']]);
            }

            // Sepeti temizle
            $sepetModel->bosalt($kulId);
            $this->db->commit();

            return ['ok' => true, 'siparis_id' => $siparisId];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return ['ok' => false, 'mesaj' => 'Sipariş oluşturulamadı.'];
        }
    }

    /** Kullanıcının siparişlerini getir */
    public function kullanicininSiparisler(int $kulId): array {
        $s = $this->db->prepare(
            'SELECT * FROM siparisler WHERE kullanici_id=? ORDER BY siparis_tarihi DESC'
        );
        $s->execute([$kulId]);
        return $s->fetchAll();
    }

    /** Sipariş detaylarını getir */
    public function detaylariGetir(int $siparisId): array {
        $s = $this->db->prepare(
            'SELECT sd.*, u.ad, u.qr_token FROM siparis_detay sd
             JOIN urunler u ON sd.urun_id = u.id
             WHERE sd.siparis_id = ?'
        );
        $s->execute([$siparisId]);
        return $s->fetchAll();
    }
}
