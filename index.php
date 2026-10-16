<?php
require __DIR__ . '/auth.php';
$p = require __DIR__ . '/profil.php';

// Halaman depan harus tetap tampil walau database bermasalah
$login = null;
if (isset($_COOKIE['PERTAMINASESS'])) {
    try { $login = current_user(); } catch (Throwable $e) { $login = null; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($p['PT. GAS ALAM BUMI REOG']) ?></title>
<link rel="icon" type="image/png" href="logo-mark.png">
<style>
:root{--red:#ED1C24;--blue:#0072BC;--green:#6CB33F;--bg:#f3f5f8;--ink:#1d2733;--line:#dde3ea}
*{box-sizing:border-box}
html{scroll-behavior:smooth;scroll-padding-top:70px}
body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:var(--ink);background:#fff;line-height:1.6}
a{color:var(--blue)}
.wrap{max-width:1050px;margin:0 auto;padding:0 18px}
.nav{position:sticky;top:0;z-index:10;background:#fff;box-shadow:0 1px 0 var(--line)}
.nav .wrap{display:flex;align-items:center;justify-content:space-between;gap:12px;height:62px}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--ink);font-weight:700}
.brand img{height:38px;width:auto}
.links{display:flex;gap:20px}
.links a{text-decoration:none;color:#52606d;font-size:.92rem}
.links a:hover{color:var(--red)}
.btn{border:0;border-radius:8px;padding:10px 20px;font:inherit;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block}
.btn.primary{background:var(--red);color:#fff}
.btn.blue{background:var(--blue);color:#fff}
.btn.ghost{background:#fff;border:1px solid var(--line);color:var(--ink)}
.stripe{height:5px;background:linear-gradient(90deg,var(--red) 0 34%,var(--blue) 34% 67%,var(--green) 67%)}
.hero{background:var(--bg)}
.hero .wrap{display:grid;grid-template-columns:1.2fr .8fr;gap:30px;align-items:center;padding-top:54px;padding-bottom:54px}
.hero h1{font-size:2.2rem;line-height:1.2;margin:0 0 12px}
.hero p{font-size:1.05rem;color:#52606d;margin:0 0 22px}
.hero .cta{display:flex;gap:10px;flex-wrap:wrap}
.hero img{width:min(300px,70%);justify-self:center}
section.s{padding:52px 0}
section.s.alt{background:var(--bg)}
h2{font-size:1.5rem;margin:0 0 6px;color:var(--blue)}
h2::after{content:"";display:block;width:54px;height:4px;background:var(--red);border-radius:2px;margin-top:8px}
.lead{color:#52606d;max-width:720px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;margin-top:24px}
.card{background:#fff;border-radius:12px;padding:20px;border-top:4px solid var(--blue);box-shadow:0 2px 10px rgba(0,0,0,.05)}
.card:nth-child(2){border-color:var(--red)}
.card:nth-child(3){border-color:var(--green)}
.card h3{margin:0 0 6px;font-size:1.05rem}
.card p{margin:0;color:#52606d;font-size:.93rem}
.fakta{display:inline-block;background:var(--blue);color:#fff;border-radius:99px;padding:3px 14px;font-size:.85rem;font-weight:600;margin-top:10px}
ul.misi{padding-left:20px;color:#52606d}
.kontak{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:24px}
.kontak .card b{display:block;color:var(--blue);font-size:.8rem;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
footer{background:#1d2733;color:#b7c0ca;font-size:.88rem;padding:22px 0}
footer .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px}
@media (max-width:760px){
  .links{display:none}
  .hero .wrap{grid-template-columns:1fr;padding-top:34px;padding-bottom:34px}
  .hero img{order:-1;width:150px}
  .hero h1{font-size:1.7rem}
}
</style>
</head>
<body>
<header class="nav"><div class="wrap">
  <a class="brand" href="#top"><img src="logo-mark.png" alt=""><span><?= h($p['nama']) ?></span></a>
  <nav class="links">
    <a href="#profil">Profil</a><a href="#visi">Visi &amp; Misi</a>
    <a href="#layanan">Layanan</a><a href="#kontak">Kontak</a>
  </nav>
  <?php if ($login): ?>
    <a class="btn primary" href="app.php">Buka Aplikasi</a>
  <?php else: ?>
    <a class="btn primary" href="login.php">Masuk</a>
  <?php endif; ?>
</div></header>
<div class="stripe"></div>

<section class="hero" id="top"><div class="wrap">
  <div>
    <h1><?= h($p['nama']) ?></h1>
    <p><?= h($p['tagline']) ?></p>
    <div class="cta">
      <?php if ($login): ?>
        <a class="btn primary" href="app.php">Buka Aplikasi</a>
      <?php else: ?>
        <a class="btn primary" href="login.php">Masuk</a>
        <a class="btn ghost" href="signup.php">Daftar Akun</a>
      <?php endif; ?>
    </div>
  </div>
  <img src="logo.png" alt="Pertamina">
</div></section>

<section class="s" id="profil"><div class="wrap">
  <h2>Profil Perusahaan</h2>
  <p class="lead"><?= nl2br(h($p['deskripsi'])) ?></p>
  <?php if (!empty($p['berdiri'])): ?><span class="fakta">Berdiri sejak <?= h($p['berdiri']) ?></span><?php endif; ?>
</div></section>

<section class="s alt" id="visi"><div class="wrap">
  <h2>Visi &amp; Misi</h2>
  <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
    <div class="card"><h3>Visi</h3><p><?= h($p['visi']) ?></p></div>
    <div class="card"><h3>Misi</h3>
      <ul class="misi"><?php foreach ($p['misi'] as $m): ?><li><?= h($m) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
</div></section>

<section class="s" id="layanan"><div class="wrap">
  <h2>Layanan</h2>
  <div class="grid">
    <?php foreach ($p['layanan'] as $l): ?>
      <div class="card"><h3><?= h($l['judul']) ?></h3><p><?= h($l['isi']) ?></p></div>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="s alt" id="kontak"><div class="wrap">
  <h2>Kontak</h2>
  <div class="kontak">
    <div class="card"><b>Alamat</b><?= h($p['alamat']) ?></div>
    <div class="card"><b>Telepon</b><?= h($p['telepon']) ?></div>
    <div class="card"><b>Email</b><?= h($p['email']) ?></div>
    <div class="card"><b>Jam Operasional</b><?= h($p['jam']) ?></div>
  </div>
</div></section>

<footer><div class="wrap">
  <span>&copy; <?= date('Y') ?> <?= h($p['nama']) ?></span>
  <span>Sistem internal &middot; hanya untuk pengguna terdaftar</span>
</div></footer>
</body>
</html>
