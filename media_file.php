<?php
// Menyajikan foto/video untuk halaman depan (publik, tanpa login). Mendukung Range supaya video bisa di-seek.
require __DIR__ . '/config.php';
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', '/var/www/pertamina_uploads');

$id = (int)($_GET['id'] ?? 0);
try {
    $st = db()->prepare('SELECT nama_file, mime FROM media WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
} catch (Throwable $e) {
    $row = false;
}
$path = $row ? UPLOAD_DIR . '/media/' . $row['nama_file'] : '';
if (!$row || !is_file($path)) {
    http_response_code(404);
    exit('Tidak ditemukan.');
}

$size  = filesize($path);
$start = 0;
$end   = $size - 1;
$code  = 200;

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m)
    && !($m[1] === '' && $m[2] === '')) {
    if ($m[1] === '') {
        $start = max(0, $size - (int)$m[2]);
    } else {
        $start = (int)$m[1];
        if ($m[2] !== '') $end = min((int)$m[2], $size - 1);
    }
    if ($start > $end || $start >= $size) {
        http_response_code(416);
        header('Content-Range: bytes */' . $size);
        exit;
    }
    $code = 206;
}

while (ob_get_level()) ob_end_clean();
set_time_limit(0);
http_response_code($code);
header('Content-Type: ' . $row['mime']);
header('X-Content-Type-Options: nosniff');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=86400');
if ($code === 206) header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
header('Content-Length: ' . ($end - $start + 1));

$fp = fopen($path, 'rb');
fseek($fp, $start);
$left = $end - $start + 1;
while ($left > 0 && !feof($fp)) {
    $chunk = fread($fp, min(131072, $left));
    if ($chunk === false || $chunk === '') break;
    echo $chunk;
    $left -= strlen($chunk);
    flush();
}
fclose($fp);
