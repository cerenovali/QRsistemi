<?php
/**
 * kayit.php — Kayıt Sayfası
 */
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/KullaniciModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
if (Oturum::girisYapildiMi()) { header('Location: ' . url()); exit(); }

$hata = $basari = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post('sifre') !== post('sifre2')) {
        $hata = 'Şifreler eşleşmiyor.';
    } else {
        $m = new KullaniciModel();
        $r = $m->kayitOl(post('ad_soyad'), post('kullanici_adi'), post('email'), post('sifre'));
        if ($r['ok']) $basari = $r['mesaj'];
        else          $hata   = $r['mesaj'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kayıt Ol | QR Kod Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:linear-gradient(135deg,#1e3a8a,#7c3aed); min-height:100vh; display:flex; align-items:center; padding:2rem 0; font-family:'Segoe UI',sans-serif; }
        .kart { border-radius:20px; border:none; box-shadow:0 25px 60px rgba(0,0,0,.3); }
        .btn-kayit { background:linear-gradient(135deg,#2563eb,#7c3aed); border:none; font-weight:600; border-radius:10px; }
    </style>
</head>
<body>
<div class="container">
<div class="row justify-content-center">
<div class="col-md-6 col-lg-5">
<div class="card kart p-4">
    <div class="text-center mb-4">
        <div class="fs-1 mb-1" style="background:linear-gradient(135deg,#2563eb,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            <i class="bi bi-person-plus"></i>
        </div>
        <h5 class="fw-bold mb-0">Yeni Hesap Oluştur</h5>
    </div>

    <?php if ($hata):   echo '<div class="alert alert-danger py-2 small">'   . temizle($hata)   . '</div>'; endif; ?>
    <?php if ($basari): echo '<div class="alert alert-success py-2 small">' . temizle($basari) . ' <a href="' . url('giris.php') . '" class="fw-semibold">Giriş Yap →</a></div>'; endif; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label small fw-semibold">Ad Soyad</label>
                <input type="text" name="ad_soyad" class="form-control" required value="<?= temizle(post('ad_soyad')) ?>" placeholder="Adınız Soyadınız">
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Kullanıcı Adı</label>
                <input type="text" name="kullanici_adi" class="form-control" required value="<?= temizle(post('kullanici_adi')) ?>" placeholder="kullanici_adi">
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">E-posta</label>
                <input type="email" name="email" class="form-control" required value="<?= temizle(post('email')) ?>" placeholder="ornek@mail.com">
            </div>
            <div class="col-sm-6">
                <label class="form-label small fw-semibold">Şifre <span class="text-muted">(≥6 karakter)</span></label>
                <input type="password" name="sifre" class="form-control" required placeholder="••••••">
            </div>
            <div class="col-sm-6">
                <label class="form-label small fw-semibold">Şifre Tekrar</label>
                <input type="password" name="sifre2" class="form-control" required placeholder="••••••">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-kayit btn-primary w-100 py-2 text-white">
                    <i class="bi bi-person-plus me-2"></i>Kayıt Ol
                </button>
            </div>
        </div>
    </form>
    <hr>
    <p class="text-center text-muted small mb-0">
        Zaten hesabın var mı? <a href="<?= url('giris.php') ?>" class="fw-semibold">Giriş Yap</a>
    </p>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
