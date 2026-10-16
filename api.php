<?php
require __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$me = null;
try {
    $me = current_user();
    if (!$me) out(['error' => 'Sesi berakhir, silakan login lagi'], 401);
    $isAdmin = $me['role'] === 'admin';
    $myId = (int)$me['id'];
    $pdo = db();
    $in  = json_decode(file_get_contents('php://input'), true) ?: [];
    $act = $_GET['action'] ?? '';

    $tulis = ['bbm_update', 'save', 'delete', 'user_set', 'user_reset', 'user_delete'];
    $method = $_SERVER['REQUEST_METHOD'];
    if (in_array($act, $tulis, true) && $method !== 'POST') out(['error' => 'Gunakan POST'], 405);
    if ($method === 'POST' && !csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
        out(['error' => 'Token keamanan tidak valid. Muat ulang halaman.'], 403);
    }
    if ((strpos($act, 'user') === 0 || $act === 'bbm_update') && !$isAdmin) {
        out(['error' => 'Hanya master admin yang boleh melakukan ini'], 403);
    }

    switch ($act) {

    case 'bbm':
        out($pdo->query('SELECT id, nama, harga FROM bbm ORDER BY id')->fetchAll());

    case 'bbm_update':
        $st = $pdo->prepare('UPDATE bbm SET harga = ? WHERE id = ?');
        foreach (($in['items'] ?? []) as $it) {
            $st->execute([max(0, (int)($it['harga'] ?? 0)), (int)($it['id'] ?? 0)]);
        }
        out(['ok' => true]);

    case 'list':
        $where = []; $p = [];
        if (!$isAdmin) { $where[] = 't.user_id = ?'; $p[] = $myId; }
        if (!empty($_GET['q'])) {
            $where[] = '(t.pelanggan LIKE ? OR t.plat LIKE ?)';
            $p[] = '%' . $_GET['q'] . '%'; $p[] = '%' . $_GET['q'] . '%';
        }
        if (!empty($_GET['dari']))   { $where[] = 't.tanggal >= ?'; $p[] = $_GET['dari']; }
        if (!empty($_GET['sampai'])) { $where[] = 't.tanggal <= ?'; $p[] = $_GET['sampai']; }
        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $st = $pdo->prepare("SELECT t.*, b.nama AS bbm, u.nama AS petugas
                             FROM transaksi t
                             JOIN bbm b ON b.id = t.bbm_id
                             LEFT JOIN users u ON u.id = t.user_id
                             $w ORDER BY t.tanggal DESC, t.id DESC LIMIT 500");
        $st->execute($p);
        $rows = $st->fetchAll();

        $st = $pdo->prepare("SELECT COUNT(*) n, COALESCE(SUM(liter),0) liter,
                             COALESCE(SUM(total),0) total FROM transaksi t $w");
        $st->execute($p);
        out(['rows' => $rows, 'stat' => $st->fetch()]);

    case 'save':
        $tgl   = $in['tanggal'] ?? '';
        $nama  = trim($in['pelanggan'] ?? '');
        $plat  = strtoupper(trim($in['plat'] ?? ''));
        $bbm   = (int)($in['bbm_id'] ?? 0);
        $liter = (float)($in['liter'] ?? 0);
        $harga = (int)($in['harga'] ?? 0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) || $nama === '' || $bbm < 1 || $liter <= 0 || $harga < 0)
            out(['error' => 'Data tidak valid'], 422);

        $total = (int)round($liter * $harga);
        if (!empty($in['id'])) {
            $st = $pdo->prepare('SELECT user_id FROM transaksi WHERE id = ?');
            $st->execute([(int)$in['id']]);
            $row = $st->fetch();
            if (!$row) out(['error' => 'Data tidak ditemukan'], 404);
            if (!$isAdmin && (int)$row['user_id'] !== $myId) out(['error' => 'Tidak boleh mengubah data milik orang lain'], 403);

            $st = $pdo->prepare('UPDATE transaksi SET tanggal=?, pelanggan=?, plat=?, bbm_id=?, liter=?, harga=?, total=? WHERE id=?');
            $st->execute([$tgl, $nama, $plat, $bbm, $liter, $harga, $total, (int)$in['id']]);
        } else {
            $st = $pdo->prepare('INSERT INTO transaksi (tanggal, pelanggan, plat, bbm_id, liter, harga, total, user_id) VALUES (?,?,?,?,?,?,?,?)');
            $st->execute([$tgl, $nama, $plat, $bbm, $liter, $harga, $total, $myId]);
        }
        out(['ok' => true]);

    case 'delete':
        $id = (int)($in['id'] ?? 0);
        $st = $pdo->prepare('SELECT user_id FROM transaksi WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) out(['error' => 'Data tidak ditemukan'], 404);
        if (!$isAdmin && (int)$row['user_id'] !== $myId) out(['error' => 'Tidak boleh menghapus data milik orang lain'], 403);
        $pdo->prepare('DELETE FROM transaksi WHERE id = ?')->execute([$id]);
        out(['ok' => true]);

    case 'users':
        out($pdo->query("SELECT id, username, nama, role, status, created_at FROM users
                         ORDER BY FIELD(status,'pending','aktif','nonaktif'), id")->fetchAll());

    case 'user_set':
        $id = (int)($in['id'] ?? 0);
        if ($id === $myId) out(['error' => 'Tidak bisa mengubah akun sendiri'], 400);
        $sets = []; $p = [];
        if (isset($in['role'])) {
            if (!in_array($in['role'], ['admin', 'user'], true)) out(['error' => 'Peran tidak valid'], 422);
            $sets[] = 'role = ?'; $p[] = $in['role'];
        }
        if (isset($in['status'])) {
            if (!in_array($in['status'], ['aktif', 'pending', 'nonaktif'], true)) out(['error' => 'Status tidak valid'], 422);
            $sets[] = 'status = ?'; $p[] = $in['status'];
        }
        if (!$sets) out(['error' => 'Tidak ada perubahan'], 422);
        $p[] = $id;
        $pdo->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($p);
        out(['ok' => true]);

    case 'user_reset':
        $id = (int)($in['id'] ?? 0);
        if ($id === $myId) out(['error' => 'Ganti password sendiri lewat menu Akun'], 400);
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $pw = '';
        for ($i = 0; $i < 10; $i++) $pw .= $chars[random_int(0, strlen($chars) - 1)];
        $pdo->prepare('UPDATE users SET password_hash = ?, gagal_login = 0, terkunci_sampai = NULL WHERE id = ?')
            ->execute([password_hash($pw, PASSWORD_DEFAULT), $id]);
        out(['password' => $pw]);

    case 'user_delete':
        $id = (int)($in['id'] ?? 0);
        if ($id === $myId) out(['error' => 'Tidak bisa menghapus akun sendiri'], 400);
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE transaksi SET user_id = NULL WHERE user_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $pdo->commit();
        out(['ok' => true]);

    default:
        out(['error' => 'Aksi tidak dikenal'], 400);
    }
} catch (Throwable $e) {
    error_log('[pertamina] ' . $e->getMessage());
    $msg = ($me && $me['role'] === 'admin') ? 'Server error: ' . $e->getMessage() : 'Terjadi kesalahan pada server';
    out(['error' => $msg], 500);
}
