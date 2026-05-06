<?php
/**
 * src/utils/yardimcilar.php
 * Projede kullanılan genel yardımcı fonksiyonlar.
 */

/** XSS koruması */
function temizle(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Para formatı */
function fiyat(float $f): string {
    return number_format($f, 2, ',', '.') . ' ₺';
}

/** Tarih formatı */
function tarih(string $t): string {
    return date('d.m.Y H:i', strtotime($t));
}

/** Yıldız HTML */
function yildizlar(float $puan, bool $girisli = false, int $urunId = 0): string {
    $html = '<span class="yildiz-grup" ' . ($girisli ? 'data-urun="'.$urunId.'"' : '') . '>';
    for ($i = 1; $i <= 5; $i++) {
        $dolu  = $i <= round($puan);
        $renk  = $dolu ? 'text-warning' : 'text-muted';
        $html .= '<i class="bi bi-star-fill ' . $renk . ' fs-5"></i>';
    }
    $html .= '</span>';
    return $html;
}

/** POST değeri al */
function post(string $k, string $varsayilan = ''): string {
    return isset($_POST[$k]) ? trim($_POST[$k]) : $varsayilan;
}

/** GET değeri al */
function get(string $k, string $varsayilan = ''): string {
    return isset($_GET[$k]) ? trim($_GET[$k]) : $varsayilan;
}

/** AJAX isteği mi? */
function ajaxMi(): bool {
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

/** JSON cevap gönder ve çık */
function jsonCevap(array $veri, int $kod = 200): never {
    http_response_code($kod);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($veri, JSON_UNESCAPED_UNICODE);
    exit();
}

/** Base URL */
function url(string $yol = ''): string {
    $base = '';
    return $base . '/' . ltrim($yol, '/');
}

/** Taksit tutarı hesapla */
function taksitTutari(float $toplam, int $taksit): float {
    $faizOrani = [1 => 0, 2 => 0.02, 3 => 0.04, 6 => 0.08, 9 => 0.12, 12 => 0.18];
    $faiz = $faizOrani[$taksit] ?? 0;
    return ($toplam * (1 + $faiz)) / $taksit;
}
