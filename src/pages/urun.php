<?php
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/UrunModel.php';
require_once __DIR__ . '/src/modules/YorumModel.php';
require_once __DIR__ . '/src/modules/SepetModel.php';
require_once __DIR__ . '/src/services/QRKodServisi.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();

$urunM = new UrunModel();
$token = get('token');
$id    = (int)get('id');

if ($token !== '')  $urun = $urunM->tokenIleGetir($token);
elseif ($id > 0)    $urun = $urunM->idIleGetir($id);
else                $urun = null;

if (!$urun) {
    Oturum::flash('danger', 'Ürün bulunamadı.');
    header('Location: ' . url('urunler.php'));
    exit();
}

$yorumM   = new YorumModel();
$yorumlar = $yorumM->urunYorumlari($urun['id']);
$puan     = $urunM->ortPuan($urun['id']);
$qrUrl    = QRKodServisi::qrIcerigi($urun['qr_token']);

// Yorum gönder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yorum_gonder'])) {
    Oturum::girisGerekli();
    $r = $yorumM->ekle($urun['id'], Oturum::getId(), (int)post('puan'), post('yorum_metni'));
    Oturum::flash($r['ok'] ? 'success' : 'danger', $r['mesaj']);
    header('Location: ' . url('urun.php?id=' . $urun['id']) . '#yorumlar');
    exit();
}

// Sepete ekle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sepete_ekle'])) {
    Oturum::girisGerekli();
    $r = (new SepetModel())->ekle(Oturum::getId(), $urun['id'], (int)(post('adet') ?: 1), post('beden', ''));
    Oturum::flash($r['ok'] ? 'success' : 'danger', $r['mesaj']);
    header('Location: ' . url('urun.php?id=' . $urun['id']));
    exit();
}

$sayfaBasligi = $urun['ad'];
include __DIR__ . '/src/ui/header.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url() ?>">Ana Sayfa</a></li>
        <li class="breadcrumb-item"><a href="<?= url('urunler.php') ?>">Ürünler</a></li>
        <li class="breadcrumb-item active"><?= temizle($urun['ad']) ?></li>
    </ol>
</nav>

<?php if ($token !== ''): ?>
<div class="alert alert-success mb-4">
    <i class="bi bi-qr-code-scan me-2"></i><strong>QR Kod ile ulaştınız!</strong> Ürün başarıyla tanındı.
</div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <!-- Sol -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm p-4 h-100">
            <?php if (!empty($urun['resim'])): ?>
            <div class="text-center mb-4 bg-light rounded-3 p-3">
                <img src="<?= temizle($urun['resim']) ?>" alt="<?= temizle($urun['ad']) ?>"
                     style="max-height:220px;max-width:100%;object-fit:contain;"
                     onerror="this.parentNode.style.display='none'">
            </div>
            <?php endif; ?>

            <div class="mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary"><?= temizle($urun['kategori_adi']) ?></span>
                <?php if ($urun['stok'] <= 0): ?>
                    <span class="badge bg-danger ms-1">Tükendi</span>
                <?php elseif ($urun['stok'] < 10): ?>
                    <span class="badge bg-warning text-dark ms-1">Son <?= $urun['stok'] ?> adet</span>
                <?php endif; ?>
            </div>
            <h2 class="fw-bold mb-2"><?= temizle($urun['ad']) ?></h2>

            <?php if ($puan > 0): ?>
            <div class="d-flex align-items-center gap-1 mb-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star-fill fs-5 <?= $i <= $puan ? 'text-warning' : 'text-muted' ?>"></i>
                <?php endfor; ?>
                <span class="text-muted small ms-1"><?= $puan ?>/5 (<?= count($yorumlar) ?> yorum)</span>
            </div>
            <?php endif; ?>

            <p class="text-muted mb-4"><?= nl2br(temizle($urun['aciklama'])) ?></p>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3 text-center">
                        <p class="text-muted small mb-1">Fiyat</p>
                        <h4 class="fw-bold text-success mb-0"><?= fiyat($urun['fiyat']) ?></h4>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 text-center">
                        <p class="text-muted small mb-1">Stok</p>
                        <h4 class="fw-bold text-primary mb-0"><?= $urun['stok'] ?> adet</h4>
                    </div>
                </div>
            </div>

            <?php if ($urun['stok'] > 0): ?>
                <?php if (Oturum::girisYapildiMi()): ?>
                <form method="POST" class="d-flex flex-wrap gap-3 align-items-end">
                    <?php
                    $bedenTokenleri = ['tshirt-beyaz-013', 'sweatshirt-polar-016', 'sort-denim-014'];
                    $spCorTokenleri = ['corap-spor-015'];
                    ?>
                    <?php if (in_array($urun['qr_token'], $bedenTokenleri)): ?>
                    <div>
                        <label class="form-label small fw-semibold">Beden</label>
                        <select name="beden" class="form-select" style="width:90px;" required>
                            <option value="">Seç</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                        </select>
                    </div>
                    <?php elseif (in_array($urun['qr_token'], $spCorTokenleri)): ?>
                    <div>
                        <label class="form-label small fw-semibold">Beden</label>
                        <select name="beden" class="form-select" style="width:100px;" required>
                            <option value="">Seç</option>
                            <option value="36">36</option>
                            <option value="37">37</option>
                            <option value="38">38</option>
                            <option value="39">39</option>
                        </select>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="beden" value="">
                    <?php endif; ?>
                    <div>
                        <label class="form-label small fw-semibold">Adet</label>
                        <input type="number" name="adet" value="1" min="1" max="<?= $urun['stok'] ?>"
                               class="form-control" style="width:80px;">
                    </div>
                    <button type="submit" name="sepete_ekle" class="btn btn-ana text-white px-4 py-2 flex-fill">
                        <i class="bi bi-cart-plus me-2"></i>Sepete Ekle
                    </button>
                    <a href="<?= url('sepet.php') ?>" class="btn btn-outline-secondary py-2">
                        <i class="bi bi-cart3"></i>
                    </a>
                </form>
                <?php else: ?>
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Sepete eklemek için <a href="<?= url('giris.php') ?>" class="fw-semibold">giriş yapın</a>.
                </div>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn btn-secondary w-100" disabled>Stokta Yok</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sağ: QR -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4 text-center mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-qr-code me-2 text-primary"></i>Ürün QR Kodu</h6>
            <div style="display:flex;justify-content:center;align-items:center;min-height:210px;" id="qrSarici">
                <canvas id="urunQR" style="border-radius:8px;"></canvas>
            </div>
            <p class="text-muted small mt-2 mb-2">Bu QR kodu tarayınca bu sayfaya ulaşırsınız.</p>
            <button onclick="urunQrIndir()" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-download me-1"></i>QR İndir
            </button>
        </div>
        <div class="card border-0 shadow-sm p-3">
            <h6 class="fw-semibold mb-2"><i class="bi bi-info-circle me-1 text-muted"></i>Ürün Bilgileri</h6>
            <table class="table table-sm mb-0 small">
                <tr><th class="text-muted fw-normal border-0">Kategori</th><td class="border-0"><?= temizle($urun['kategori_adi']) ?></td></tr>
                <tr>
                    <th class="text-muted fw-normal">Token</th>
                    <td>
                        <span class="font-monospace text-primary"><?= temizle($urun['qr_token']) ?></span>
                        <button type="button" onclick="tokenKopyala('<?= temizle($urun['qr_token']) ?>')"
                                class="btn btn-sm btn-outline-secondary py-0 px-1 ms-1" title="Kopyala">
                            <i class="bi bi-clipboard" id="kopyalaIkon"></i>
                        </button>
                    </td>
                </tr>
                <tr><th class="text-muted fw-normal">Eklenme</th><td><?= tarih($urun['olusturma']) ?></td></tr>
            </table>
        </div>
    </div>
</div>

<!-- Yorumlar -->
<div id="yorumlar" class="card border-0 shadow-sm p-4">
    <h5 class="fw-bold mb-4"><i class="bi bi-chat-dots me-2 text-primary"></i>Kullanıcı Yorumları</h5>

    <?php if (Oturum::girisYapildiMi()): ?>
    <div class="bg-light rounded-3 p-4 mb-4">
        <h6 class="fw-semibold mb-3">Yorum Yaz</h6>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Puanınız</label>
                <div class="d-flex gap-1 mb-1">
                    <span class="yildiz" data-v="1" style="font-size:2rem;cursor:pointer;color:#d1d5db;"><i class="bi bi-star-fill"></i></span>
                    <span class="yildiz" data-v="2" style="font-size:2rem;cursor:pointer;color:#d1d5db;"><i class="bi bi-star-fill"></i></span>
                    <span class="yildiz" data-v="3" style="font-size:2rem;cursor:pointer;color:#d1d5db;"><i class="bi bi-star-fill"></i></span>
                    <span class="yildiz" data-v="4" style="font-size:2rem;cursor:pointer;color:#d1d5db;"><i class="bi bi-star-fill"></i></span>
                    <span class="yildiz" data-v="5" style="font-size:2rem;cursor:pointer;color:#d1d5db;"><i class="bi bi-star-fill"></i></span>
                </div>
                <input type="hidden" name="puan" id="puanInput" value="0">
                <small class="text-muted" id="puanYazi">Yıldıza tıklayarak puan verin</small>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Yorumunuz</label>
                <textarea name="yorum_metni" class="form-control" rows="3" required
                          placeholder="Bu ürün hakkında ne düşünüyorsunuz?"></textarea>
            </div>
            <button type="submit" name="yorum_gonder" class="btn btn-ana text-white px-4">
                <i class="bi bi-send me-2"></i>Yorumu Gönder
            </button>
        </form>
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-4 py-2 small">
        <i class="bi bi-info-circle me-1"></i>
        Yorum yapabilmek için <a href="<?= url('giris.php') ?>" class="fw-semibold">giriş yapın</a>.
    </div>
    <?php endif; ?>

    <?php if (empty($yorumlar)): ?>
        <p class="text-muted text-center py-3">Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
    <?php else: ?>
        <?php foreach ($yorumlar as $y): ?>
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between mb-1 flex-wrap gap-1">
                <div>
                    <strong class="small"><?= temizle($y['ad_soyad']) ?></strong>
                    <span class="text-muted small ms-1">@<?= temizle($y['kullanici_adi']) ?></span>
                </div>
                <div>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star-fill small <?= $i <= $y['puan'] ? 'text-warning' : 'text-muted' ?>"></i>
                    <?php endfor; ?>
                    <span class="text-muted small ms-1"><?= tarih($y['tarih']) ?></span>
                </div>
            </div>
            <p class="mb-0 small"><?= temizle($y['metin']) ?></p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$qrUrlEscaped = addslashes($qrUrl);
$urunId = (int)$urun['id'];
$qrUrlJs = addslashes($qrUrl);

$sayfaJs = <<<JSEOF
function qrKodOlustur() {
    var canvas = document.getElementById('urunQR');
    if (!canvas) return;
    if (typeof QRCode !== 'undefined') {
        QRCode.toCanvas(canvas, '{$qrUrlEscaped}', {
            width: 200, margin: 2,
            color: { dark: '#1e3a8a', light: '#ffffff' }
        }, function(err) { if (err) console.error('QR hata:', err); });
    } else {
        var img = document.createElement('img');
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent('{$qrUrlEscaped}') + '&size=200x200&ecc=M';
        img.style.maxWidth = '200px';
        img.style.borderRadius = '8px';
        canvas.parentNode.insertBefore(img, canvas);
        canvas.style.display = 'none';
    }
}

function urunQrIndir() {
    var canvas = document.getElementById('urunQR');
    if (canvas && canvas.width > 0) {
        var dataUrl = canvas.toDataURL('image/png');
        var a = document.createElement('a');
        a.download = 'urun-qr-{$urunId}.png';
        a.href = dataUrl;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    } else {
        window.open('https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent('{$qrUrlJs}') + '&size=300x300', '_blank');
    }
}

var qrScript = document.createElement('script');
qrScript.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
qrScript.onload = function() { qrKodOlustur(); };
qrScript.onerror = function() { qrKodOlustur(); };
document.head.appendChild(qrScript);

function tokenKopyala(token) {
    navigator.clipboard.writeText(token).then(function() {
        var ikon = document.getElementById('kopyalaIkon');
        ikon.className = 'bi bi-clipboard-check text-success';
        setTimeout(function() { ikon.className = 'bi bi-clipboard'; }, 2000);
    });
}

var yildizlar = document.querySelectorAll('.yildiz');
var secilenPuan = 0;
var puanAdlari = ['','Cok Kotu','Kotu','Orta','Iyi','Mukemmel'];

function yildizBoy(n) {
    yildizlar.forEach(function(y) {
        y.style.color = parseInt(y.getAttribute('data-v')) <= n ? '#f59e0b' : '#d1d5db';
    });
}

yildizlar.forEach(function(y) {
    y.addEventListener('mouseenter', function() { yildizBoy(parseInt(y.getAttribute('data-v'))); });
    y.addEventListener('mouseleave', function() { yildizBoy(secilenPuan); });
    y.addEventListener('click', function() {
        secilenPuan = parseInt(y.getAttribute('data-v'));
        document.getElementById('puanInput').value = secilenPuan;
        yildizBoy(secilenPuan);
    });
});
JSEOF;

include __DIR__ . '/src/ui/footer.php';
?>
