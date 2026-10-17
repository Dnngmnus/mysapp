<?php
require __DIR__ . '/dokumen_lib.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

const MEDIA_FOTO_MAX  = 8 * 1024 * 1024;
const MEDIA_VIDEO_MAX = 50 * 1024 * 1024;

function json_out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$me = null;
try {
    $me = current_user();
    if (!$me) json_out(['error' => 'Sesi berakhir, silakan login lagi'], 401);
    if ($me['role'] !== 'admin') json_out(['error' => 'Hanya master admin yang boleh mengubah media'], 403);
    $pdo = db();
    $act = $_GET['action'] ?? 'list';
    $method = $_SERVER['REQUEST_METHOD'];

    if (in_array($act, ['upload', 'delete'], true) && $method !== 'POST') json_out(['error' => 'Gunakan POST'], 405);
    if ($method === 'POST' && !csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
        json_out(['error' => 'Token keamanan tidak valid. Muat ulang halaman.'], 403);
    }
    $dir = UPLOAD_DIR . '/media';

    switch ($act) {

    case 'list':
        json_out($pdo->query('SELECT id, jenis, judul, ukuran, created_at FROM media ORDER BY urutan, id')->fetchAll());

    case 'upload':
        if (empty($_FILES['berkas'])) {
            json_out(['error' => 'Tidak ada file yang diterima. Kemungkinan ukurannya melebihi batas upload server.'], 400);
        }
        $f = $_FILES['berkas'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $pesan = in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'Ukuran file melebihi batas server (naikkan upload_max_filesize dan post_max_size di php.ini).'
                : 'Upload gagal (kode ' . (int)$f['error'] . ').';
            json_out(['error' => $pesan], 400);
        }
        if (!is_uploaded_file($f['tmp_name'])) json_out(['error' => 'Upload tidak valid.'], 400);

        $raw = basename(str_replace('\\', '/', (string)$f['name']));
        $ext = strtolower(pathinfo($raw, PATHINFO_EXTENSION));
        $fotoTipe = ['jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG, 'webp' => IMAGETYPE_WEBP];

        if (isset($fotoTipe[$ext])) {
            if ($f['size'] > MEDIA_FOTO_MAX) json_out(['error' => 'Ukuran foto maksimal 8 MB.'], 413);
            $info = @getimagesize($f['tmp_name']);
            if (!$info || $info[2] !== $fotoTipe[$ext]) json_out(['error' => 'Isi file bukan gambar yang valid.'], 422);
            $jenis = 'foto';
            $mime = image_type_to_mime_type($info[2]);
        } elseif ($ext === 'mp4' || $ext === 'webm') {
            if ($f['size'] > MEDIA_VIDEO_MAX) json_out(['error' => 'Ukuran video maksimal 50 MB. Kompres dulu videonya.'], 413);
            $fh = @fopen($f['tmp_name'], 'rb');
            $head = $fh ? (string)fread($fh, 12) : '';
            if ($fh) fclose($fh);
            $okMp4  = $ext === 'mp4'  && substr($head, 4, 4) === 'ftyp';
            $okWebm = $ext === 'webm' && strncmp($head, "\x1A\x45\xDF\xA3", 4) === 0;
            if (!$okMp4 && !$okWebm) json_out(['error' => 'Isi file bukan video ' . strtoupper($ext) . ' yang valid.'], 422);
            $jenis = 'video';
            $mime = $ext === 'mp4' ? 'video/mp4' : 'video/webm';
        } else {
            json_out(['error' => 'Jenis file tidak diizinkan. Foto: JPG, PNG, WebP. Video: MP4, WebM.'], 422);
        }

        $judul = dok_potong(trim((string)($_POST['judul'] ?? '')), 150);

        if (!is_dir($dir)) @mkdir($dir, 0750, true);
        if (!is_dir($dir) || !is_writable($dir)) {
            error_log('[pertamina] folder media tidak bisa dibuat/ditulis: ' . $dir);
            json_out(['error' => 'Folder penyimpanan media di server belum siap. Cek izin folder ' . UPLOAD_DIR], 500);
        }
        $nama = bin2hex(random_bytes(16));
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $nama)) {
            json_out(['error' => 'Gagal menyimpan file di server.'], 500);
        }
        try {
            $st = $pdo->prepare('INSERT INTO media (jenis, nama_file, mime, judul, ukuran) VALUES (?,?,?,?,?)');
            $st->execute([$jenis, $nama, $mime, $judul, (int)$f['size']]);
        } catch (Throwable $e) {
            @unlink($dir . '/' . $nama);
            throw $e;
        }
        json_out(['ok' => true]);

    case 'delete':
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int)($in['id'] ?? 0);
        $st = $pdo->prepare('SELECT nama_file FROM media WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) json_out(['error' => 'Media tidak ditemukan'], 404);
        $path = $dir . '/' . $row['nama_file'];
        if (is_file($path)) @unlink($path);
        $pdo->prepare('DELETE FROM media WHERE id = ?')->execute([$id]);
        json_out(['ok' => true]);

    default:
        json_out(['error' => 'Aksi tidak dikenal'], 400);
    }
} catch (Throwable $e) {
    error_log('[pertamina] ' . $e->getMessage());
    json_out(['error' => 'Server error: ' . $e->getMessage()], 500);
}
