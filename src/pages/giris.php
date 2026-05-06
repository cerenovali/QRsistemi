<?php
/**
 * giris.php — Giriş Sayfası
 */
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/KullaniciModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
if (Oturum::girisYapildiMi()) { header('Location: ' . url()); exit(); }

$hata = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $m = new KullaniciModel();
        $r = $m->girisYap(post('kullanici_adi'), post('sifre'));
        if ($r['ok']) {
            Oturum::kullaniciyiKaydet($r['kullanici']);
            Oturum::flash('success', 'Hoş geldiniz, ' . $r['kullanici']['ad_soyad'] . '!');
            $redirect = get('redirect') ?: url();
            header('Location: ' . $redirect);
            exit();
        } else {
            $hata = $r['mesaj'];
        }
    } catch (Exception $e) {
        $hata = 'Giriş sırasında hata oluştu.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Giriş | QR Kod Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:linear-gradient(135deg,#1e3a8a,#7c3aed); min-height:100vh; display:flex; align-items:center; font-family:'Segoe UI',sans-serif; }
        .kart { border-radius:20px; border:none; box-shadow:0 25px 60px rgba(0,0,0,.3); }
        .btn-giris { background:linear-gradient(135deg,#2563eb,#7c3aed); border:none; font-weight:600; border-radius:10px; }
    </style>
</head>
<body>
<div class="container">
<div class="row justify-content-center">
<div class="col-md-5 col-lg-4">
<div class="card kart p-4">
    <div class="text-center mb-4">
        <div class="fs-1 mb-1" style="background:linear-gradient(135deg,#2563eb,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            <i class="bi bi-qr-code-scan"></i>
        </div>
        <h5 class="fw-bold mb-0">Giriş Yap</h5>
        <p class="text-muted small">Hesabınıza giriş yapın</p>
    </div>

    <?php if ($hata): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= temizle($hata) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Kullanıcı Adı</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                <input type="text" name="kullanici_adi" class="form-control" required
                       placeholder="kullanici_adi" value="<?= temizle(post('kullanici_adi')) ?>">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold">Şifre</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" name="sifre" class="form-control" required id="sifre" placeholder="••••••">
                <button type="button" class="btn btn-outline-secondary" onclick="toggleSifre()">
                    <i class="bi bi-eye" id="goz"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-giris btn-primary w-100 py-2 text-white">
            <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
        </button>
    </form>
    <hr>
    <p class="text-center text-muted small mb-2">
        Hesabın yok mu? <a href="<?= url('kayit.php') ?>" class="fw-semibold">Kayıt Ol</a>
    </p>
    <p class="text-center"><a href="<?= url() ?>" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Ana Sayfa</a></p>

</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSifre() {
    const s = document.getElementById('sifre');
    const g = document.getElementById('goz');
    s.type = s.type === 'password' ? 'text' : 'password';
    g.className = s.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
