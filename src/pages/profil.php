<?php
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/core/Veritabani.php';
require_once __DIR__ . '/src/modules/KullaniciModel.php';
require_once __DIR__ . '/src/modules/YorumModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
Oturum::girisGerekli();

$kid        = Oturum::getId();
$km         = new KullaniciModel();
$kullanici  = $km->idIleGetir($kid);
$ym         = new YorumModel();
$yorumlar   = $ym->hepsiniGetir();
$benimYorumlarim = array_filter($yorumlar, fn($y) => $y['kullanici_id'] == $kid);

// Kayıtlı kart
$kaydedilmisKart = $_SESSION['kayitli_kart'] ?? null;

// Kartı sil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['karti_sil'])) {
    unset($_SESSION['kayitli_kart']);
    Oturum::flash('success', 'Kayıtlı kart silindi.');
    header('Location: ' . url('profil.php'));
    exit();
}

$sayfaBasligi = 'Profilim';
include __DIR__ . '/src/ui/header.php';
?>

<div class="row g-4">
    <!-- Sol: Profil Kartı -->
    <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm p-4 text-center mb-4">
            <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center text-white fw-bold fs-2"
                 style="width:80px;height:80px;background:linear-gradient(135deg,#2563eb,#7c3aed);">
                <?= mb_strtoupper(mb_substr($kullanici['ad_soyad'], 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <h5 class="fw-bold mb-0"><?= temizle($kullanici['ad_soyad']) ?></h5>
            <p class="text-muted small mb-1">@<?= temizle($kullanici['kullanici_adi']) ?></p>
            <span class="badge <?= $kullanici['rol']==='admin'?'bg-warning text-dark':'bg-primary' ?>">
                <?= $kullanici['rol']==='admin' ? 'Admin' : 'Üye' ?>
            </span>
            <hr>
            <p class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i><?= temizle($kullanici['email']) ?></p>
            <p class="small text-muted mb-0"><i class="bi bi-calendar me-1"></i>Kayıt: <?= date('d.m.Y', strtotime($kullanici['kayit_tarihi'])) ?></p>
        </div>

        <!-- Kayıtlı Kart -->
        <div class="card border-0 shadow-sm p-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-1 text-primary"></i>Kayıtlı Kart</h6>
            <?php if ($kaydedilmisKart): ?>
            <div class="rounded-3 p-3 text-white mb-3" style="background:linear-gradient(135deg,#374151,#1f2937);">
                <div class="small opacity-75 mb-1">Kredi Kartı</div>
                <div class="fw-bold font-monospace">•••• •••• •••• <?= temizle($kaydedilmisKart['son4']) ?></div>
                <div class="small mt-1"><?= temizle($kaydedilmisKart['sahip']) ?></div>
                <div class="small opacity-75"><?= temizle($kaydedilmisKart['tarih']) ?></div>
            </div>
            <form method="POST">
                <button type="submit" name="karti_sil" class="btn btn-outline-danger btn-sm w-100"
                        onclick="return confirm('Kayıtlı kartı silmek istiyor musunuz?')">
                    <i class="bi bi-trash me-1"></i>Kartı Sil
                </button>
            </form>
            <?php else: ?>
            <p class="text-muted small mb-0">Kayıtlı kart bulunmuyor. Ödeme sırasında kartınızı kaydedebilirsiniz.</p>
            <a href="<?= url('odeme.php') ?>" class="btn btn-outline-primary btn-sm mt-2 w-100">
                <i class="bi bi-plus me-1"></i>Kart Ekle
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sağ: İçerik -->
    <div class="col-md-8 col-lg-9">
        <!-- Yorumlarım -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-2 text-primary"></i>Yorumlarım (<?= count($benimYorumlarim) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($benimYorumlarim)): ?>
                <p class="text-muted small text-center py-4 mb-0">Henüz yorum yapmadınız.</p>
                <?php else: ?>
                <table class="table table-hover mb-0 small align-middle">
                    <thead class="table-light"><tr><th class="ps-3">Ürün</th><th>Puan</th><th>Yorum</th><th>Tarih</th></tr></thead>
                    <tbody>
                    <?php foreach ($benimYorumlarim as $y): ?>
                    <tr>
                        <td class="ps-3">
                            <a href="<?= url('urun.php?id='.$y['urun_id']) ?>" class="text-decoration-none fw-semibold">
                                <?= temizle($y['urun_adi']) ?>
                            </a>
                        </td>
                        <td>
                            <?php for($i=1;$i<=5;$i++): ?>
                                <i class="bi bi-star-fill <?= $i<=$y['puan']?'text-warning':'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td class="text-muted" style="max-width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                            <?= temizle($y['metin']) ?>
                        </td>
                        <td class="text-muted"><?= date('d.m.Y', strtotime($y['tarih'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/src/ui/footer.php'; ?>
