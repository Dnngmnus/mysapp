<?php
/* Blok "Galeri" untuk halaman depan.
   Cara pakai: di index.php, tambahkan include 'galeri_blok.php' (lihat petunjuk) di tempat galeri mau muncul.
   Kalau belum ada media (atau tabelnya belum dibuat), blok ini tidak menampilkan apa-apa. */
try {
    $galeri = db()->query('SELECT id, jenis, judul FROM media ORDER BY urutan, id')->fetchAll();
} catch (Throwable $e) {
    $galeri = [];
}
if ($galeri):
?>
<style>
.galeri{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:24px}
.galeri figure{margin:0;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.08)}
.galeri img,.galeri video{width:100%;aspect-ratio:16/10;object-fit:cover;display:block;background:#0b1220}
.galeri figcaption{padding:10px 14px;font-size:.9rem;color:#52606d}
</style>
<section class="s" id="galeri"><div class="wrap">
  <h2>Galeri</h2>
  <div class="galeri">
    <?php foreach ($galeri as $g): $src = 'media_file.php?id=' . (int)$g['id']; ?>
      <figure>
        <?php if ($g['jenis'] === 'video'): ?>
          <video controls playsinline preload="metadata" src="<?= h($src) ?>"></video>
        <?php else: ?>
          <a href="<?= h($src) ?>" target="_blank" rel="noopener"><img loading="lazy" src="<?= h($src) ?>" alt="<?= h($g['judul']) ?>"></a>
        <?php endif; ?>
        <?php if ($g['judul'] !== ''): ?><figcaption><?= h($g['judul']) ?></figcaption><?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </div>
</div></section>
<?php endif; ?>
