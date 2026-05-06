<?php
/**
 * src/modules/UrunModel.php
 * Ürün CRUD ve listeleme işlemleri.
 * OOP: TemelModel'den kalıtım, Encapsulation.
 */
require_once __DIR__ . '/../core/TemelModel.php';

class UrunModel extends TemelModel {
    protected string $tablo = 'urunler';

    // Encapsulation
    private string $ad      = '';
    private float  $fiyat   = 0.0;
    private int    $stok    = 0;

    public function setAd(string $v): void    { $this->ad    = trim($v); }
    public function setFiyat(float $v): void  { $this->fiyat = max(0, $v); }
    public function setStok(int $v): void     { $this->stok  = max(0, $v); }
    public function getAd(): string           { return $this->ad; }
    public function getFiyat(): float         { return $this->fiyat; }
    public function getStok(): int            { return $this->stok; }

    /** Tüm ürünleri kategori bilgisiyle getir */
    public function hepsiniGetir(): array {
        return $this->db->query(
            'SELECT u.*, k.ad AS kategori_adi, k.slug AS kategori_slug, k.ikon AS kategori_ikon
             FROM urunler u JOIN kategoriler k ON u.kategori_id = k.id
             ORDER BY u.id DESC'
        )->fetchAll();
    }

    /** ID ile ürün getir (kategori dahil) */
    public function idIleGetir(int $id): ?array {
        $s = $this->db->prepare(
            'SELECT u.*, k.ad AS kategori_adi, k.slug AS kategori_slug, k.ikon AS kategori_ikon
             FROM urunler u JOIN kategoriler k ON u.kategori_id = k.id
             WHERE u.id = ? LIMIT 1'
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** qr_token ile ürün getir */
    public function tokenIleGetir(string $token): ?array {
        $s = $this->db->prepare(
            'SELECT u.*, k.ad AS kategori_adi, k.slug AS kategori_slug
             FROM urunler u JOIN kategoriler k ON u.kategori_id = k.id
             WHERE u.qr_token = ? LIMIT 1'
        );
        $s->execute([$token]);
        return $s->fetch() ?: null;
    }

    /** Kategori slug ile listele */
    public function kategoriIleGetir(string $slug): array {
        $s = $this->db->prepare(
            'SELECT u.*, k.ad AS kategori_adi, k.slug AS kategori_slug
             FROM urunler u JOIN kategoriler k ON u.kategori_id = k.id
             WHERE k.slug = ? ORDER BY u.id DESC'
        );
        $s->execute([$slug]);
        return $s->fetchAll();
    }

    /** Arama */
    public function ara(string $q): array {
        $s = $this->db->prepare(
            'SELECT u.*, k.ad AS kategori_adi FROM urunler u
             JOIN kategoriler k ON u.kategori_id = k.id
             WHERE u.ad LIKE ? OR u.aciklama LIKE ? ORDER BY u.id DESC'
        );
        $like = "%$q%";
        $s->execute([$like, $like]);
        return $s->fetchAll();
    }

    /** Son eklenenler */
    public function sonEklenenler(int $n = 8): array {
        $s = $this->db->prepare(
            'SELECT u.*, k.ad AS kategori_adi, k.slug AS kategori_slug, k.ikon AS kategori_ikon
             FROM urunler u JOIN kategoriler k ON u.kategori_id = k.id
             ORDER BY u.id DESC LIMIT ?'
        );
        $s->bindValue(1, $n, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    /** Ürün ortalama puanı */
    public function ortPuan(int $urunId): float {
        $s = $this->db->prepare('SELECT AVG(puan) FROM yorumlar WHERE urun_id=?');
        $s->execute([$urunId]);
        return round((float)$s->fetchColumn(), 1);
    }

    /** Yorum sayısı */
    public function yorumSayisi(int $urunId): int {
        $s = $this->db->prepare('SELECT COUNT(*) FROM yorumlar WHERE urun_id=?');
        $s->execute([$urunId]);
        return (int)$s->fetchColumn();
    }
}
