<?php
require __DIR__ . '/dokumen_lib.php';
$me = require_login();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM dokumen WHERE id = ?');
$st->execute([$id]);
$row = $st->fetch();

if (!$row || ($me['role'] !== 'admin' && (int)$row['user_id'] !== (int)$me['id'])) {
    http_response_code(404);
    exit('Dokumen tidak ditemukan.');
}
$path = UPLOAD_DIR . '/' . $row['nama_file'];
if (!is_file($path)) {
    http_response_code(404);
    exit('File tidak ada di server.');
}

session_write_close();
while (ob_get_level()) ob_end_clean();

$fallback = preg_replace('/[^A-Za-z0-9._-]+/', '_', $row['nama_asli']);
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($row['nama_asli']));
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, no-store');
readfile($path);
