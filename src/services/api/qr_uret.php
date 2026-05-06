<?php
/**
 * api/qr_uret.php
 * QR kod üretir ve base64 PNG olarak döndürür.
 * Python qrcode kütüphanesi kullanır.
 * 
 * Kullanım: api/qr_uret.php?veri=URL_VEYA_METIN&boyut=200
 */

// Hata gösterme
error_reporting(0);

$veri  = $_GET['veri'] ?? '';
$boyut = max(100, min(400, (int)($_GET['boyut'] ?? 200)));

if (empty($veri)) {
    http_response_code(400);
    exit('Veri eksik');
}

// Python scripti çalıştır
$pythonScript = __DIR__ . '/../src/services/qr_uret.py';
$veriEsc      = escapeshellarg($veri);
$boyutEsc     = escapeshellarg($boyut);

$cikti = shell_exec("python3 $pythonScript $veriEsc $boyutEsc 2>/dev/null");

if ($cikti && strlen($cikti) > 50) {
    // base64 PNG döndür
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=3600');
    echo base64_decode($cikti);
} else {
    // Python çalışmadıysa basit PNG placeholder
    http_response_code(500);
    exit('QR üretilemedi');
}
