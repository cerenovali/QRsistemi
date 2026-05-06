<?php
/**
 * sepet.php — Sepet Sayfası
 */
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/SepetModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
Oturum::girisGerekli();

$sm  = new SepetModel();
$kid = Oturum::getId();

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem  = post('islem');
    $urunId = (int)post('urun_id');

    if ($islem === 'cikar') {
        $sm->cikar($kid, $urunId);
        Oturum::flash('success', 'Ürün sepetten çıkarıldı.');
    } elseif ($islem === 'guncelle') {
        $sm->adetGuncelle($kid, $urunId, (int)post('adet'));
    } elseif ($islem === 'bosalt') {
        $sm->bosalt($kid);
        Oturum::flash('info', 'Sepetiniz boşaltıldı.');
    }
    header('Location: ' . url('sepet.php'));
    exit();
}

$sepetItems = $sm->sepetimGetir($kid);
$toplam     = $sm->toplamTutar($kid);
$sayfaBasligi = 'Sepetim';
include __DIR__ . '/src/ui/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-cart3 me-2 text-primary"></i>Sepetim</h4>
    <?php if (!empty($sepetItems)): ?>
    <form method="POST" onsubmit="return confirm('Sepeti tamamen boşaltmak istiyor musunuz?')">
        <input type="hidden" name="islem" value="bosalt">
        <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-trash me-1"></i>Sepeti Boşalt
        </button>
    </form>
    <?php endif; ?>
</div>

<?php if (empty($sepetItems)): ?>
<div class="text-center py-5">
    <i class="bi bi-cart-x fs-1 text-muted opacity-25 d-block mb-3"></i>
    <p class="text-muted fs-5">Sepetiniz boş.</p>
    <a href="<?= url('urunler.php') ?>" class="btn btn-ana text-white px-4">
        <i class="bi bi-grid me-2"></i>Alışverişe Başla
    </a>
</div>
<?php else: ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ürün</th>
                            <th class="text-center">Birim Fiyat</th>
                            <th class="text-center" style="width:120px;">Adet</th>
                            <th class="text-center">Ara Toplam</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sepetItems as $item): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded-2 p-2 text-center" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-box text-muted"></i>
                                </div>
                                <div>
                                    <a href="<?= url('urun.php?id=' . $item['urun_id']) ?>" class="fw-semibold text-decoration-none text-dark">
                                        <?= temizle($item['ad']) ?>
                                    </a>
                                    <div class="small text-muted">
                                        <?= temizle($item['kategori_adi']) ?>
                                        <?php if (!empty($item['beden'])): ?>
                                            <span class="badge bg-secondary ms-1"><?= temizle($item['beden']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center fw-semibold text-success"><?= fiyat($item['fiyat']) ?></td>
                        <td class="text-center">
                            <!-- Adet güncelleme -->
                            <form method="POST" class="d-flex align-items-center justify-content-center gap-1">
                                <input type="hidden" name="islem" value="guncelle">
                                <input type="hidden" name="urun_id" value="<?= $item['urun_id'] ?>">
                                <button type="submit" name="adet" value="<?= $item['adet'] - 1 ?>"
                                        class="btn btn-sm btn-outline-secondary px-2 py-1">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="fw-bold px-2"><?= $item['adet'] ?></span>
                                <button type="submit" name="adet" value="<?= min($item['adet'] + 1, $item['stok']) ?>"
                                        class="btn btn-sm btn-outline-secondary px-2 py-1"
                                        <?= $item['adet'] >= $item['stok'] ? 'disabled' : '' ?>>
                                    <i class="bi bi-plus"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center fw-bold text-primary"><?= fiyat($item['ara_toplam']) ?></td>
                        <td class="pe-3">
                            <form method="POST">
                                <input type="hidden" name="islem" value="cikar">
                                <input type="hidden" name="urun_id" value="<?= $item['urun_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Kaldır">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sipariş Özeti -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 sticky-top" style="top:80px;">
            <h5 class="fw-bold mb-3">Sipariş Özeti</h5>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Ara Toplam</span>
                <span><?= fiyat($toplam) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Kargo</span>
                <span class="text-success">Ücretsiz</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                <span>Toplam</span>
                <span class="text-primary"><?= fiyat($toplam) ?></span>
            </div>
            <a href="<?= url('odeme.php') ?>" class="btn btn-ana text-white w-100 py-2">
                <i class="bi bi-credit-card me-2"></i>Ödemeye Geç
            </a>
            <a href="<?= url('urunler.php') ?>" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Alışverişe Devam
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/src/ui/footer.php'; ?>
