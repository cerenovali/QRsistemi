<?php
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/core/Veritabani.php';
require_once __DIR__ . '/src/modules/UrunModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
$sayfaBasligi = 'QR Sistemi — Ana Sayfa';
$urunM       = new UrunModel();
$sonUrunler  = $urunM->sonEklenenler(8);
$kategoriler = Veritabani::baglan()->query('SELECT * FROM kategoriler ORDER BY id')->fetchAll();

include __DIR__ . '/src/ui/header.php';
?>

<!-- Hero -->
<div class="rounded-4 text-white p-5 mb-5 text-center"
     style="background:linear-gradient(135deg,#1e3a8a,#7c3aed);">
    <h1 class="display-5 fw-bold mb-3">
        <i class="bi bi-qr-code-scan me-2"></i>QR Sistemi
    </h1>
    <p class="lead mb-4 opacity-90">Ürünleri QR koduyla tara, keşfet, sepete ekle ve yorumla.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= url('urunler.php') ?>" class="btn btn-light btn-lg fw-semibold px-4">
            <i class="bi bi-grid me-2"></i>Ürünleri Gör
        </a>
        <a href="<?= url('qr_olustur.php') ?>" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-qr-code me-2"></i>QR Oluştur
        </a>
        <a href="<?= url('qr_oku.php') ?>" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-camera me-2"></i>QR Tara
        </a>
    </div>
</div>

<!-- Kategoriler -->
<h4 class="fw-bold mb-3"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Kategoriler</h4>
<div class="row g-3 mb-5">
<?php foreach ($kategoriler as $kat): ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-auto">
        <a href="<?= url('urunler.php?kategori=' . urlencode($kat['slug'])) ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;min-width:110px;cursor:pointer;">
                <i class="bi <?= temizle($kat['ikon']) ?> fs-1 text-<?= temizle($kat['renk']) ?> mb-1"></i>
                <p class="mb-0 small fw-semibold"><?= temizle($kat['ad']) ?></p>
            </div>
        </a>
    </div>
<?php endforeach; ?>
</div>

<!-- Son Ürünler -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Son Eklenen Ürünler</h4>
    <a href="<?= url('urunler.php') ?>" class="btn btn-sm btn-outline-primary">Tümünü gör</a>
</div>
<div class="row g-4">
<?php foreach ($sonUrunler as $u):
    $puan = $urunM->ortPuan($u['id']);
?>
    <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="card kart shadow-sm h-100">
            <div class="text-center bg-light" style="height:130px;display:flex;align-items:center;justify-content:center;border-radius:14px 14px 0 0;overflow:hidden;">
                <?php if (!empty($u['resim'])): ?>
                    <img src="<?= temizle($u['resim']) ?>" alt="<?= temizle($u['ad']) ?>"
                         style="max-height:120px;max-width:100%;object-fit:contain;"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <i class="bi <?= temizle($u['kategori_ikon'] ?? 'bi-box') ?> fs-1 text-primary opacity-25"></i>
                <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column p-3">
                <span class="badge bg-primary bg-opacity-10 text-primary small mb-1 align-self-start">
                    <?= temizle($u['kategori_adi']) ?>
                </span>
                <h6 class="fw-bold mb-1"><?= temizle($u['ad']) ?></h6>
                <?php if ($puan > 0): ?>
                    <div class="small mb-1">
                        <?php for($i=1;$i<=5;$i++): ?>
                            <i class="bi bi-star-fill <?= $i<=$puan?'text-warning':'text-muted' ?>"></i>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
                <p class="text-success fw-bold mt-auto mb-2 fs-6"><?= fiyat($u['fiyat']) ?></p>
                <a href="<?= url('urun.php?id=' . $u['id']) ?>" class="btn btn-ana btn-sm w-100">
                    <i class="bi bi-eye me-1"></i>İncele
                </a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php include __DIR__ . '/src/ui/footer.php'; ?>
