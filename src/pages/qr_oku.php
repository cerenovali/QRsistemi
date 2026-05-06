<?php
/**
 * qr_oku.php — QR Kod Tarayıcı
 */
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';
Oturum::baslat();
$sayfaBasligi = 'QR Kod Tara';
include __DIR__ . '/src/ui/header.php';
?>

<h4 class="fw-bold mb-1"><i class="bi bi-camera me-2 text-primary"></i>QR Kod Tarayıcı</h4>
<p class="text-muted small mb-4">Kameranızı ürün QR koduna doğrultun — otomatik tanınır ve ürün sayfasına yönlendirilirsiniz.</p>

<div class="row g-4 justify-content-center">
<div class="col-lg-7">
    <div class="card border-0 shadow-sm p-4">
        <!-- Kamera alanı -->
        <div class="position-relative bg-dark rounded-3 overflow-hidden mb-3" style="aspect-ratio:4/3;">
            <video id="video" class="w-100 h-100" style="object-fit:cover;" playsinline autoplay></video>
            <canvas id="canvas" class="position-absolute top-0 start-0 w-100 h-100" style="opacity:0;pointer-events:none;"></canvas>
            <!-- Tarama çerçevesi -->
            <div class="position-absolute top-50 start-50 translate-middle"
                 style="width:220px;height:220px;border:3px solid #3b82f6;border-radius:14px;
                        box-shadow:0 0 0 9999px rgba(0,0,0,.45);">
                <div id="taramaHatti" style="position:absolute;top:0;left:0;right:0;height:3px;
                     background:linear-gradient(90deg,transparent,#3b82f6,transparent);
                     animation:tara 2s linear infinite;"></div>
            </div>
            <!-- Durum -->
            <div class="position-absolute bottom-0 start-0 end-0 p-2 text-center"
                 style="background:rgba(0,0,0,.55);">
                <span class="text-white small" id="durumMesaji">Kamera başlatılıyor...</span>
            </div>
        </div>

        <!-- Sonuç Bölümü -->
        <div id="sonucAlani" class="d-none">
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <strong>Kod okundu!</strong>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Okunan İçerik</label>
                <div class="input-group">
                    <input type="text" id="okunanDeger" class="form-control font-monospace" readonly>
                    <button class="btn btn-outline-secondary" onclick="kopyala()" title="Kopyala">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button onclick="urunYonlendir()" class="btn btn-ana text-white flex-fill">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Ürüne Git
                </button>
                <button onclick="yenidenTara()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Tekrar
                </button>
            </div>
        </div>

        <!-- Manuel Giriş -->
        <hr class="mt-3">
        <p class="small text-muted fw-semibold mb-1"><i class="bi bi-keyboard me-1"></i>Ürün Token ile Ara</p>
        <p class="text-muted" style="font-size:.75rem;">Ürün sayfasındaki <strong>Token</strong> numarasını girerek ürüne ulaşabilirsin.</p>
        <div class="input-group">
            <input type="text" id="manuelToken" class="form-control font-monospace small" placeholder="Örn: pinar-su-500ml">
            <button class="btn btn-outline-primary" onclick="manuelGit()">
                <i class="bi bi-search me-1"></i>Git
            </button>
        </div>
    </div>
</div>

<div class="col-lg-5">
    <div class="card border-0 shadow-sm p-4 mb-3">
        <h6 class="fw-bold mb-3"><i class="bi bi-question-circle me-1 text-muted"></i>Nasıl Kullanılır?</h6>
        <ol class="small text-muted ps-3 mb-0">
            <li class="mb-2">Tarayıcınız kamera iznini soracak — <strong>İzin Ver</strong>'e tıklayın.</li>
            <li class="mb-2">Kamerayı ürünün QR koduna doğrultun.</li>
            <li class="mb-2">Kod otomatik okunur, ürün sayfasına yönlendirilirsiniz.</li>
            <li>Kameranız yoksa Manuel Token girişini kullanabilirsiniz.</li>
        </ol>
    </div>
    <div class="card border-0 shadow-sm p-4">
        <h6 class="fw-bold mb-2"><i class="bi bi-lightbulb me-1 text-warning"></i>Test Et</h6>
        <p class="small text-muted mb-2">Herhangi bir ürün sayfasından QR kodu indirip buradan okutun.</p>
        <a href="<?= url('urunler.php') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-grid me-1"></i>Ürünlere Git
        </a>
    </div>
</div>
</div>

<style>
@keyframes tara {
    0%   { top: 0; }
    50%  { top: calc(100% - 3px); }
    100% { top: 0; }
}
</style>

<?php
$baseUrl = url('urun.php');
$sayfaJs = "
const BASE_URL = '" . addslashes($baseUrl) . "';
let video   = document.getElementById('video');
let canvas  = document.getElementById('canvas');
let ctx     = canvas.getContext('2d');
let aktif   = true;
let sonOku  = '';

navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(function(stream) {
        video.srcObject = stream;
        video.play();
        document.getElementById('durumMesaji').textContent = 'QR kodu çerçeveye getirin...';
        requestAnimationFrame(taraCerceve);
    })
    .catch(function() {
        document.getElementById('durumMesaji').textContent = '⚠️ Kamera erişimi reddedildi.';
    });

function taraCerceve() {
    if (!aktif) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const kod = jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'dontInvert' });
        if (kod && kod.data !== sonOku) {
            sonOku = kod.data;
            kodBulundu(kod.data);
            return;
        }
    }
    requestAnimationFrame(taraCerceve);
}

function kodBulundu(deger) {
    aktif = false;
    document.getElementById('durumMesaji').textContent = '✅ Kod okundu!';
    document.getElementById('okunanDeger').value = deger;
    document.getElementById('sonucAlani').classList.remove('d-none');
}

function urunYonlendir() {
    const deger = document.getElementById('okunanDeger').value;
    if (!deger) return;
    if (deger.startsWith('http')) {
        window.location.href = deger;
    } else {
        window.location.href = BASE_URL + '?token=' + encodeURIComponent(deger);
    }
}

function yenidenTara() {
    sonOku = '';
    aktif  = true;
    document.getElementById('sonucAlani').classList.add('d-none');
    document.getElementById('durumMesaji').textContent = 'QR kodu çerçeveye getirin...';
    requestAnimationFrame(taraCerceve);
}

function kopyala() {
    navigator.clipboard.writeText(document.getElementById('okunanDeger').value)
        .then(function() { alert('Kopyalandı!'); });
}

function manuelGit() {
    const token = document.getElementById('manuelToken').value.trim();
    if (!token) return;
    window.location.href = BASE_URL + '?token=' + encodeURIComponent(token);
}

document.getElementById('manuelToken').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') manuelGit();
});
";
include __DIR__ . '/src/ui/footer.php';
?>
