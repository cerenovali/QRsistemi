<?php
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/SiparisModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';
Oturum::baslat();
Oturum::girisGerekli();
$sm = new SiparisModel();
$tumSiparisler = $sm->kullanicininSiparisler(Oturum::getId());
$siparisler = array_slice($tumSiparisler, 0, 2);
$sayfaBasligi = 'Siparişlerim';
include __DIR__ . '/src/ui/header.php';
?>
<h4 class="fw-bold mb-4"><i class="bi bi-list-check me-2 text-primary"></i>Siparişlerim</h4>
<?php if (empty($siparisler)): ?>
<div class="text-center py-5">
    <i class="bi bi-bag-x fs-1 text-muted opacity-25 d-block mb-3"></i>
    <p class="text-muted">Henüz siparişiniz bulunmuyor.</p>
    <a href="<?= url('urunler.php') ?>" class="btn btn-ana text-white px-4"><i class="bi bi-grid me-2"></i>Alışverişe Başla</a>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
<table class="table align-middle mb-0">
    <thead class="table-light"><tr><th class="ps-4">Sipariş No</th><th>Tarih</th><th>Taksit</th><th>Tutar</th><th>Durum</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($siparisler as $s): ?>
    <tr>
        <td class="ps-4 fw-bold">#<?= $s['id'] ?></td>
        <td class="text-muted small"><?= tarih($s['siparis_tarihi']) ?></td>
        <td><?= $s['taksit_sayisi'] === 1 ? 'Tek Çekim' : $s['taksit_sayisi'] . ' Taksit' ?></td>
        <td class="fw-bold text-primary"><?= fiyat($s['toplam_tutar']) ?></td>
        <td><?= $s['durum']==='bekliyor' ? '<span class="badge bg-warning text-dark">Kapıda Ödeme</span>' : '<span class="badge bg-success">Ödendi</span>' ?></td>
        <td><a href="<?= url('siparis.php?id=' . $s['id']) ?>" class="btn btn-sm btn-outline-primary">Detay</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/src/ui/footer.php'; ?>
