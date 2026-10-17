<?php
require __DIR__ . '/auth.php';
$me = require_login();
if ($me['role'] !== 'admin') { header('Location: app.php'); exit; }
page_head('Media Beranda');
?>
<style>
.pn{background:#fff;border-radius:10px;padding:16px;margin-bottom:16px}
.pn form{grid-template-columns:repeat(auto-fit,minmax(200px,1fr));align-items:end}
.mg{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px}
.mi{background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.08)}
.mi img,.mi video{width:100%;height:150px;object-fit:cover;display:block;background:#0b1220}
.mi .cap{padding:10px;font-size:.85rem;display:grid;gap:8px}
</style>
<div class="stripe"></div>
<div class="page">
  <div class="bar"><h1>Media Beranda</h1>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a class="btn ghost" href="index.php" target="_blank">Lihat Beranda</a>
      <a class="btn ghost" href="app.php">&larr; Kembali</a>
    </div></div>
  <p class="muted">Foto (JPG, PNG, WebP, maks. 8 MB) dan video (MP4 atau WebM, maks. 50 MB) yang tampil di bagian Galeri halaman depan. Hanya master admin yang bisa mengubahnya.</p>

  <div class="pn">
    <form id="frm" autocomplete="off">
      <label>Judul / keterangan (opsional)<input id="judul" maxlength="150"></label>
      <label>File foto atau video
        <input type="file" id="berkas" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm" required></label>
      <button class="btn primary" type="submit" id="btn">Unggah</button>
    </form>
    <p class="muted" id="msg" style="margin:10px 0 0"></p>
  </div>

  <div class="mg" id="mg"></div>
</div>
<script>
const CSRF = <?= json_encode(csrf_token()) ?>;
const msg = document.getElementById('msg');

async function api(action, body) {
  const res = await fetch('media_api.php?action=' + action, body
    ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) }
    : undefined);
  if (res.status === 401) { location.href = 'login.php'; throw new Error('Sesi berakhir'); }
  let d = {};
  try { d = await res.json(); } catch (e) {}
  if (!res.ok) throw new Error(d.error || 'Terjadi kesalahan (' + res.status + ')');
  return d;
}

function upload(fd) {
  return new Promise((ok, fail) => {
    const x = new XMLHttpRequest();
    x.open('POST', 'media_api.php?action=upload');
    x.setRequestHeader('X-CSRF-Token', CSRF);
    x.upload.onprogress = e => {
      if (e.lengthComputable) msg.textContent = 'Mengunggah... ' + Math.round(e.loaded / e.total * 100) + '%';
    };
    x.onload = () => {
      let d = {};
      try { d = JSON.parse(x.responseText); } catch (e) {}
      if (x.status >= 200 && x.status < 300) ok(d);
      else fail(new Error(d.error || 'Gagal mengunggah (' + x.status + ')'));
    };
    x.onerror = () => fail(new Error('Koneksi terputus saat mengunggah.'));
    x.send(fd);
  });
}

async function load() {
  const rows = await api('list');
  const mg = document.getElementById('mg');
  mg.innerHTML = '';
  if (!rows.length) {
    mg.innerHTML = '<p class="muted">Belum ada foto atau video.</p>';
    return;
  }
  rows.forEach(r => {
    const box = document.createElement('div');
    box.className = 'mi';
    const src = 'media_file.php?id=' + encodeURIComponent(r.id);
    let el;
    if (r.jenis === 'video') {
      el = document.createElement('video');
      el.src = src; el.controls = true; el.preload = 'metadata'; el.setAttribute('playsinline', '');
    } else {
      el = document.createElement('img');
      el.src = src; el.alt = r.judul || ''; el.loading = 'lazy';
    }
    const cap = document.createElement('div');
    cap.className = 'cap';
    const t = document.createElement('span');
    t.textContent = r.judul || (r.jenis === 'video' ? 'Video' : 'Foto');
    const del = document.createElement('button');
    del.className = 'btn ghost sm'; del.textContent = 'Hapus';
    del.onclick = async () => {
      if (!confirm('Hapus media ini dari halaman depan?')) return;
      try { await api('delete', { id: r.id }); load(); } catch (e) { alert(e.message); }
    };
    cap.append(t, del);
    box.append(el, cap);
    mg.append(box);
  });
}

document.getElementById('frm').addEventListener('submit', async ev => {
  ev.preventDefault();
  const f = document.getElementById('berkas').files[0];
  if (!f) return;
  const fd = new FormData();
  fd.append('berkas', f);
  fd.append('judul', document.getElementById('judul').value);
  document.getElementById('btn').disabled = true;
  msg.textContent = 'Mengunggah...';
  try {
    await upload(fd);
    msg.textContent = 'Berhasil diunggah.';
    document.getElementById('frm').reset();
    load();
  } catch (e) {
    msg.textContent = e.message;
  } finally {
    document.getElementById('btn').disabled = false;
  }
});
load().catch(e => alert(e.message));
</script>
</body></html>
