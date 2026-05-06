<?php
/**
 * urunler.php — Ürün Listesi
 */
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/UrunModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
$sayfaBasligi = 'Ürünler';

$urunM = new UrunModel();
$db    = Veritabani::baglan();

$kategoriSlug = get('kategori');
$aramaQ       = get('ara');

if ($aramaQ !== '')            $urunler = $urunM->ara($aramaQ);
elseif ($kategoriSlug !== '')  $urunler = $urunM->kategoriIleGetir($kategoriSlug);
else                           $urunler = $urunM->hepsiniGetir();

$kategoriler = $db->query('SELECT * FROM kategoriler ORDER BY id')->fetchAll();

include __DIR__ . '/src/ui/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-grid me-2 text-primary"></i>
        <?php
        if ($aramaQ)       echo '"' . temizle($aramaQ) . '" için sonuçlar';
        elseif ($kategoriSlug) {
            $kAd = $db->prepare('SELECT ad FROM kategoriler WHERE slug=?');
            $kAd->execute([$kategoriSlug]);
            echo temizle($kAd->fetchColumn() ?: $kategoriSlug);
        } else echo 'Tüm Ürünler';
        ?>
    </h4>
    <span class="text-muted small"><?= count($urunler) ?> ürün</span>
</div>

<!-- Arama & Filtre -->
<div class="card border-0 shadow-sm mb-4 p-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="ara" class="form-control" placeholder="Ürün ara..." value="<?= temizle($aramaQ) ?>">
        </div>
        <div class="col-md-4">
            <select name="kategori" class="form-select">
                <option value="">Tüm Kategoriler</option>
                <?php foreach ($kategoriler as $k): ?>
                    <option value="<?= temizle($k['slug']) ?>" <?= $k['slug'] === $kategoriSlug ? 'selected' : '' ?>>
                        <?= temizle($k['ad']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-ana text-white flex-fill"><i class="bi bi-search me-1"></i>Ara</button>
            <a href="<?= url('urunler.php') ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
        </div>
    </form>
</div>

<!-- Kategori Butonları -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= url('urunler.php') ?>" class="btn btn-sm <?= $kategoriSlug === '' ? 'btn-primary' : 'btn-outline-secondary' ?>">Tümü</a>
    <?php foreach ($kategoriler as $k): ?>
        <a href="<?= url('urunler.php?kategori=' . urlencode($k['slug'])) ?>"
           class="btn btn-sm <?= $k['slug'] === $kategoriSlug ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="bi <?= temizle($k['ikon']) ?> me-1"></i><?= temizle($k['ad']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Ürün Kartları -->
<?php if (empty($urunler)): ?>
    <div class="text-center py-5">
        <i class="bi bi-box-seam fs-1 text-muted opacity-25 mb-3 d-block"></i>
        <p class="text-muted">Ürün bulunamadı.</p>
        <a href="<?= url('urunler.php') ?>" class="btn btn-outline-primary">Tüm ürünleri göster</a>
    </div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($urunler as $u):
        $puan = $urunM->ortPuan($u['id']);
        $yCount = $urunM->yorumSayisi($u['id']);
    ?>
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="card kart shadow-sm h-100">
            <div class="bg-light text-center" style="height:140px;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:14px 14px 0 0;">
                <?php if (!empty($u['resim'])): ?>
                    <img src="<?= temizle($u['resim']) ?>" alt="<?= temizle($u['ad']) ?>"
                         style="max-height:130px;max-width:100%;object-fit:contain;"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                    <i class="bi bi-box-seam fs-1 text-secondary opacity-25" style="display:none;"></i>
                <?php else: ?>
                    <i class="bi bi-box-seam fs-1 text-secondary opacity-25"></i>
                <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column p-3">
                <span class="badge bg-primary bg-opacity-10 text-primary small mb-1 align-self-start">
                    <?= temizle($u['kategori_adi']) ?>
                </span>
                <h6 class="fw-bold mb-1"><?= temizle($u['ad']) ?></h6>
                <?php if ($puan > 0): ?>
                    <div class="small mb-1 d-flex align-items-center gap-1">
                        <?= yildizlar($puan) ?>
                        <span class="text-muted">(<?= $yCount ?>)</span>
                    </div>
                <?php endif; ?>
                <p class="text-muted small mb-2 flex-grow-1" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    <?= temizle($u['aciklama']) ?>
                </p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <span class="fw-bold text-success"><?= fiyat($u['fiyat']) ?></span>
                    <span class="small text-muted"><i class="bi bi-box me-1"></i><?= $u['stok'] ?></span>
                </div>
                <a href="<?= url('urun.php?id=' . $u['id']) ?>" class="btn btn-ana btn-sm mt-2 w-100">
                    <i class="bi bi-eye me-1"></i>İncele
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/src/ui/footer.php'; ?>
