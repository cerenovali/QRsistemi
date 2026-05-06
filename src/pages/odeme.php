<?php
require_once __DIR__ . '/src/core/Oturum.php';
require_once __DIR__ . '/src/modules/SepetModel.php';
require_once __DIR__ . '/src/modules/SiparisModel.php';
require_once __DIR__ . '/src/utils/yardimcilar.php';

Oturum::baslat();
Oturum::girisGerekli();

$sm         = new SepetModel();
$kid        = Oturum::getId();
$sepetItems = $sm->sepetimGetir($kid);
$toplam     = $sm->toplamTutar($kid);

if (empty($sepetItems)) {
    Oturum::flash('info', 'Sepetiniz boş.');
    header('Location: ' . url('sepet.php'));
    exit();
}

$kaydedilmisKart = $_SESSION['kayitli_kart'] ?? null;
$hatalar = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['odeme_yap'])) {
    $odemeYontemi = post('odeme_yontemi', 'kart');

    // Adres doğrulama
    if (strlen(post('ad_soyad_teslimat')) < 5) $hatalar[] = 'Ad soyad giriniz.';
    if (strlen(post('telefon')) < 10)          $hatalar[] = 'Geçerli telefon giriniz.';
    if (strlen(post('adres')) < 10)            $hatalar[] = 'Teslimat adresi giriniz.';
    if (strlen(post('il')) < 2)                $hatalar[] = 'İl seçiniz.';
    if (strlen(post('ilce')) < 2)              $hatalar[] = 'İlçe giriniz.';

    if ($odemeYontemi === 'kart') {
        $kartKullan = post('kayitli_kart_kullan');
        if ($kartKullan === '1' && $kaydedilmisKart) {
            $kartSon4 = $kaydedilmisKart['son4'];
        } else {
            $kartNoSade = preg_replace('/\s+/', '', post('kart_no'));
            if (strlen($kartNoSade) < 16 || !ctype_digit($kartNoSade)) $hatalar[] = 'Kart numarası 16 haneli olmalıdır.';
            if (strlen(post('kart_sahibi')) < 5)                       $hatalar[] = 'Kart sahibi adı soyadı giriniz.';
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', post('son_kullanim'))) $hatalar[] = 'Son kullanma AA/YY formatında olmalı.';
            if (!preg_match('/^\d{3}$/', post('cvv')))                 $hatalar[] = 'CVV 3 haneli olmalıdır.';
            $kartSon4 = isset($kartNoSade) && strlen($kartNoSade) >= 4 ? substr($kartNoSade, -4) : '0000';
            if (post('karti_kaydet') === '1' && empty($hatalar)) {
                $_SESSION['kayitli_kart'] = ['son4' => $kartSon4, 'sahip' => post('kart_sahibi'), 'tarih' => post('son_kullanim')];
            }
        }
        $durumKapida = false;
    } else {
        // Kapıda ödeme
        $kartSon4    = '0000';
        $durumKapida = true;
    }

    if (empty($hatalar)) {
        $taksit   = (!$durumKapida) ? (int)post('taksit', 1) : 1;
        $siparisM = new SiparisModel();
        // Kapıda ödemede durum 'bekliyor', kartta 'odendi'
        $r = $siparisM->olustur($kid, $kartSon4, $taksit, $durumKapida ? 'bekliyor' : 'odendi');
        if ($r['ok']) {
            $mesaj = $durumKapida
                ? 'Siparişiniz alındı! Kapıda ödeme seçtiniz. Sipariş No: #' . $r['siparis_id']
                : 'Ödemeniz başarıyla tamamlandı! Sipariş No: #' . $r['siparis_id'];
            Oturum::flash('success', $mesaj);
            header('Location: ' . url('siparis.php?id=' . $r['siparis_id']));
            exit();
        } else {
            $hatalar[] = $r['mesaj'];
        }
    }
}

$sayfaBasligi = 'Ödeme';
include __DIR__ . '/src/ui/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-11">
<h4 class="fw-bold mb-4"><i class="bi bi-shield-lock me-2 text-success"></i>Güvenli Ödeme</h4>

<?php if (!empty($hatalar)): ?>
<div class="alert alert-danger mb-4">
    <strong><i class="bi bi-exclamation-triangle me-1"></i>Hata:</strong>
    <ul class="mb-0 mt-1">
        <?php foreach ($hatalar as $h): ?><li><?= temizle($h) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST">
<div class="row g-4">
    <!-- Sol -->
    <div class="col-lg-7">

        <!-- Teslimat Adresi -->
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-geo-alt me-2 text-primary"></i>Teslimat Adresi</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Ad Soyad</label>
                    <input type="text" name="ad_soyad_teslimat" class="form-control" required
                           value="<?= temizle(post('ad_soyad_teslimat', Oturum::getAd())) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Telefon</label>
                    <input type="tel" name="telefon" class="form-control" required
                           placeholder="05XX XXX XX XX" value="<?= temizle(post('telefon')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Adres</label>
                    <textarea name="adres" class="form-control" rows="2" required
                              placeholder="Mahalle, sokak, bina no, daire no..."><?= temizle(post('adres')) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">İl</label>
                    <select name="il" class="form-select" required>
                        <option value="">İl seçin...</option>
                        <?php foreach (['Adana','Ankara','Antalya','Bursa','Denizli','Diyarbakır','Erzurum','Eskişehir','Gaziantep','Hatay','İstanbul','İzmir','Kayseri','Kocaeli','Konya','Malatya','Manisa','Mersin','Muğla','Samsun','Trabzon','Şanlıurfa'] as $il): ?>
                            <option value="<?= $il ?>" <?= post('il')===$il?'selected':'' ?>><?= $il ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">İlçe</label>
                    <input type="text" name="ilce" class="form-control" required placeholder="İlçe adı" value="<?= temizle(post('ilce')) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Posta Kodu</label>
                    <input type="text" name="posta_kodu" class="form-control" placeholder="34000" maxlength="5" value="<?= temizle(post('posta_kodu')) ?>">
                </div>
            </div>
        </div>

        <!-- Ödeme Yöntemi -->
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-4"><i class="bi bi-credit-card me-2 text-primary"></i>Ödeme Yöntemi</h5>

            <div class="d-flex gap-3 mb-4">
                <label class="border rounded-3 p-3 flex-fill d-flex align-items-center gap-2" style="cursor:pointer;">
                    <input type="radio" name="odeme_yontemi" value="kart" checked onchange="odemeYontemiDegis()">
                    <span class="fw-semibold"><i class="bi bi-credit-card me-1 text-primary"></i>Kredi/Banka Kartı</span>
                </label>
                <label class="border rounded-3 p-3 flex-fill d-flex align-items-center gap-2" style="cursor:pointer;">
                    <input type="radio" name="odeme_yontemi" value="kapida" onchange="odemeYontemiDegis()">
                    <span class="fw-semibold"><i class="bi bi-cash-coin me-1 text-success"></i>Kapıda Ödeme</span>
                </label>
            </div>

            <!-- Kart Bölümü -->
            <div id="kartBolumu">
                <?php if ($kaydedilmisKart): ?>
                <div class="alert alert-info py-2 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="kayitli_kart_kullan"
                               value="1" id="kayitliKartCB" onchange="kayitliKartToggle()">
                        <label class="form-check-label small fw-semibold" for="kayitliKartCB">
                            Kayıtlı kartımı kullan: •••• •••• •••• <?= temizle($kaydedilmisKart['son4']) ?>
                            (<?= temizle($kaydedilmisKart['sahip']) ?>)
                        </label>
                    </div>
                </div>
                <?php endif; ?>

                <div id="yeniKartAlani">
                    <!-- Kart Görseli -->
                    <div class="rounded-4 p-4 mb-4 text-white" style="background:linear-gradient(135deg,#1e3a8a,#7c3aed);min-height:165px;">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">VISA</span>
                            <span class="opacity-75 small">Kredi Kartı</span>
                        </div>
                        <div class="fs-5 fw-bold mb-3 font-monospace" id="kartNoG" style="letter-spacing:.2em;">•••• •••• •••• ••••</div>
                        <div class="d-flex justify-content-between">
                            <div><div class="small opacity-75">Kart Sahibi</div><div id="kartSahibiG">AD SOYAD</div></div>
                            <div class="text-end"><div class="small opacity-75">Son Kullanma</div><div id="sonKullanimG">AA/YY</div></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kart Numarası</label>
                        <input type="text" name="kart_no" id="kartNoI" class="form-control font-monospace" placeholder="0000 0000 0000 0000" maxlength="19">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kart Sahibi</label>
                        <input type="text" name="kart_sahibi" id="kartSahibiI" class="form-control" placeholder="AD SOYAD">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Son Kullanma (AA/YY)</label>
                            <input type="text" name="son_kullanim" id="sonKullanimI" class="form-control" placeholder="12/26" maxlength="5">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">CVV</label>
                            <div class="input-group">
                                <input type="password" name="cvv" id="cvvI" class="form-control" placeholder="•••" maxlength="3">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleCvv()"><i class="bi bi-eye" id="cvvGoz"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="karti_kaydet" value="1" id="kartiKaydetCB">
                        <label class="form-check-label small" for="kartiKaydetCB">
                            <i class="bi bi-bookmark me-1"></i>Bu kartı bir sonraki alışveriş için kaydet
                        </label>
                    </div>
                    <!-- Taksit -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Taksit Seçeneği</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center align-middle">
                                <thead class="table-light"><tr><th>Taksit</th><th>Aylık Tutar</th><th>Toplam</th><th></th></tr></thead>
                                <tbody id="taksitBody"></tbody>
                            </table>
                        </div>
                        <input type="hidden" name="taksit" id="taksitGizli" value="1">
                    </div>
                </div>
            </div>

            <!-- Kapıda Ödeme -->
            <div id="kapidaBolumu" style="display:none;">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Kapıda Ödeme</strong> seçtiniz.<br>
                    <small>Siparişiniz elinize ulaştığında nakit veya kartla ödeme yapabilirsiniz. Ek ücret yoktur.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sağ: Özet -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm p-4 sticky-top" style="top:80px;">
            <h5 class="fw-bold mb-3">Sipariş Özeti</h5>
            <table class="table table-sm mb-3">
                <?php foreach ($sepetItems as $item): ?>
                <tr>
                    <td><?= temizle($item['ad']) ?> <span class="badge bg-secondary">x<?= $item['adet'] ?></span></td>
                    <td class="text-end fw-semibold"><?= fiyat($item['ara_toplam']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr><td class="text-muted small">Kargo</td><td class="text-end text-success small">Ücretsiz</td></tr>
                <tr class="border-top fw-bold fs-6">
                    <td>Toplam</td>
                    <td class="text-end text-primary"><?= fiyat($toplam) ?></td>
                </tr>
            </table>
            <button type="submit" name="odeme_yap" class="btn btn-success w-100 py-2 fw-bold fs-6">
                <i class="bi bi-lock me-2"></i>Siparişi Tamamla — <?= fiyat($toplam) ?>
            </button>
            <a href="<?= url('sepet.php') ?>" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Sepete Dön
            </a>
            <hr>
            <div class="d-flex gap-2 align-items-center text-success small fw-semibold mb-1">
                <i class="bi bi-shield-check fs-5"></i>256-bit SSL Şifreli
            </div>
            <div class="d-flex gap-2 align-items-center text-muted small mb-1">
                <i class="bi bi-arrow-counterclockwise fs-5"></i>14 gün iade garantisi
            </div>
            <div class="d-flex gap-2 align-items-center text-muted small">
                <i class="bi bi-truck fs-5"></i>Ücretsiz kargo
            </div>
        </div>
    </div>
</div>
</form>
</div>
</div>

<?php
$toplamJs = (float)$toplam;
$sayfaJs = <<<JSEOF
var toplam = {$toplamJs};
var taksitler = [{sayi:1,faiz:0},{sayi:2,faiz:0.02},{sayi:3,faiz:0.04},{sayi:6,faiz:0.08},{sayi:9,faiz:0.12},{sayi:12,faiz:0.18}];
function taksitCiz(s) {
    var tb = document.getElementById('taksitBody');
    if (!tb) return;
    tb.innerHTML = '';
    taksitler.forEach(function(t) {
        var tot = toplam*(1+t.faiz), ayl = tot/t.sayi, ak = t.sayi===s;
        var tr = document.createElement('tr');
        tr.className = ak ? 'table-primary fw-semibold' : '';
        tr.innerHTML = '<td>'+(t.sayi===1?'Tek Çekim':t.sayi+' Taksit')+'</td>'
            +'<td>'+ayl.toFixed(2).replace('.',',')+' ₺</td>'
            +'<td>'+tot.toFixed(2).replace('.',',')+' ₺</td>'
            +'<td><button type="button" class="btn btn-sm '+(ak?'btn-primary':'btn-outline-primary')+'" onclick="taksitSec('+t.sayi+')">'+(ak?'✓ Seçildi':'Seç')+'</button></td>';
        tb.appendChild(tr);
    });
}
function taksitSec(s) { document.getElementById('taksitGizli').value=s; taksitCiz(s); }
taksitCiz(1);

var ki = document.getElementById('kartNoI');
if (ki) ki.addEventListener('input', function() {
    var v=this.value.replace(/\D/g,'').slice(0,16);
    this.value=v.replace(/(.{4})/g,'$1 ').trim();
    document.getElementById('kartNoG').textContent=v.padEnd(16,'•').replace(/(.{4})/g,'$1 ').trim();
});
var ks = document.getElementById('kartSahibiI');
if (ks) ks.addEventListener('input', function() { document.getElementById('kartSahibiG').textContent=this.value.toUpperCase()||'AD SOYAD'; });
var sk = document.getElementById('sonKullanimI');
if (sk) sk.addEventListener('input', function() {
    var v=this.value.replace(/\D/g,'');
    if(v.length>=2) v=v.slice(0,2)+'/'+v.slice(2,4);
    this.value=v;
    document.getElementById('sonKullanimG').textContent=v||'AA/YY';
});
function toggleCvv() {
    var i=document.getElementById('cvvI'),g=document.getElementById('cvvGoz');
    i.type=i.type==='password'?'text':'password';
    g.className=i.type==='password'?'bi bi-eye':'bi bi-eye-slash';
}
function odemeYontemiDegis() {
    var kart=document.querySelector('[name=odeme_yontemi][value=kart]').checked;
    document.getElementById('kartBolumu').style.display=kart?'':'none';
    document.getElementById('kapidaBolumu').style.display=kart?'none':'';
}
function kayitliKartToggle() {
    var cb=document.getElementById('kayitliKartCB');
    if(cb) document.getElementById('yeniKartAlani').style.display=cb.checked?'none':'';
}
JSEOF;
include __DIR__ . '/src/ui/footer.php';
?>
