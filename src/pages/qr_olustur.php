<?php
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';
Oturum::baslat();
$sayfaBasligi = 'QR Kod Oluşturucu';
include __DIR__ . '/src/ui/header.php';
?>

<h4 class="fw-bold mb-1"><i class="bi bi-qr-code me-2 text-primary"></i>QR Kod Oluşturucu</h4>
<p class="text-muted small mb-4">Herhangi bir metin, URL veya bilgi için anında QR kod oluşturun ve indirin.</p>

<div class="row g-4">
    <!-- Sol: Form -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="mb-3">
                <label class="form-label fw-semibold small">İçerik Tipi</label>
                <select class="form-select" id="tipSec" onchange="tipDegisti()">
                    <option value="metin">Metin / Not</option>
                    <option value="url">Web Adresi (URL)</option>
                    <option value="email">E-posta Adresi</option>
                    <option value="telefon">Telefon Numarası</option>
                    <option value="wifi">WiFi Bilgisi</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small" id="veriLabel">İçerik</label>
                <textarea id="qrIcerik" class="form-control" rows="4"
                          placeholder="İçerik girin..."></textarea>
                <div class="form-text" id="veriYardim">Metin, URL veya herhangi bir bilgi girebilirsiniz.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Renk</label>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" onclick="renkSec('#1e3a8a','#ffffff',this)" class="btn btn-sm btn-primary secili-renk">🔵 Mavi</button>
                    <button type="button" onclick="renkSec('#000000','#ffffff',this)" class="btn btn-sm btn-outline-dark">⬛ Siyah</button>
                    <button type="button" onclick="renkSec('#7c3aed','#ffffff',this)" class="btn btn-sm btn-outline-secondary" style="color:#7c3aed;border-color:#7c3aed;">🟣 Mor</button>
                    <button type="button" onclick="renkSec('#16a34a','#ffffff',this)" class="btn btn-sm btn-outline-success">🟢 Yeşil</button>
                    <button type="button" onclick="renkSec('#dc2626','#ffffff',this)" class="btn btn-sm btn-outline-danger">🔴 Kırmızı</button>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <label class="form-label fw-semibold small">Hızlı Şablonlar</label>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" onclick="sablon('url','https://')" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-link me-1"></i>URL
                </button>
                <button type="button" onclick="sablon('email','mailto:ornek@mail.com')" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-envelope me-1"></i>E-posta
                </button>
                <button type="button" onclick="sablon('telefon','tel:+905001234567')" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-phone me-1"></i>Telefon
                </button>
                <button type="button" onclick="sablon('wifi','WIFI:T:WPA;S:AgAdi;P:Sifre;;')" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-wifi me-1"></i>WiFi
                </button>
            </div>
        </div>
    </div>

    <!-- Sağ: Önizleme -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm p-4 text-center mb-4">
            <h6 class="fw-bold mb-3">QR Önizleme</h6>
            <div id="qrOnizleme" style="min-height:220px;display:flex;align-items:center;justify-content:center;">
                <div class="text-muted">
                    <i class="bi bi-qr-code" style="font-size:4rem;opacity:.2;display:block;"></i>
                    <small>İçerik girin...</small>
                </div>
            </div>
            <!-- Sadece İndir butonu — Büyüt kaldırıldı -->
            <div id="qrButonlar" style="display:none;" class="mt-3 d-flex gap-2 justify-content-center">
                <button type="button" onclick="qrOlusturVeKaydet()" class="btn btn-ana text-white px-4">
                    <i class="bi bi-qr-code me-2"></i>Oluştur & Kaydet
                </button>
                <button type="button" onclick="qrIndir()" class="btn btn-outline-primary px-4">
                    <i class="bi bi-download me-2"></i>İndir
                </button>
            </div>
        </div>

        <!-- Son Oluşturulanlar -->
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-muted"></i>Son Oluşturulanlar</h6>
                <button type="button" onclick="gecmisTemizle()" class="btn btn-sm btn-outline-secondary">Temizle</button>
            </div>
            <div id="gecmisAlani"><p class="text-muted small mb-0">Henüz QR oluşturulmadı.</p></div>
        </div>
    </div>
</div>

<?php
$sayfaJs = <<<'JSEOF'
var koyuRenk = '#1e3a8a';
var acikRenk = '#ffffff';
var qrCanvas = null;
var qrHazir  = false;

// Geçmişi localStorage'dan yükle
var gecmis = [];
try {
    var g = localStorage.getItem('qr_gecmis');
    if (g) gecmis = JSON.parse(g);
} catch(e) { gecmis = []; }

// qrcode.js dinamik yükle
var s = document.createElement('script');
s.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js';
s.onload  = function() { qrHazir = true; gecmisGuncelle(); };
s.onerror = function() { qrHazir = false; gecmisGuncelle(); };
document.head.appendChild(s);

var textarea = document.getElementById('qrIcerik');
textarea.addEventListener('input', guncelle);

function guncelle() {
    var deger = textarea.value.trim();
    var onizleme = document.getElementById('qrOnizleme');
    var butonlar = document.getElementById('qrButonlar');

    if (!deger) {
        onizleme.innerHTML = '<div class="text-muted"><i class="bi bi-qr-code" style="font-size:4rem;opacity:.2;display:block;"></i><small>İçerik girin...</small></div>';
        butonlar.style.display = 'none';
        qrCanvas = null;
        return;
    }

    if (!qrHazir) {
        onizleme.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(deger) + '&size=220x220&ecc=M&color=' + koyuRenk.replace('#','') + '" id="qrYedekImg" style="border-radius:8px;max-width:220px;">';
        butonlar.style.display = 'flex';
        qrCanvas = null;
        return;
    }

    onizleme.innerHTML = '';
    var c = document.createElement('canvas');
    c.id = 'qrCanvasAna';
    onizleme.appendChild(c);
    QRCode.toCanvas(c, deger, {
        width: 220, margin: 2,
        color: { dark: koyuRenk, light: acikRenk }
    }, function(err) {
        if (!err) {
            qrCanvas = c;
            butonlar.style.display = 'flex';
        }
    });
}

function qrOlusturVeKaydet() {
    var deger = textarea.value.trim();
    if (!deger) { alert('İçerik girin!'); return; }

    // Varsa güncelle, yoksa ekle
    var idx = gecmis.findIndex(function(x){ return x.icerik === deger; });
    if (idx >= 0) gecmis.splice(idx, 1);
    gecmis.unshift({ icerik: deger, renk: koyuRenk, tarih: new Date().toLocaleString('tr-TR') });
    if (gecmis.length > 10) gecmis.pop();
    gecmisKaydet();
    gecmisGuncelle();

    // Buton geri bildirimi
    var btn = document.getElementById('qrButonlar').querySelector('button');
    var eski = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Kaydedildi!';
    btn.disabled = true;
    setTimeout(function() { btn.innerHTML = eski; btn.disabled = false; }, 1500);
}

function qrIndir() {
    var deger = textarea.value.trim();
    if (!deger) return;

    if (qrCanvas) {
        // Canvas varsa direkt indir
        qrCanvas.toBlob(function(blob) {
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.download = 'qr_kod.png';
            a.href = url;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 'image/png');
    } else {
        // Yedek: CDN görseli yeni sekmede aç
        window.open('https://api.qrserver.com/v1/create-qr-code/?data=' + encodeURIComponent(deger) + '&size=400x400&ecc=M', '_blank');
    }
}

function gecmisKaydet() {
    try { localStorage.setItem('qr_gecmis', JSON.stringify(gecmis)); } catch(e) {}
}

function gecmisGuncelle() {
    var alani = document.getElementById('gecmisAlani');
    if (!gecmis.length) {
        alani.innerHTML = '<p class="text-muted small mb-0">Henüz QR oluşturulmadı.</p>';
        return;
    }

    var html = '<div class="d-flex flex-column gap-2">';
    gecmis.forEach(function(item, i) {
        var g = item.icerik || item; // eski format uyumu
        var kisa = g.length > 40 ? g.slice(0,40)+'...' : g;
        html += '<div class="d-flex align-items-center gap-2 border rounded-3 p-2">';
        // Mini QR önizleme canvas
        html += '<canvas id="miniQr' + i + '" width="40" height="40" style="border-radius:4px;flex-shrink:0;"></canvas>';
        html += '<div class="flex-grow-1" style="min-width:0;">';
        html += '<div class="small fw-semibold text-truncate" style="max-width:180px;">' + kisa + '</div>';
        if (item.tarih) html += '<div class="text-muted" style="font-size:.7rem;">' + item.tarih + '</div>';
        html += '</div>';
        html += '<button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="gecmisYukle(' + i + ')" title="Yükle"><i class="bi bi-arrow-up-circle"></i></button>';
        html += '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="gecmisSil(' + i + ')" title="Sil"><i class="bi bi-trash"></i></button>';
        html += '</div>';
    });
    html += '</div>';
    alani.innerHTML = html;

    // Mini QR'ları çiz
    if (qrHazir) {
        gecmis.forEach(function(item, i) {
            var g = item.icerik || item;
            var renk = item.renk || '#1e3a8a';
            var c = document.getElementById('miniQr' + i);
            if (c) {
                QRCode.toCanvas(c, g, { width: 40, margin: 1, color: { dark: renk, light: '#ffffff' } }, function(){});
            }
        });
    }
}

function gecmisYukle(idx) {
    var item = gecmis[idx];
    textarea.value = item.icerik || item;
    koyuRenk = item.renk || '#1e3a8a';
    guncelle();
    textarea.focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function gecmisSil(idx) {
    gecmis.splice(idx, 1);
    gecmisKaydet();
    gecmisGuncelle();
}

function gecmisTemizle() {
    if (!confirm('Tüm geçmiş silinsin mi?')) return;
    gecmis = [];
    gecmisKaydet();
    gecmisGuncelle();
}

function renkSec(koyu, ak, btn) {
    koyuRenk = koyu; acikRenk = ak;
    document.querySelectorAll('.secili-renk').forEach(function(b){ b.classList.remove('secili-renk'); });
    btn.classList.add('secili-renk');
    guncelle();
}

var tipBilgileri = {
    'metin':   { label:'İçerik',       yer:'İçerik girin...',               yardim:'Herhangi bir metin veya bilgi.' },
    'url':     { label:'Web Adresi',   yer:'https://ornek.com',             yardim:'https:// ile başlayan URL girin.' },
    'email':   { label:'E-posta',      yer:'mailto:ornek@mail.com',         yardim:'mailto: ön ekiyle e-posta adresi.' },
    'telefon': { label:'Telefon',      yer:'tel:+905001234567',             yardim:'tel: ön ekiyle uluslararası format.' },
    'wifi':    { label:'WiFi Bilgisi', yer:'WIFI:T:WPA;S:AgAdi;P:Sifre;;', yardim:'WPA şifreli ağ için format.' }
};

function tipDegisti() {
    var tip = document.getElementById('tipSec').value;
    var b = tipBilgileri[tip];
    document.getElementById('veriLabel').textContent  = b.label;
    textarea.placeholder = b.yer;
    document.getElementById('veriYardim').textContent = b.yardim;
}

function sablon(tip, veri) {
    document.getElementById('tipSec').value = tip;
    textarea.value = veri;
    tipDegisti();
    guncelle();
    textarea.focus();
}
JSEOF;
include __DIR__ . '/src/ui/footer.php';
?>
