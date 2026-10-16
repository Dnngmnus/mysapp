<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

function out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
    $in  = json_decode(file_get_contents('php://input'), true) ?: [];
    $act = $_GET['action'] ?? '';

    switch ($act) {

    case 'bbm':
        out($pdo->query('SELECT id, nama, harga FROM bbm ORDER BY id')->fetchAll());

    case 'bbm_update':
        $st = $pdo->prepare('UPDATE bbm SET harga = ? WHERE id = ?');
        foreach (($in['items'] ?? []) as $it) {
            $st->execute([max(0, (int)$it['harga']), (int)$it['id']]);
        }
        out(['ok' => true]);

    case 'list':
        $where = []; $p = [];
        if (!empty($_GET['q'])) {
            $where[] = '(t.pelanggan LIKE ? OR t.plat LIKE ?)';
            $p[] = '%' . $_GET['q'] . '%'; $p[] = '%' . $_GET['q'] . '%';
        }
        if (!empty($_GET['dari']))   { $where[] = 't.tanggal >= ?'; $p[] = $_GET['dari']; }
        if (!empty($_GET['sampai'])) { $where[] = 't.tanggal <= ?'; $p[] = $_GET['sampai']; }
        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $st = $pdo->prepare("SELECT t.*, b.nama AS bbm FROM transaksi t
                             JOIN bbm b ON b.id = t.bbm_id $w
                             ORDER BY t.tanggal DESC, t.id DESC LIMIT 500");
        $st->execute($p);
        $rows = $st->fetchAll();

        $st = $pdo->prepare("SELECT COUNT(*) n, COALESCE(SUM(liter),0) liter,
                             COALESCE(SUM(total),0) total FROM transaksi t $w");
        $st->execute($p);
        out(['rows' => $rows, 'stat' => $st->fetch()]);

    case 'save':
        $tgl = $in['tanggal'] ?? '';
        $nama = trim($in['pelanggan'] ?? '');
        $plat = strtoupper(trim($in['plat'] ?? ''));
        $bbm = (int)($in['bbm_id'] ?? 0);
        $liter = (float)($in['liter'] ?? 0);
        $harga = (int)($in['harga'] ?? 0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl) || $nama === '' || $bbm < 1 || $liter <= 0 || $harga < 0)
            out(['error' => 'Data tidak valid'], 422);

        $total = (int)round($liter * $harga);
        if (!empty($in['id'])) {
            $st = $pdo->prepare('UPDATE transaksi SET tanggal=?, pelanggan=?, plat=?, bbm_id=?, liter=?, harga=?, total=? WHERE id=?');
            $st->execute([$tgl, $nama, $plat, $bbm, $liter, $harga, $total, (int)$in['id']]);
        } else {
            $st = $pdo->prepare('INSERT INTO transaksi (tanggal, pelanggan, plat, bbm_id, liter, harga, total) VALUES (?,?,?,?,?,?,?)');
            $st->execute([$tgl, $nama, $plat, $bbm, $liter, $harga, $total]);
        }
        out(['ok' => true]);

    case 'delete':
        $st = $pdo->prepare('DELETE FROM transaksi WHERE id = ?');
        $st->execute([(int)($in['id'] ?? 0)]);
        out(['ok' => true]);

    default:
        out(['error' => 'Aksi tidak dikenal'], 400);
    }
} catch (Throwable $e) {
    out(['error' => 'Server error: ' . $e->getMessage()], 500);
}
