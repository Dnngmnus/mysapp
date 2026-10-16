<?php
require __DIR__ . '/auth.php';
if (current_user()) { header('Location: app.php'); exit; }

$err = '';
$info = '';
if (isset($_GET['daftar'])) {
    $info = SIGNUP_BUTUH_PERSETUJUAN
        ? 'Pendaftaran berhasil. Tunggu persetujuan master admin sebelum bisa masuk.'
        : 'Pendaftaran berhasil. Silakan masuk.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $err = 'Sesi form kedaluwarsa, coba lagi.';
    } else {
        $err = do_login(strtolower(trim($_POST['username'] ?? '')), (string)($_POST['password'] ?? ''));
        if ($err === '') { header('Location: app.php'); exit; }
    }
}
page_head('Masuk');
?>
<div class="stripe"></div>
<div class="auth"><div class="box">
  <div class="brand"><img src="logo-mark.png" alt="Pertamina" style="height:42px;width:auto">
    <div><b>Pencatatan Penjualan BBM</b><small>Sistem lokal</small></div></div>
  <h1>Masuk</h1>
  <?php if ($info): ?><div class="note ok"><?= h($info) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="note err"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label>Username<input name="username" required autofocus autocomplete="username" value="<?= h($_POST['username'] ?? '') ?>"></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn primary" type="submit">Masuk</button>
  </form>
  <p class="alt">Belum punya akun? <a href="signup.php">Daftar</a></p>
  <p class="alt"><a href="index.php">&larr; Kembali ke beranda</a></p>
</div></div>
</body></html>
