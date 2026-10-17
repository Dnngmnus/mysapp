<?php
require __DIR__ . '/auth.php';
$me = require_login();
if ($me['role'] !== 'admin') { header('Location: app.php'); exit; }
page_head('List Agen');
?>
<div class="stripe"></div>
<div class="page">
  <div class="bar"><h1>List Agen</h1><a class="btn ghost" href="app.php">&larr; Kembali</a></div>
  <p class="muted">Nama agen di sini muncul sebagai pilihan saat mengisi laporan harian.</p>
  <form id="frm" autocomplete="off" style="grid-template-columns:1fr auto;align-items:end;max-width:560px;margin-bottom:16px">
    <label>Nama agen baru<input id="nama" maxlength="100" required placeholder="Contoh: Agen Maju Jaya"></label>
    <button class="btn primary" type="submit">Tambah</button>
  </form>
  <div class="scroll"><table>
    <thead><tr><th>#</th><th>Nama Agen</th><th></th></tr></thead>
    <tbody id="tb"></tbody>
  </table></div>
</div>
<script>
const CSRF = <?= json_encode(csrf_token()) ?>;

async function api(action, body) {
  const res = await fetch('agen_api.php?action=' + action, body
    ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) }
    : undefined);
  if (res.status === 401) { location.href = 'login.php'; throw new Error('Sesi berakhir'); }
  let d = {};
  try { d = await res.json(); } catch (e) {}
  if (!res.ok) throw new Error(d.error || 'Terjadi kesalahan (' + res.status + ')');
  return d;
}

function cell(t) { const c = document.createElement('td'); c.textContent = t; return c; }

async function load() {
  const rows = await api('list');
  const tb = document.getElementById('tb');
  tb.innerHTML = '';
  if (!rows.length) {
    tb.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#8a96a3;padding:20px">Belum ada agen</td></tr>';
    return;
  }
  rows.forEach((a, i) => {
    const tr = document.createElement('tr');
    const act = document.createElement('td');
    const del = document.createElement('button');
    del.className = 'btn ghost sm'; del.textContent = 'Hapus';
    del.onclick = async () => {
      if (!confirm('Hapus agen "' + a.nama + '" dari daftar? Laporan lama tidak terpengaruh.')) return;
      try { await api('delete', { id: a.id }); load(); } catch (e) { alert(e.message); }
    };
    act.append(del);
    tr.append(cell(i + 1), cell(a.nama), act);
    tb.append(tr);
  });
}

document.getElementById('frm').addEventListener('submit', async ev => {
  ev.preventDefault();
  const inp = document.getElementById('nama');
  try {
    await api('add', { nama: inp.value });
    inp.value = '';
    inp.focus();
    load();
  } catch (e) { alert(e.message); }
});
load().catch(e => alert(e.message));
</script>
</body></html>
