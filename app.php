<?php
require __DIR__ . '/auth.php';
$me = require_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PT. GAS ALAM BUMI REOG</title>
<link rel="icon" type="image/png" href="logo-mark.png">
<style>
:root{--red:#ED1C24;--blue:#0072BC;--green:#6CB33F;--bg:#f3f5f8;--ink:#1d2733;--line:#dde3ea}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--ink)}
.top{display:flex;justify-content:space-between;align-items:center;padding:14px 22px;background:#fff}
.brand{display:flex;gap:14px;align-items:center}
.brand h1{margin:0;font-size:1.15rem}
.brand small{color:#6b7886}
.mark{display:grid;grid-template-columns:1fr 1fr;width:38px;height:38px;gap:3px;transform:rotate(45deg) scale(.75)}
.mark i{display:block}.mark .r{background:var(--red)}.mark .b{background:var(--blue)}.mark .g{background:var(--green);grid-column:1/3}
.stripe{height:5px;background:linear-gradient(90deg,var(--red) 0 34%,var(--blue) 34% 67%,var(--green) 67%)}
main{max-width:1100px;margin:20px auto;padding:0 14px;display:grid;gap:16px}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}
.card{background:#fff;border-radius:10px;padding:14px 16px;border-left:5px solid var(--blue)}
.card.red{border-color:var(--red)}
.card span{display:block;color:#6b7886;font-size:.8rem}
.card b{font-size:1.4rem}
.panel{background:#fff;border-radius:10px;padding:16px}
.panel h2{margin:0 0 12px;font-size:1rem;color:var(--blue)}
form{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px}
label{display:grid;gap:4px;font-size:.8rem;color:#52606d}
input,select{padding:9px 10px;border:1px solid var(--line);border-radius:7px;font:inherit;color:var(--ink);background:#fff}
input:focus,select:focus{outline:2px solid var(--blue);outline-offset:-1px}
input[readonly]{background:#f3f5f8;font-weight:700}
.actions{display:flex;gap:8px;align-items:end}
.btn{border:0;border-radius:7px;padding:9px 16px;font:inherit;font-weight:600;cursor:pointer}
.btn.primary{background:var(--red);color:#fff}
.btn.blue{background:var(--blue);color:#fff}
.btn.ghost{background:#fff;border:1px solid var(--line);color:var(--ink)}
.btn.sm{padding:4px 10px;font-size:.78rem}
.filters{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px}
.filters input{flex:1;min-width:140px}
.scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:.9rem}
th{background:var(--blue);color:#fff;text-align:left;padding:9px;white-space:nowrap}
td{padding:9px;border-bottom:1px solid var(--line);white-space:nowrap}
.num{text-align:right}
tr:hover td{background:#f8fafc}
.empty{text-align:center;color:#8a96a3;padding:22px}
dialog{border:0;border-radius:12px;padding:20px;width:min(420px,92vw)}
dialog::backdrop{background:rgba(0,0,0,.45)}
dialog h2{margin-top:0;color:var(--red)}
.hrow{display:grid;grid-template-columns:1fr 130px;gap:8px;align-items:center;margin-bottom:8px}
dialog .actions{margin-top:14px}

.top{flex-wrap:wrap;gap:10px}
.userbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.userbar form{display:inline;margin:0}
.userbar .who{font-size:.85rem;color:#52606d;margin-right:4px}
.role{font-style:normal;background:var(--blue);color:#fff;border-radius:99px;padding:2px 9px;font-size:.72rem;margin-left:4px}
.role.admin{background:var(--red)}
a.btn{text-decoration:none;display:inline-block}
.ticker{background:var(--blue);color:#fff;overflow:hidden;white-space:nowrap;font-size:.9rem;padding:7px 0}
.ticker-track{display:inline-flex;animation:jalan 30s linear infinite}
.ticker-track span{padding-right:3rem}
.ticker:hover .ticker-track{animation-play-state:paused}
@keyframes jalan{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.brand .logo{height:44px;width:auto}
.tabs{display:flex;gap:6px;border-bottom:2px solid var(--line)}
.tabs button{background:none;border:0;padding:10px 16px;font:inherit;font-weight:600;color:#52606d;cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px}
.tabs button.on{color:var(--red);border-color:var(--red)}
.tabpane{display:grid;gap:16px}
.tabpane[hidden]{display:none}
.hint{color:#6b7886;font-size:.85rem;margin:8px 0 0}
</style>
</head>
<body>
<header class="top">
  <div class="brand">
    <img class="logo" src="logo-mark.png" alt="Pertamina">
    <div><h1>PT. GAS ALAM BUMI REOG</h1><small>SERVER LOKAL &middot; HG680P</small></div>
  </div>
  <div class="userbar">
    <span class="who"><?= htmlspecialchars($me['nama'], ENT_QUOTES, 'UTF-8') ?>
      <em class="role <?= $me['role'] === 'admin' ? 'admin' : '' ?>"><?= $me['role'] === 'admin' ? 'Master Admin' : 'User' ?></em></span>
    <?php if ($me['role'] === 'admin'): ?><a class="btn ghost" href="admin.php">Kelola User</a><?php endif; ?>
    <button class="btn ghost" id="btnHarga" <?= $me['role'] === 'admin' ? '' : 'hidden' ?>>HARGA</button>
    <a class="btn ghost" href="index.php">Beranda</a>
    <a class="btn ghost" href="akun.php">Akun</a>
    <form method="post" action="logout.php">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
      <button class="btn primary" type="submit">Keluar</button>
    </form>
  </div>
</header>
<div class="stripe"></div>
<div class="ticker">
  <div class="ticker-track">
    <span>Selamat datang di sistem pencatatan penjualan BBM &bull; Pastikan data liter dan plat nomor diisi dengan benar &bull; Harga dapat diubah oleh master admin lewat tombol Atur Harga &bull;</span>
    <span>Selamat datang di sistem pencatatan penjualan BBM &bull; Pastikan data liter dan plat nomor diisi dengan benar &bull; Harga dapat diubah oleh master admin lewat tombol Atur Harga &bull;</span>
  </div>
</div>

<main>
  <nav class="tabs">
    <button type="button" data-tab="bbm" class="on">Penjualan BBM</button>
    <button type="button" data-tab="dok">Dokumen Penting</button>
  </nav>
  <div id="tabBbm" class="tabpane">
  <section class="cards">
    <div class="card"><span>Transaksi</span><b id="sN">0</b></div>
    <div class="card"><span>Total Liter</span><b id="sL">0</b></div>
    <div class="card red"><span>Total Penjualan</span><b id="sT">Rp 0</b></div>
  </section>

  <section class="panel">
    <h2 id="formTitle">Tambah Transaksi</h2>
    <form id="frm" autocomplete="off">
      <input type="hidden" id="id">
      <label>Tanggal<input type="date" id="tanggal" required></label>
      <label>Pelanggan<input id="pelanggan" maxlength="100" required></label>
      <label>Plat Nomor<input id="plat" maxlength="15" placeholder="AG 1234 XX"></label>
      <label>Jenis BBM<select id="bbm_id" required></select></label>
      <label>Liter<input type="number" id="liter" step="0.01" min="0.01" required></label>
      <label>Harga / Liter<input type="number" id="harga" min="0" required></label>
      <label>Total<input id="total" readonly></label>
      <div class="actions">
        <button class="btn primary" type="submit">Simpan</button>
        <button class="btn ghost" type="button" id="btnBatal">Batal</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="filters">
      <input id="q" placeholder="Cari pelanggan / plat">
      <input type="date" id="dari" title="Dari">
      <input type="date" id="sampai" title="Sampai">
      <button class="btn blue" id="btnCari">Filter</button>
    </div>
    <div class="scroll">
      <table>
        <thead><tr><th>Tanggal</th><th>Pelanggan</th><th>Plat</th><th>BBM</th><th>Petugas</th>
          <th class="num">Liter</th><th class="num">Harga</th><th class="num">Total</th><th></th></tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
  </section>
</div>

  <div id="tabDok" class="tabpane" hidden>
    <section class="panel">
      <h2>Simpan Dokumen</h2>
      <form id="frmDok" autocomplete="off">
        <label>Judul<input id="dokJudul" maxlength="150" placeholder="Opsional, default nama file"></label>
        <label>File (PDF, Word, Excel, CSV &middot; maks. 20 MB)
          <input type="file" id="dokFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv" required></label>
        <div class="actions"><button class="btn primary" type="submit" id="btnUnggah">Unggah</button></div>
      </form>
      <p class="hint" id="dokMsg"></p>
    </section>
    <section class="panel">
      <div class="filters">
        <input id="dokQ" placeholder="Cari judul / nama file">
        <button class="btn blue" type="button" id="btnDokCari">Cari</button>
      </div>
      <div class="scroll">
        <table>
          <thead><tr><th>Judul</th><th>Nama File</th><th>Jenis</th><th class="num">Ukuran</th>
            <th>Pemilik</th><th>Diunggah</th><th></th></tr></thead>
          <tbody id="dokBody"></tbody>
        </table>
      </div>
    </section>
  </div>
</main>

<dialog id="dlgHarga">
  <h2>Atur Harga BBM</h2>
  <div id="hargaList"></div>
  <div class="actions">
    <button class="btn primary" id="btnSimpanHarga">Simpan</button>
    <button class="btn ghost" id="btnTutup">Tutup</button>
  </div>
</dialog>

<script>
const $ = id => document.getElementById(id);
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
let bbm = [];

const CSRF = <?= json_encode(csrf_token()) ?>;

async function api(action, body, qs = '') {
  const res = await fetch(`api.php?action=${action}${qs}`, body
    ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) }
    : undefined);
  if (res.status === 401) { location.href = 'login.php'; throw new Error('Sesi berakhir'); }
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Terjadi kesalahan');
  return data;
}

function td(text, cls) {
  const c = document.createElement('td');
  c.textContent = text;
  if (cls) c.className = cls;
  return c;
}

async function loadBbm() {
  bbm = await api('bbm');
  $('bbm_id').innerHTML = '';
  bbm.forEach(b => $('bbm_id').add(new Option(b.nama, b.id)));
  setHargaDefault();
}

function setHargaDefault() {
  const b = bbm.find(x => x.id == $('bbm_id').value);
  if (b) $('harga').value = b.harga;
  hitung();
}

function hitung() {
  const t = Math.round((+$('liter').value || 0) * (+$('harga').value || 0));
  $('total').value = rp(t);
}

async function loadList() {
  const qs = '&q=' + encodeURIComponent($('q').value)
           + '&dari=' + $('dari').value + '&sampai=' + $('sampai').value;
  const { rows, stat } = await api('list', null, qs);
  $('sN').textContent = stat.n;
  $('sL').textContent = Number(stat.liter).toLocaleString('id-ID') + ' L';
  $('sT').textContent = rp(stat.total);

  const tb = $('tbody');
  tb.innerHTML = '';
  if (!rows.length) {
    tb.innerHTML = '<tr><td class="empty" colspan="9">Belum ada data</td></tr>';
    return;
  }
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.append(td(r.tanggal), td(r.pelanggan), td(r.plat || '-'), td(r.bbm), td(r.petugas || '-'),
      td(Number(r.liter).toLocaleString('id-ID'), 'num'),
      td(rp(r.harga), 'num'), td(rp(r.total), 'num'));
    const act = document.createElement('td');
    const e = document.createElement('button');
    e.className = 'btn ghost sm'; e.textContent = 'Edit';
    e.onclick = () => edit(r);
    const d = document.createElement('button');
    d.className = 'btn ghost sm'; d.textContent = 'Hapus';
    d.onclick = () => hapus(r);
    act.append(e, ' ', d);
    tr.append(act);
    tb.append(tr);
  });
}

function edit(r) {
  $('formTitle').textContent = 'Edit Transaksi #' + r.id;
  $('id').value = r.id;
  $('tanggal').value = r.tanggal;
  $('pelanggan').value = r.pelanggan;
  $('plat').value = r.plat;
  $('bbm_id').value = r.bbm_id;
  $('liter').value = r.liter;
  $('harga').value = r.harga;
  hitung();
  scrollTo({ top: 0, behavior: 'smooth' });
}

async function hapus(r) {
  if (!confirm(`Hapus transaksi ${r.pelanggan} (${r.tanggal})?`)) return;
  await api('delete', { id: r.id });
  loadList();
}

function resetForm() {
  $('frm').reset();
  $('id').value = '';
  $('formTitle').textContent = 'Tambah Transaksi';
  $('tanggal').value = new Date().toISOString().slice(0, 10);
  setHargaDefault();
}

$('frm').addEventListener('submit', async ev => {
  ev.preventDefault();
  try {
    await api('save', {
      id: $('id').value || null,
      tanggal: $('tanggal').value,
      pelanggan: $('pelanggan').value,
      plat: $('plat').value,
      bbm_id: $('bbm_id').value,
      liter: $('liter').value,
      harga: $('harga').value
    });
    resetForm();
    loadList();
  } catch (e) { alert(e.message); }
});

$('bbm_id').onchange = setHargaDefault;
$('liter').oninput = hitung;
$('harga').oninput = hitung;
$('btnBatal').onclick = resetForm;
$('btnCari').onclick = loadList;
$('q').addEventListener('keydown', e => { if (e.key === 'Enter') loadList(); });

$('btnHarga').onclick = () => {
  const box = $('hargaList');
  box.innerHTML = '';
  bbm.forEach(b => {
    const row = document.createElement('div');
    row.className = 'hrow';
    const lb = document.createElement('span'); lb.textContent = b.nama;
    const inp = document.createElement('input');
    inp.type = 'number'; inp.min = 0; inp.value = b.harga; inp.dataset.id = b.id;
    row.append(lb, inp);
    box.append(row);
  });
  $('dlgHarga').showModal();
};
$('btnTutup').onclick = () => $('dlgHarga').close();
$('btnSimpanHarga').onclick = async () => {
  const items = [...$('hargaList').querySelectorAll('input')]
    .map(i => ({ id: i.dataset.id, harga: i.value }));
  await api('bbm_update', { items });
  await loadBbm();
  $('dlgHarga').close();
};

(async () => {
  try {
    await loadBbm();
    resetForm();
    await loadList();
  } catch (e) { alert('Gagal memuat: ' + e.message); }
})();


// ===== Dokumen penting =====
const DOK_MAX = 20 * 1024 * 1024;

async function dokApi(action, body, qs = '') {
  const opt = body instanceof FormData
    ? { method: 'POST', headers: { 'X-CSRF-Token': CSRF }, body }
    : body
      ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) }
      : undefined;
  const res = await fetch(`dokumen_api.php?action=${action}${qs}`, opt);
  if (res.status === 401) { location.href = 'login.php'; throw new Error('Sesi berakhir'); }
  let data = {};
  try { data = await res.json(); } catch (e) {}
  if (!res.ok) throw new Error(data.error || 'Terjadi kesalahan (' + res.status + ')');
  return data;
}

function fmtUkuran(b) {
  if (b < 1024) return b + ' B';
  if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
  return (b / 1048576).toFixed(1) + ' MB';
}

async function dokLoad() {
  const rows = await dokApi('list', null, '&q=' + encodeURIComponent($('dokQ').value));
  const tb = $('dokBody');
  tb.innerHTML = '';
  if (!rows.length) {
    tb.innerHTML = '<tr><td class="empty" colspan="7">Belum ada dokumen</td></tr>';
    return;
  }
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.append(td(r.judul), td(r.nama_asli), td(r.ekstensi.toUpperCase()),
      td(fmtUkuran(+r.ukuran), 'num'), td(r.pemilik || '(dihapus)'), td(r.created_at));
    const act = document.createElement('td');
    const dl = document.createElement('a');
    dl.className = 'btn ghost sm'; dl.textContent = 'Unduh';
    dl.href = 'unduh.php?id=' + encodeURIComponent(r.id);
    const del = document.createElement('button');
    del.className = 'btn ghost sm'; del.textContent = 'Hapus';
    del.onclick = async () => {
      if (!confirm('Hapus dokumen "' + r.judul + '"? Tidak bisa dibatalkan.')) return;
      try { await dokApi('delete', { id: r.id }); dokLoad(); } catch (e) { alert(e.message); }
    };
    act.append(dl, ' ', del);
    tr.append(act);
    tb.append(tr);
  });
}

$('frmDok').addEventListener('submit', async ev => {
  ev.preventDefault();
  const file = $('dokFile').files[0];
  const msg = $('dokMsg');
  if (!file) return;
  if (file.size > DOK_MAX) { msg.textContent = 'Ukuran file maksimal 20 MB.'; return; }
  const fd = new FormData();
  fd.append('berkas', file);
  fd.append('judul', $('dokJudul').value);
  $('btnUnggah').disabled = true;
  msg.textContent = 'Mengunggah...';
  try {
    await dokApi('upload', fd);
    msg.textContent = 'Dokumen berhasil disimpan.';
    $('frmDok').reset();
    dokLoad();
  } catch (e) {
    msg.textContent = e.message;
  } finally {
    $('btnUnggah').disabled = false;
  }
});
$('btnDokCari').onclick = () => dokLoad().catch(e => alert(e.message));
$('dokQ').addEventListener('keydown', e => { if (e.key === 'Enter') dokLoad().catch(er => alert(er.message)); });

document.querySelectorAll('.tabs button').forEach(b => {
  b.onclick = () => {
    document.querySelectorAll('.tabs button').forEach(x => x.classList.toggle('on', x === b));
    $('tabBbm').hidden = b.dataset.tab !== 'bbm';
    $('tabDok').hidden = b.dataset.tab !== 'dok';
    if (b.dataset.tab === 'dok') dokLoad().catch(e => alert(e.message));
  };
});
</script>
</body>
</html>
