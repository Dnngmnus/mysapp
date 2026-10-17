<?php
require __DIR__ . '/penyaluran_lib.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function stat_row(PDO $pdo, string $where, array $args): array {
    $st = $pdo->prepare('SELECT COUNT(*) AS n, COALESCE(SUM(tabung),0) AS tabung, COALESCE(SUM(kg),0) AS kg FROM penyaluran' . $where);
    $st->execute($args);
    $r = $st->fetch();
    return ['n' => (int)$r['n'], 'tabung' => (int)$r['tabung'], 'kg' => (int)$r['kg']];
}

$me = null;
try {
    $me = current_user();
    if (!$me) json_out(['error' => 'Sesi berakhir, silakan login lagi'], 401);
    $isAdmin = $me['role'] === 'admin';
    $pdo = db();
    $act = $_GET['action'] ?? 'list';
    $method = $_SERVER['REQUEST_METHOD'];

    if (in_array($act, ['save', 'delete'], true)) {
        if ($method !== 'POST') json_out(['error' => 'Gunakan POST'], 405);
        if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
            json_out(['error' => 'Token keamanan tidak valid. Muat ulang halaman.'], 403);
        }
    }
    $in = json_decode(file_get_contents('php://input'), true) ?: [];

    // Boleh ubah/hapus: master admin, atau petugas yang menginput data itu
    $boleh = function (array $row) use ($me, $isAdmin): bool {
        return $isAdmin || ((int)$row['user_id'] === (int)$me['id']);
    };

    switch ($act) {

    case 'list':
        $where = []; $args = [];
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where[] = '(agen LIKE ? OR plat LIKE ? OR sopir LIKE ?)';
            array_push($args, $like, $like, $like);
        }
        $dari = (string)($_GET['dari'] ?? '');
        $sampai = (string)($_GET['sampai'] ?? '');
        if (tgl_valid($dari))   { $where[] = 'tanggal >= ?'; $args[] = $dari; }
        if (tgl_valid($sampai)) { $where[] = 'tanggal <= ?'; $args[] = $sampai; }
        $w = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $st = $pdo->prepare('SELECT id, user_id, tanggal, agen, plat, tabung, kg, sopir FROM penyaluran'
            . $w . ' ORDER BY tanggal DESC, id DESC LIMIT 1000');
        $st->execute($args);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['can'] = $boleh($r);
            unset($r['user_id']);
        }
        unset($r);

        $today = hari_ini();
        json_out([
            'rows'   => $rows,
            'filter' => stat_row($pdo, $w, $args),
            'today'  => stat_row($pdo, ' WHERE tanggal = ?', [$today]) + ['tanggal' => $today],
        ]);

    case 'save':
        $id = (int)($in['id'] ?? 0);
        $tanggal = (string)($in['tanggal'] ?? '');
        if (!tgl_valid($tanggal)) json_out(['error' => 'Tanggal tidak valid.'], 422);

        $agen = preg_replace('/\s+/u', ' ', trim((string)($in['agen'] ?? '')));
        if ($agen === null || $agen === '') json_out(['error' => 'Pilih agen dulu.'], 422);

        $tabung = (int)($in['tabung'] ?? 0);
        if (!in_array($tabung, PENYALURAN_TABUNG, true)) {
            json_out(['error' => 'Total tabung harus salah satu dari: ' . implode(', ', PENYALURAN_TABUNG) . '.'], 422);
        }

        $plat = strtoupper(preg_replace('/\s+/', ' ', trim((string)($in['plat'] ?? ''))));
        if ($plat !== '' && !preg_match('/^[A-Z0-9 -]{3,15}$/', $plat)) {
            json_out(['error' => 'Plat nomor hanya boleh huruf, angka, spasi (3-15 karakter).'], 422);
        }

        $sopir = preg_replace('/\s+/u', ' ', trim((string)($in['sopir'] ?? '')));
        if ($sopir === null || !preg_match('/^.{2,100}$/us', $sopir)) {
            json_out(['error' => 'Nama sopir 2-100 karakter.'], 422);
        }

        $kg = $tabung * KG_PER_TABUNG;

        $lama = null;
        if ($id) {
            $st = $pdo->prepare('SELECT id, user_id, agen FROM penyaluran WHERE id = ?');
            $st->execute([$id]);
            $lama = $st->fetch();
            if (!$lama) json_out(['error' => 'Data tidak ditemukan.'], 404);
            if (!$boleh($lama)) json_out(['error' => 'Anda tidak boleh mengubah data ini.'], 403);
        }

        // Agen harus ada di List Agen (kecuali tidak diganti saat edit data lama)
        if (!$lama || $lama['agen'] !== $agen) {
            $st = $pdo->prepare('SELECT 1 FROM agen WHERE nama = ?');
            $st->execute([$agen]);
            if (!$st->fetchColumn()) json_out(['error' => 'Agen tidak ada di List Agen.'], 422);
        }

        if ($lama) {
            $pdo->prepare('UPDATE penyaluran SET tanggal=?, agen=?, plat=?, tabung=?, kg=?, sopir=? WHERE id=?')
                ->execute([$tanggal, $agen, $plat, $tabung, $kg, $sopir, $id]);
        } else {
            $pdo->prepare('INSERT INTO penyaluran (user_id, tanggal, agen, plat, tabung, kg, sopir) VALUES (?,?,?,?,?,?,?)')
                ->execute([(int)$me['id'], $tanggal, $agen, $plat, $tabung, $kg, $sopir]);
        }
        json_out(['ok' => true]);

    case 'delete':
        $id = (int)($in['id'] ?? 0);
        $st = $pdo->prepare('SELECT id, user_id FROM penyaluran WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) json_out(['error' => 'Data tidak ditemukan.'], 404);
        if (!$boleh($row)) json_out(['error' => 'Anda tidak boleh menghapus data ini.'], 403);
        $pdo->prepare('DELETE FROM penyaluran WHERE id = ?')->execute([$id]);
        json_out(['ok' => true]);

    default:
        json_out(['error' => 'Aksi tidak dikenal'], 400);
    }
} catch (Throwable $e) {
    error_log('[pertamina] ' . $e->getMessage());
    $msg = ($me && $me['role'] === 'admin') ? 'Server error: ' . $e->getMessage() : 'Terjadi kesalahan pada server';
    json_out(['error' => $msg], 500);
}
