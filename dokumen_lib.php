<?php
require_once __DIR__ . '/auth.php';

// Folder penyimpanan di LUAR folder web, jadi file tidak bisa dibuka lewat URL langsung.
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', '/var/www/pertamina_uploads');
const DOK_MAX_BYTES = 20 * 1024 * 1024;
const DOK_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'];

// Cek isi file sesuai jenisnya (bukan hanya melihat ekstensi)
function dok_magic_ok(string $path, string $ext): bool {
    $f = @fopen($path, 'rb');
    if (!$f) return false;
    $head = (string)fread($f, 4096);
    fclose($f);

    switch ($ext) {
        case 'pdf':
            return strpos(substr($head, 0, 1024), '%PDF') !== false;
        case 'docx':
        case 'xlsx':
            return strncmp($head, "PK\x03\x04", 4) === 0;
        case 'doc':
        case 'xls':
            return strncmp($head, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", 8) === 0;
        case 'csv':
            return strpos($head, "\0") === false;
    }
    return false;
}

// Potong teks sesuai jumlah karakter (aman untuk UTF-8). Mengembalikan '' kalau bukan UTF-8 valid.
function dok_potong(string $s, int $n): string {
    return preg_match('/^.{0,' . $n . '}/us', $s, $m) ? $m[0] : '';
}
