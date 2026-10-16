<?php
require __DIR__ . '/auth.php';
if (current_user()) { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u  = strtolower(trim($_POST['username'] ?? ''));
    $n  = trim($_POST['nama'] ?? '');
    $p  = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');

    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $err = 'Sesi form kedaluwarsa, coba lagi.';
    } elseif (!preg_match('/^[a-z0-9_]{3,30}$/', $u)) {
        $err = 'Username 3-30 karakter: huruf kecil, angka, atau underscore.';
    } elseif (!preg_match('/^.{2,100}$/u', $n)) {
        $err = 'Nama lengkap 2-100 karakter.';
    } elseif (strlen($p) < 8 || strlen($p) > 72) {
        $err = 'Password 8-72 karakter.';
    } elseif ($p !== $p2) {
        $err = 'Konfirmasi password tidak sama.';
    } else {
        try {
            $st = db()->prepare('INSERT INTO users (username, nama, password_hash, role, status) VALUES (?,?,?,?,?)');
            $st->execute([$u, $n, password_hash($p, PASSWORD_DEFAULT), 'user',
                          SIGNUP_BUTUH_PERSETUJUAN ? 'pending' : 'aktif']);
            header('Location: login.php?daftar=1');
            exit;
        } catch (PDOException $e) {
            $err = ($e->errorInfo[1] ?? 0) == 1062 ? 'Username sudah dipakai.' : 'Gagal mendaftar, coba lagi.';
            if ($err === 'Gagal mendaftar, coba lagi.') error_log('[pertamina] ' . $e->getMessage());
        }
    }
}
page_head('Daftar');
?>
<div class="stripe"></div>
<div class="auth"><div class="box">
  <div class="brand"><span class="mark"><i class="r"></i><i class="b"></i><i class="g"></i></span>
    <div><b>Pencatatan Penjualan BBM</b><small>Sistem lokal</small></div></div>
  <h1>Daftar Akun</h1>
  <?php if ($err): ?><div class="note err"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label>Nama lengkap<input name="nama" required maxlength="100" value="<?= h($_POST['nama'] ?? '') ?>"></label>
    <label>Username<input name="username" required maxlength="30" autocomplete="username" value="<?= h($_POST['username'] ?? '') ?>"></label>
    <label>Password (min. 8 karakter)<input type="password" name="password" required autocomplete="new-password"></label>
    <label>Ulangi password<input type="password" name="password2" required autocomplete="new-password"></label>
    <button class="btn primary" type="submit">Daftar</button>
  </form>
  <p class="alt">Sudah punya akun? <a href="login.php">Masuk</a></p>
</div></div>
</body></html>
