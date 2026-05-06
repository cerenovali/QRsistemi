<?php
/**
 * siparis.php — Sipariş Onay Sayfası
 */
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/SiparisModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
Oturum::girisGerekli();

$siparisId = (int)get('id');
$sm  = new SiparisModel();
$sip = $sm->idIleGetir($siparisId);

if (!$sip || $sip['kullanici_id'] !== Oturum::getId()) {
    header('Location: ' . url('siparisler.php'));
    exit();
}

$detaylar = $sm->detaylariGetir($siparisId);
$sayfaBasligi = 'Sipariş Onayı #' . $siparisId;
include __DIR__ . '/src/ui/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-7">

<!-- Başarı Mesajı -->
<div class="text-center py-4 mb-4">
    <div class="display-1 mb-3 text-success"><i class="bi bi-check-circle-fill"></i></div>
    <h3 class="fw-bold text-success">Siparişiniz Alındı!</h3>
    <p class="text-muted">Sipariş No: <strong>#<?= $siparisId ?></strong></p>
</div>

<!-- Sipariş Detayları -->
<div class="card border-0 shadow-sm p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-bag-check me-2 text-primary"></i>Sipariş Detayları</h5>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Ürün</th><th class="text-center">Adet</th><th class="text-end">Tutar</th></tr></thead>
        <tbody>
        <?php foreach ($detaylar as $d): ?>
        <tr>
            <td>
                <a href="<?= url('urun.php?id=' . $d['urun_id']) ?>" class="text-decoration-none">
                    <?= temizle($d['ad']) ?>
                </a>
            </td>
            <td class="text-center"><?= $d['adet'] ?></td>
            <td class="text-end"><?= fiyat($d['adet'] * $d['birim_fiyat']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold border-top">
                <td colspan="2">Toplam</td>
                <td class="text-end text-primary"><?= fiyat($sip['toplam_tutar']) ?></td>
            </tr>
            <?php if ($sip['taksit_sayisi'] > 1): ?>
            <tr class="text-muted small">
                <td colspan="2"><?= $sip['taksit_sayisi'] ?> taksit / aylık</td>
                <td class="text-end"><?= fiyat($sip['toplam_tutar'] / $sip['taksit_sayisi']) ?></td>
            </tr>
            <?php endif; ?>
        </tfoot>
    </table>
</div>

<!-- Kart Bilgisi -->
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex gap-3 align-items-center">
        <?php if ($sip['durum'] === 'bekliyor'): ?>
            <i class="bi bi-cash-coin fs-3 text-success"></i>
            <div>
                <p class="mb-0 fw-semibold">Kapıda Ödeme</p>
                <p class="mb-0 text-muted small">
                    Teslimat sırasında nakit veya kartla ödeme yapabilirsiniz
                    · <?= tarih($sip['siparis_tarihi']) ?>
                </p>
            </div>
            <span class="ms-auto badge bg-warning text-dark">Kapıda Ödeme Bekliyor</span>
        <?php else: ?>
            <i class="bi bi-credit-card fs-3 text-muted"></i>
            <div>
                <p class="mb-0 fw-semibold">•••• •••• •••• <?= temizle($sip['kart_son4']) ?></p>
                <p class="mb-0 text-muted small">
                    <?= $sip['taksit_sayisi'] === 1 ? 'Tek çekim' : $sip['taksit_sayisi'] . ' taksit' ?>
                    · <?= tarih($sip['siparis_tarihi']) ?>
                </p>
            </div>
            <span class="ms-auto badge bg-success">Ödendi</span>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex gap-3">
    <a href="<?= url() ?>" class="btn btn-ana text-white flex-fill">
        <i class="bi bi-house me-2"></i>Ana Sayfa
    </a>
    <a href="<?= url('siparisler.php') ?>" class="btn btn-outline-primary flex-fill">
        <i class="bi bi-list-check me-2"></i>Siparişlerim
    </a>
</div>

</div>
</div>
<?php include __DIR__ . '/src/ui/footer.php'; ?>
