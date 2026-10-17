<?php
require __DIR__ . '/dokumen_lib.php';
$me = require_login();

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT * FROM dokumen WHERE id = ?');
$st->execute([$id]);
$row = $st->fetch();
if (!$row || ($me['role'] !== 'admin' && (int)$row['user_id'] !== (int)$me['id'])) {
    http_response_code(404);
    exit('Dokumen tidak ditemukan.');
}
$ext = $row['ekstensi'];
$did = (int)$row['id'];
page_head('Lihat Dokumen');
?>
<style>
.view{width:100%;height:78vh;min-height:420px;border:1px solid var(--line);border-radius:10px;background:#fff;display:block}
.top2{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:12px}
.top2 h1{margin:6px 0 2px;word-break:break-word}
</style>
<div class="stripe"></div>
<div class="page" style="max-width:1150px">
  <div class="top2">
    <div><h1><?= h($row['judul']) ?></h1><span class="muted"><?= h($row['nama_asli']) ?></span></div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn ghost" href="app.php" onclick="if (history.length > 1) { history.back(); return false; }">&larr; Kembali</a>
      <a class="btn primary" href="unduh.php?id=<?= $did ?>">Unduh</a>
    </div>
  </div>
  <p class="muted" id="status"></p>

<?php if ($ext === 'pdf'): ?>
  <iframe class="view" src="unduh.php?id=<?= $did ?>&amp;inline=1" title="Pratinjau PDF"></iframe>
  <p class="muted">Kalau PDF tidak tampil di HP, <a href="unduh.php?id=<?= $did ?>&amp;inline=1" target="_blank" rel="noopener">buka di tab baru</a> atau unduh.</p>
<?php elseif ($ext === 'doc'): ?>
  <div class="note err">Pratinjau tidak tersedia untuk format .doc (Word lama). Silakan unduh, atau simpan ulang file sebagai .docx supaya bisa dilihat di sini.</div>
<?php else: ?>
  <iframe id="sb" class="view" sandbox="allow-scripts" src="lihat_sandbox.html" title="Pratinjau dokumen"></iframe>
  <p class="muted">Pratinjau menampilkan isi file secara sederhana (tampilan asli seperti rumus, grafik, atau format khusus bisa berbeda). Untuk tampilan lengkap, unduh filenya.</p>
  <script>
  const ID = <?= $did ?>;
  const EXT = <?= json_encode($ext) ?>;
  const NAMA = <?= json_encode($row['nama_asli']) ?>;
  const frame = document.getElementById('sb');
  const status = document.getElementById('status');
  status.textContent = 'Memuat pratinjau...';

  window.addEventListener('message', async (e) => {
    if (e.source !== frame.contentWindow || !e.data) return;
    if (e.data.type === 'ready') {
      try {
        const res = await fetch('unduh.php?id=' + ID, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Gagal mengambil file (' + res.status + ').');
        const buf = await res.arrayBuffer();
        frame.contentWindow.postMessage({ type: 'file', ext: EXT, name: NAMA, buf: buf }, '*', [buf]);
      } catch (err) {
        status.textContent = err.message;
      }
    } else if (e.data.type === 'done') {
      status.textContent = '';
    } else if (e.data.type === 'error') {
      status.textContent = e.data.message || 'Gagal menampilkan dokumen.';
    }
  });
  </script>
<?php endif; ?>
</div>
</body></html>
