<?php
require __DIR__ . '/dokumen_lib.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$me = null;
try {
    $me = current_user();
    if (!$me) json_out(['error' => 'Sesi berakhir, silakan login lagi'], 401);
    $isAdmin = $me['role'] === 'admin';
    $myId = (int)$me['id'];
    $pdo = db();
    $act = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    if (in_array($act, ['upload', 'delete'], true) && $method !== 'POST') {
        json_out(['error' => 'Gunakan POST'], 405);
    }
    if ($method === 'POST' && !csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
        json_out(['error' => 'Token keamanan tidak valid. Muat ulang halaman.'], 403);
    }

    switch ($act) {

    case 'list':
        $where = []; $p = [];
        if (!$isAdmin) { $where[] = 'd.user_id = ?'; $p[] = $myId; }
        if (!empty($_GET['q'])) {
            $where[] = '(d.judul LIKE ? OR d.nama_asli LIKE ?)';
            $p[] = '%' . $_GET['q'] . '%'; $p[] = '%' . $_GET['q'] . '%';
        }
        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $st = $pdo->prepare("SELECT d.id, d.judul, d.nama_asli, d.ekstensi, d.ukuran, d.created_at,
                                    u.nama AS pemilik
                             FROM dokumen d LEFT JOIN users u ON u.id = d.user_id
                             $w ORDER BY d.id DESC LIMIT 500");
        $st->execute($p);
        json_out($st->fetchAll());

    case 'upload':
        if (empty($_FILES['berkas'])) {
            json_out(['error' => 'Tidak ada file yang diterima. Kemungkinan ukurannya melebihi batas upload server.'], 400);
        }
        $f = $_FILES['berkas'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $pesan = in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'Ukuran file melebihi batas server (naikkan upload_max_filesize di php.ini).'
                : 'Upload gagal (kode ' . (int)$f['error'] . ').';
            json_out(['error' => $pesan], 400);
        }
        if ($f['size'] > DOK_MAX_BYTES) json_out(['error' => 'Ukuran file maksimal 20 MB.'], 413);

        $raw = basename(str_replace('\\', '/', (string)$f['name']));
        $ext = strtolower(pathinfo($raw, PATHINFO_EXTENSION));
        if (!in_array($ext, DOK_EXT, true)) {
            json_out(['error' => 'Jenis file tidak diizinkan. Gunakan: ' . implode(', ', DOK_EXT) . '.'], 422);
        }
        if (!is_uploaded_file($f['tmp_name']) || !dok_magic_ok($f['tmp_name'], $ext)) {
            json_out(['error' => 'Isi file tidak sesuai dengan ekstensinya.'], 422);
        }

        $asli = dok_potong(preg_replace('/[\x00-\x1F\x7F]/', '', $raw), 255);
        if ($asli === '') $asli = 'dokumen.' . $ext;
        $judul = dok_potong(trim((string)($_POST['judul'] ?? '')), 150);
        if ($judul === '') $judul = dok_potong(pathinfo($asli, PATHINFO_FILENAME), 150);
        if ($judul === '') $judul = 'Dokumen';

        if (!is_dir(UPLOAD_DIR) || !is_writable(UPLOAD_DIR)) {
            error_log('[pertamina] UPLOAD_DIR tidak ada / tidak bisa ditulis: ' . UPLOAD_DIR);
            json_out(['error' => 'Folder penyimpanan di server belum siap. Hubungi admin.'], 500);
        }

        $nama = bin2hex(random_bytes(16));
        if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $nama)) {
            json_out(['error' => 'Gagal menyimpan file di server.'], 500);
        }
        try {
            $st = $pdo->prepare('INSERT INTO dokumen (user_id, judul, nama_asli, nama_file, ekstensi, ukuran) VALUES (?,?,?,?,?,?)');
            $st->execute([$myId, $judul, $asli, $nama, $ext, (int)$f['size']]);
        } catch (Throwable $e) {
            @unlink(UPLOAD_DIR . '/' . $nama);
            throw $e;
        }
        json_out(['ok' => true]);

    case 'delete':
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int)($in['id'] ?? 0);
        $st = $pdo->prepare('SELECT user_id, nama_file FROM dokumen WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) json_out(['error' => 'Dokumen tidak ditemukan'], 404);
        if (!$isAdmin && (int)$row['user_id'] !== $myId) {
            json_out(['error' => 'Tidak boleh menghapus dokumen milik orang lain'], 403);
        }
        $path = UPLOAD_DIR . '/' . $row['nama_file'];
        if (is_file($path)) @unlink($path);
        $pdo->prepare('DELETE FROM dokumen WHERE id = ?')->execute([$id]);
        json_out(['ok' => true]);

    default:
        json_out(['error' => 'Aksi tidak dikenal'], 400);
    }
} catch (Throwable $e) {
    error_log('[pertamina] ' . $e->getMessage());
    $msg = ($me && $me['role'] === 'admin') ? 'Server error: ' . $e->getMessage() : 'Terjadi kesalahan pada server';
    json_out(['error' => $msg], 500);
}
