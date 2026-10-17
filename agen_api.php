<?php
require __DIR__ . '/auth.php';
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
    $pdo = db();
    $act = $_GET['action'] ?? 'list';
    $method = $_SERVER['REQUEST_METHOD'];

    if (in_array($act, ['add', 'delete'], true)) {
        if ($method !== 'POST') json_out(['error' => 'Gunakan POST'], 405);
        if (!$isAdmin) json_out(['error' => 'Hanya master admin yang boleh mengubah List Agen'], 403);
    }
    if ($method === 'POST' && !csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
        json_out(['error' => 'Token keamanan tidak valid. Muat ulang halaman.'], 403);
    }
    $in = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($act) {

    case 'list':
        json_out($pdo->query('SELECT id, nama FROM agen ORDER BY nama')->fetchAll());

    case 'add':
        $nama = preg_replace('/\s+/u', ' ', trim((string)($in['nama'] ?? '')));
        if ($nama === null || !preg_match('/^.{2,100}$/us', $nama)) {
            json_out(['error' => 'Nama agen 2-100 karakter.'], 422);
        }
        try {
            $pdo->prepare('INSERT INTO agen (nama) VALUES (?)')->execute([$nama]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) == 1062) json_out(['error' => 'Nama agen sudah ada.'], 409);
            throw $e;
        }
        json_out(['ok' => true]);

    case 'delete':
        $pdo->prepare('DELETE FROM agen WHERE id = ?')->execute([(int)($in['id'] ?? 0)]);
        json_out(['ok' => true]);

    default:
        json_out(['error' => 'Aksi tidak dikenal'], 400);
    }
} catch (Throwable $e) {
    error_log('[pertamina] ' . $e->getMessage());
    $msg = ($me && $me['role'] === 'admin') ? 'Server error: ' . $e->getMessage() : 'Terjadi kesalahan pada server';
    json_out(['error' => $msg], 500);
}
