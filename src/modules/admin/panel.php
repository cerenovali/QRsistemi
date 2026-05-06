<?php
require_once __DIR__ . '/../src/core/Oturum.php';
require_once __DIR__ . '/../src/utils/yardimcilar.php';
Oturum::baslat();
if (!Oturum::adminMi()) {
    Oturum::flash('danger', 'Bu sayfaya erişim yetkiniz yok.');
    header('Location: ' . url()); exit();
}
require_once __DIR__ . '/../src/core/Veritabani.php';
$db = Veritabani::baglan();
$urunSayisi    = $db->query("SELECT COUNT(*) FROM urunler")->fetchColumn();
$kulSayisi     = $db->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
$yorumSayisi   = $db->query("SELECT COUNT(*) FROM yorumlar")->fetchColumn();
$siparisSayisi = $db->query("SELECT COUNT(*) FROM siparisler")->fetchColumn();
$sonUrunler    = $db->query("SELECT u.*, k.ad AS kategori_adi FROM urunler u JOIN kategoriler k ON u.kategori_id=k.id ORDER BY u.id DESC LIMIT 8")->fetchAll();
$sonYorumlar   = $db->query("SELECT y.*, ku.ad_soyad, ur.ad AS urun_adi FROM yorumlar y JOIN kullanicilar ku ON y.kullanici_id=ku.id JOIN urunler ur ON y.urun_id=ur.id ORDER BY y.tarih DESC LIMIT 5")->fetchAll();
$sayfaBasligi  = 'Admin Panel';
include __DIR__ . '/../src/ui/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield me-2 text-warning"></i>Admin Paneli</h4>
</div>
<div class="row g-4 mb-5">
<?php
$kartlar = [
    ['başlık'=>'Toplam Ürün',    'değer'=>$urunSayisi,    'ikon'=>'bi-box',          'renk'=>'primary'],
    ['başlık'=>'Kullanıcılar',   'değer'=>$kulSayisi,     'ikon'=>'bi-people',       'renk'=>'success'],
    ['başlık'=>'Yorumlar',       'değer'=>$yorumSayisi,   'ikon'=>'bi-chat-dots',    'renk'=>'warning'],
    ['başlık'=>'Siparişler',     'değer'=>$siparisSayisi, 'ikon'=>'bi-bag-check',    'renk'=>'info'],
];
foreach ($kartlar as $k): ?>
<div class="col-md-3">
    <div class="card border-0 shadow-sm p-4 text-center">
        <i class="bi <?= $k['ikon'] ?> fs-1 text-<?= $k['renk'] ?> mb-2"></i>
        <h3 class="fw-bold mb-0"><?= $k['değer'] ?></h3>
        <p class="text-muted small mb-0"><?= $k['başlık'] ?></p>
    </div>
</div>
<?php endforeach; ?>
</div>
<div class="row g-4">
<div class="col-lg-7">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Ürünler</h6>
            <a href="<?= url('urunler.php') ?>" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
        </div>
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light"><tr><th class="ps-3">Ürün Adı</th><th>Kategori</th><th>Fiyat</th><th>Stok</th></tr></thead>
            <tbody>
            <?php foreach ($sonUrunler as $u): ?>
            <tr>
                <td class="ps-3"><a href="<?= url('urun.php?id='.$u['id']) ?>" class="text-decoration-none fw-semibold"><?= temizle($u['ad']) ?></a></td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= temizle($u['kategori_adi']) ?></span></td>
                <td class="text-success fw-semibold"><?= fiyat($u['fiyat']) ?></td>
                <td><?= $u['stok'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="col-lg-5">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Son Yorumlar</h6></div>
        <div class="card-body p-0">
        <?php if (empty($sonYorumlar)): ?>
            <p class="text-muted small text-center py-3">Henüz yorum yok.</p>
        <?php else: ?>
            <?php foreach ($sonYorumlar as $y): ?>
            <div class="border-bottom px-3 py-2">
                <div class="d-flex justify-content-between">
                    <strong class="small"><?= temizle($y['ad_soyad']) ?></strong>
                    <span class="text-warning small"><?= str_repeat('★', $y['puan']) ?></span>
                </div>
                <p class="small text-muted mb-0 text-truncate"><?= temizle($y['urun_adi']) ?> — <?= temizle($y['metin']) ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../src/ui/footer.php'; ?>
