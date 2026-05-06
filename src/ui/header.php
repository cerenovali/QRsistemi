<?php
/**
 * src/ui/header.php
 */
require_once __DIR__ . '/../core/Oturum.php';
require_once __DIR__ . '/../utils/yardimcilar.php';
Oturum::baslat();

$sepetimdekiler = 0;
if (Oturum::girisYapildiMi()) {
    require_once __DIR__ . '/../modules/SepetModel.php';
    $sepetimdekiler = (new SepetModel())->urunSayisi(Oturum::getId());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= temizle($sayfaBasligi ?? 'QR Sistemi') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --mavi:#2563eb; --mor:#7c3aed; }
        body { background:#f8fafc; font-family:'Segoe UI',sans-serif; }
        .navbar-custom { background:linear-gradient(135deg,var(--mavi),var(--mor)); box-shadow:0 2px 12px rgba(0,0,0,.18); }
        .navbar-custom .nav-link { color:rgba(255,255,255,.85) !important; }
        .navbar-custom .nav-link:hover { color:#fff !important; }
        .kart { border:none; border-radius:14px; transition:transform .2s,box-shadow .2s; overflow:hidden; }
        .kart:hover { transform:translateY(-5px); box-shadow:0 14px 30px rgba(0,0,0,.12); }
        .btn-ana { background:linear-gradient(135deg,var(--mavi),var(--mor)); border:none; color:#fff; font-weight:600; border-radius:10px; }
        .btn-ana:hover { opacity:.88; color:#fff; }
        .sepet-badge { position:absolute; top:-6px; right:-8px; font-size:.65rem; min-width:18px; padding:2px 4px; }
        .yildiz { cursor:pointer; font-size:1.8rem; color:#d1d5db; transition:color .1s; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-white fs-5" href="<?= url() ?>">
            <i class="bi bi-qr-code-scan me-2"></i>QR Sistemi
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url() ?>"><i class="bi bi-house me-1"></i>Ana Sayfa</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto gap-1 align-items-center">
                <?php if (Oturum::girisYapildiMi()): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= url('sepet.php') ?>">
                            <i class="bi bi-cart3 fs-5"></i>
                            <?php if ($sepetimdekiler > 0): ?>
                                <span class="badge bg-danger rounded-pill sepet-badge"><?= $sepetimdekiler ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= temizle(Oturum::getAd()) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="<?= url('profil.php') ?>"><i class="bi bi-person me-2"></i>Profilim</a></li>
                            <li><a class="dropdown-item" href="<?= url('siparisler.php') ?>"><i class="bi bi-bag-check me-2"></i>Siparişlerim</a></li>
                            <?php if (Oturum::adminMi()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-warning" href="<?= url('admin/panel.php') ?>"><i class="bi bi-shield me-2"></i>Admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= url('cikis.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Çıkış</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('giris.php') ?>"><i class="bi bi-box-arrow-in-right me-1"></i>Giriş</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm ms-1 fw-semibold" href="<?= url('kayit.php') ?>">Kayıt Ol</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php foreach (Oturum::flashlariAl() as $f): ?>
<div class="container mt-3">
    <div class="alert alert-<?= temizle($f['tur']) ?> alert-dismissible fade show shadow-sm">
        <?= temizle($f['mesaj']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endforeach; ?>

<main class="container py-4">
