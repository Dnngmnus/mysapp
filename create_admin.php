<?php
// Dijalankan dari terminal STB, bukan dari browser:
//   cd /var/www/html/pertamina && sudo php create_admin.php admin "Nama Lengkap"
// Kalau username sudah ada, password-nya di-reset dan akunnya dijadikan master admin aktif
// (berguna kalau lupa password admin).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/config.php';

$u = strtolower($argv[1] ?? '');
if (!preg_match('/^[a-z0-9_]{3,30}$/', $u)) {
    fwrite(STDERR, "Pakai: php create_admin.php USERNAME [\"Nama Lengkap\"]\n");
    exit(1);
}
$nama = $argv[2] ?? 'Master Admin';

function baca_password(string $prompt): string {
    echo $prompt;
    system('stty -echo');
    $p = rtrim((string)fgets(STDIN), "\r\n");
    system('stty echo');
    echo "\n";
    return $p;
}

$p  = baca_password('Password (min. 8 karakter): ');
$p2 = baca_password('Ulangi password: ');
if ($p !== $p2)                          { fwrite(STDERR, "Password tidak sama.\n"); exit(1); }
if (strlen($p) < 8 || strlen($p) > 72)   { fwrite(STDERR, "Password harus 8-72 karakter.\n"); exit(1); }

$pdo  = db();
$hash = password_hash($p, PASSWORD_DEFAULT);
$st = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$st->execute([$u]);
$id = $st->fetchColumn();

if ($id) {
    $pdo->prepare("UPDATE users SET password_hash = ?, role = 'admin', status = 'aktif', gagal_login = 0, terkunci_sampai = NULL WHERE id = ?")
        ->execute([$hash, $id]);
    echo "Akun '$u' diperbarui: master admin, aktif, password baru.\n";
} else {
    $pdo->prepare("INSERT INTO users (username, nama, password_hash, role, status) VALUES (?,?,?, 'admin', 'aktif')")
        ->execute([$u, $nama, $hash]);
    echo "Master admin '$u' dibuat.\n";
}
