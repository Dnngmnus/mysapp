<?php
require __DIR__ . '/auth.php';
$me = require_login();

$err = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lama  = (string)($_POST['lama'] ?? '');
    $baru  = (string)($_POST['baru'] ?? '');
    $baru2 = (string)($_POST['baru2'] ?? '');
    $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $st->execute([$me['id']]);
    $hash = (string)$st->fetchColumn();

    if (!csrf_ok($_POST['csrf'] ?? '')) {
        $err = 'Sesi form kedaluwarsa, coba lagi.';
    } elseif (!password_verify($lama, $hash)) {
        $err = 'Password lama salah.';
    } elseif (strlen($baru) < 8 || strlen($baru) > 72) {
        $err = 'Password baru 8-72 karakter.';
    } elseif ($baru !== $baru2) {
        $err = 'Konfirmasi password tidak sama.';
    } else {
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($baru, PASSWORD_DEFAULT), $me['id']]);
        session_regenerate_id(true);
        $ok = 'Password berhasil diganti.';
    }
}
page_head('Akun Saya');
?>
<div class="stripe"></div>
<div class="auth"><div class="box">
  <h1>Akun Saya</h1>
  <p class="muted"><?= h($me['nama']) ?> (<?= h($me['username']) ?>) &middot;
    <?= $me['role'] === 'admin' ? 'Master Admin' : 'User' ?></p>
  <?php if ($ok): ?><div class="note ok"><?= h($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="note err"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label>Password lama<input type="password" name="lama" required autocomplete="current-password"></label>
    <label>Password baru (min. 8 karakter)<input type="password" name="baru" required autocomplete="new-password"></label>
    <label>Ulangi password baru<input type="password" name="baru2" required autocomplete="new-password"></label>
    <button class="btn primary" type="submit">Ganti Password</button>
    <a class="btn ghost" href="index.php">Kembali</a>
  </form>
</div></div>
</body></html>
