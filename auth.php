<?php
require_once __DIR__ . '/config.php';

// true  = akun baru harus disetujui master admin dulu
// false = akun baru langsung aktif
const SIGNUP_BUTUH_PERSETUJUAN = true;

function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function start_session(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    session_name('PERTAMINASESS');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_ok($t): bool {
    start_session();
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}

function current_user(): ?array {
    start_session();
    if (empty($_SESSION['uid'])) return null;
    $st = db()->prepare('SELECT id, username, nama, role, status FROM users WHERE id = ?');
    $st->execute([(int)$_SESSION['uid']]);
    $u = $st->fetch();
    if (!$u || $u['status'] !== 'aktif') {
        unset($_SESSION['uid']);
        return null;
    }
    return $u;
}

function require_login(): array {
    $u = current_user();
    if ($u) return $u;
    header('Location: login.php');
    exit;
}

function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
    return $u;
}

// Mengembalikan pesan error, atau '' kalau login berhasil
function do_login(string $username, string $password): string {
    $pdo = db();
    $salah = 'Username atau password salah.';
    $st = $pdo->prepare('SELECT *, (terkunci_sampai IS NOT NULL AND terkunci_sampai > NOW()) AS terkunci
                         FROM users WHERE username = ?');
    $st->execute([$username]);
    $u = $st->fetch();

    if (!$u) {
        password_hash($password, PASSWORD_DEFAULT); // samakan waktu respon
        return $salah;
    }
    if ($u['terkunci']) {
        return 'Terlalu banyak percobaan gagal. Coba lagi beberapa menit lagi.';
    }
    if (!password_verify($password, $u['password_hash'])) {
        $g = (int)$u['gagal_login'] + 1;
        if ($g >= 5) {
            $pdo->prepare('UPDATE users SET gagal_login = 0, terkunci_sampai = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id = ?')
                ->execute([$u['id']]);
        } else {
            $pdo->prepare('UPDATE users SET gagal_login = ? WHERE id = ?')->execute([$g, $u['id']]);
        }
        return $salah;
    }
    if ($u['status'] === 'pending')  return 'Akun Anda belum disetujui master admin.';
    if ($u['status'] === 'nonaktif') return 'Akun Anda dinonaktifkan. Hubungi master admin.';

    $pdo->prepare('UPDATE users SET gagal_login = 0, terkunci_sampai = NULL WHERE id = ?')->execute([$u['id']]);
    start_session();
    session_regenerate_id(true);
    unset($_SESSION['csrf']);
    $_SESSION['uid'] = (int)$u['id'];
    return '';
}

function auth_css(): string {
    return <<<'CSS'
:root{--red:#ED1C24;--blue:#0072BC;--green:#6CB33F;--bg:#f3f5f8;--ink:#1d2733;--line:#dde3ea}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--ink)}
.stripe{height:5px;background:linear-gradient(90deg,var(--red) 0 34%,var(--blue) 34% 67%,var(--green) 67%)}
.auth{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
.box{background:#fff;border-radius:12px;padding:26px;width:min(400px,100%);box-shadow:0 6px 24px rgba(0,0,0,.08);border-top:5px solid var(--red)}
.brand{display:flex;gap:12px;align-items:center;margin-bottom:6px}
.brand b{font-size:1.05rem}
.brand small{display:block;color:#6b7886}
.mark{display:grid;grid-template-columns:1fr 1fr;width:38px;height:38px;gap:3px;transform:rotate(45deg) scale(.75)}
.mark i{display:block}.mark .r{background:var(--red)}.mark .b{background:var(--blue)}.mark .g{background:var(--green);grid-column:1/3}
h1{font-size:1.2rem;margin:14px 0;color:var(--blue)}
form{display:grid;gap:12px}
label{display:grid;gap:4px;font-size:.8rem;color:#52606d}
input{padding:10px;border:1px solid var(--line);border-radius:7px;font:inherit;color:var(--ink);background:#fff}
input:focus{outline:2px solid var(--blue);outline-offset:-1px}
.btn{border:0;border-radius:7px;padding:10px 16px;font:inherit;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;text-align:center}
.btn.primary{background:var(--red);color:#fff}
.btn.ghost{background:#fff;border:1px solid var(--line);color:var(--ink)}
.btn.sm{padding:4px 10px;font-size:.78rem}
.note{padding:10px 12px;border-radius:7px;font-size:.88rem;margin-bottom:12px}
.note.err{background:#fde8e9;color:#a1121a}
.note.ok{background:#e6f4dc;color:#2f6a12}
.alt{text-align:center;font-size:.88rem;color:#52606d;margin:14px 0 0}
a{color:var(--blue)}
.page{max-width:1000px;margin:20px auto;padding:0 14px}
.bar{display:flex;justify-content:space-between;align-items:center;gap:10px}
.muted{color:#6b7886;font-size:.9rem}
.scroll{overflow-x:auto;background:#fff;border-radius:10px;padding:6px}
table{width:100%;border-collapse:collapse;font-size:.9rem}
th{background:var(--blue);color:#fff;text-align:left;padding:9px;white-space:nowrap}
td{padding:9px;border-bottom:1px solid var(--line);white-space:nowrap}
.badge{border-radius:99px;padding:2px 10px;font-size:.75rem;font-weight:600}
.badge.aktif{background:#e6f4dc;color:#2f6a12}
.badge.pending{background:#fff3cd;color:#8a6100}
.badge.nonaktif{background:#eceff3;color:#52606d}
CSS;
}

function page_head(string $title): void {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . h($title) . '</title><style>' . auth_css() . '</style></head><body>';
}
