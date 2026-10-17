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
label{min-width:0}
label select,label input{width:100%;min-width:0;text-overflow:ellipsis}
label.lebar{grid-column:span 2}
tfoot td{font-weight:700;background:#f3f5f8;border-top:2px solid var(--line)}
.filters input[type=month]{flex:0 1 190px}
</style>
  <link rel="stylesheet" href="app_ext.css">
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
    <button class="btn ghost" id="btnHarga" <?= $me['role'] === 'admin' ? '' : 'hidden' ?>>List Agen</button>
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
    <span>Selamat datang di sistem pencatatan Penyaluran Elpiji &bull; Pastikan data agen dan plat nomor diisi dengan benar &bull; Halaman ini hanya dapat diakses oleh Admin &bull;</span>
    <span>Selamat datang di sistem pencatatan Penyaluran Elpiji &bull; Pastikan data agen dan plat nomor diisi dengan benar &bull; Halaman ini hanya dapat diakses oleh Admin &bull;</span>
  </div>
</div>

<main>
  <nav class="tabs">
    <button type="button" data-tab="bbm" class="on">laporan Harian</button>
    <button type="button" data-tab="dok">Dokumen Penting</button>
  </nav>
  <div id="tabBbm" class="tabpane">
  <section class="cards">
    <div class="card"><span id="sTglLbl">Transaksi Hari Ini</span><b id="sN">0</b></div>
    <div class="card"><span>Total Tabung Hari Ini</span><b id="sB">0</b></div>
    <div class="card red"><span>Total Penyaluran Hari Ini</span><b id="sT">0 kg</b></div>
  </section>

  <section class="panel">
    <h2 id="formTitle">Tambah Transaksi</h2>
    <form id="frm" autocomplete="off">
      <input type="hidden" id="id">
      <label>Tanggal<input type="date" id="tanggal" required></label>
      <label class="lebar">Agen<select id="agen" required></select></label>
      <label>Plat Nomor<input id="plat" maxlength="15" placeholder="AG 1234 XX" style="text-transform:uppercase"></label>
      <label>Total Tabung<select id="tabung" required>
        <option>560</option><option>1120</option><option>1680</option><option>2240</option><option>2800</option></select></label>
      <label>Penyaluran (tabung &times; 3 kg)<input id="kg" readonly></label>
      <label>Nama Sopir<input id="sopir" maxlength="100" required placeholder="Nama sopir"></label>
      <div class="actions">
        <button class="btn primary" type="submit">Simpan</button>
        <button class="btn ghost" type="button" id="btnBatal">Batal</button>
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="filters">
      <input id="q" placeholder="Cari agen / plat / sopir">
      <input type="date" id="dari" title="Dari tanggal">
      <input type="date" id="sampai" title="Sampai tanggal">
      <button class="btn blue" id="btnCari">Filter</button>
    </div>
    <div class="filters">
      <button class="btn ghost" type="button" id="btnXlsTgl" title="Pakai kolom Dari/Sampai di atas. Kosong = hari ini.">Unduh Excel (per tanggal)</button>
      <input type="month" id="bulan" title="Pilih bulan">
      <button class="btn ghost" type="button" id="btnXlsBln">Unduh Excel (per bulan)</button>
    </div>
    <div class="scroll">
      <table>
        <thead><tr><th>Tanggal</th><th>Agen</th><th>Plat</th>
          <th class="num">Total Tabung</th><th class="num">Penyaluran (kg)</th><th>Sopir</th><th></th></tr></thead>
        <tbody id="tbody"></tbody>
        <tfoot id="tfoot"></tfoot>
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


<script>
const $ = id => document.getElementById(id);
const KG_PER_TABUNG = 3;
const angka = n => Number(n).toLocaleString('id-ID');
const tglId = s => s ? s.split('-').reverse().join('/') : '';

const CSRF = <?= json_encode(csrf_token()) ?>;

async function api(action, body, qs = '') {
  const res = await fetch(`penyaluran_api.php?action=${action}${qs}`, body
    ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) }
    : undefined);
  if (res.status === 401) { location.href = 'login.php'; throw new Error('Sesi berakhir'); }
  let data = {};
  try { data = await res.json(); } catch (e) {}
  if (!res.ok) throw new Error(data.error || 'Terjadi kesalahan (' + res.status + ')');
  return data;
}

function td(text, cls) {
  const c = document.createElement('td');
  c.textContent = text;
  if (cls) c.className = cls;
  return c;
}

const hariIni = () => new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);

// ----- Agen: pilihan dari List Agen -----
async function loadAgen() {
  const res = await fetch('agen_api.php?action=list', { credentials: 'same-origin' });
  if (res.status === 401) { location.href = 'login.php'; return; }
  const rows = res.ok ? await res.json() : [];
  const sel = $('agen');
  sel.innerHTML = '';
  if (!rows.length) {
    sel.add(new Option('Belum ada agen (isi di List Agen)', ''));
    return;
  }
  sel.add(new Option('— Pilih agen —', ''));
  rows.forEach(a => sel.add(new Option(a.nama, a.nama)));
}

// ----- Penyaluran = Total Tabung x 3 kg -----
function hitung() {
  $('kg').value = angka((+$('tabung').value || 0) * KG_PER_TABUNG) + ' kg';
}
$('tabung').onchange = hitung;

// ----- Daftar & ringkasan -----
async function loadList() {
  const qs = '&q=' + encodeURIComponent($('q').value)
           + '&dari=' + $('dari').value + '&sampai=' + $('sampai').value;
  const { rows, filter, today } = await api('list', null, qs);

  $('sTglLbl').textContent = 'Transaksi Hari Ini (' + tglId(today.tanggal) + ')';
  $('sN').textContent = angka(today.n);
  $('sB').textContent = angka(today.tabung);
  $('sT').textContent = angka(today.kg) + ' kg';

  const tb = $('tbody'), tf = $('tfoot');
  tb.innerHTML = ''; tf.innerHTML = '';
  if (!rows.length) {
    tb.innerHTML = '<tr><td class="empty" colspan="8">Belum ada data</td></tr>';
    return;
  }
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.append(td(tglId(r.tanggal)), td(r.agen), td(r.plat || '-'),
      td(angka(r.tabung), 'num'), td(angka(r.kg), 'num'), td(r.sopir || '-'));
    const act = document.createElement('td');
    if (r.can) {
      const e = document.createElement('button');
      e.className = 'btn ghost sm'; e.textContent = 'Edit';
      e.onclick = () => edit(r);
      const d = document.createElement('button');
      d.className = 'btn ghost sm'; d.textContent = 'Hapus';
      d.onclick = () => hapus(r);
      act.append(e, ' ', d);
    }
    tr.append(act);
    tb.append(tr);
  });
  const ft = document.createElement('tr');
  const lbl = td('Total (sesuai filter): ' + angka(filter.n) + ' transaksi');
  lbl.colSpan = 3;
  ft.append(lbl, td(angka(filter.tabung), 'num'), td(angka(filter.kg), 'num'), td(''), td(''));
  tf.append(ft);
}

function edit(r) {
  $('formTitle').textContent = 'Edit Transaksi #' + r.id;
  $('id').value = r.id;
  $('tanggal').value = r.tanggal;
  const sel = $('agen');
  if (![...sel.options].some(o => o.value === r.agen)) sel.add(new Option(r.agen + ' (tidak ada di List Agen)', r.agen));
  sel.value = r.agen;
  $('plat').value = r.plat;
  $('tabung').value = String(r.tabung);
  $('sopir').value = r.sopir;
  hitung();
  scrollTo({ top: 0, behavior: 'smooth' });
}

async function hapus(r) {
  if (!confirm(`Hapus transaksi ${r.agen} (${tglId(r.tanggal)})?`)) return;
  try { await api('delete', { id: r.id }); loadList(); } catch (e) { alert(e.message); }
}

function resetForm() {
  $('frm').reset();
  $('id').value = '';
  $('formTitle').textContent = 'Tambah Transaksi';
  $('tanggal').value = hariIni();
  $('agen').value = '';
  $('tabung').value = '560';
  hitung();
}

$('frm').addEventListener('submit', async ev => {
  ev.preventDefault();
  try {
    await api('save', {
      id: $('id').value || null,
      tanggal: $('tanggal').value,
      agen: $('agen').value,
      plat: $('plat').value,
      tabung: $('tabung').value,
      sopir: $('sopir').value
    });
    resetForm();
    loadList();
  } catch (e) { alert(e.message); }
});

$('btnBatal').onclick = resetForm;
$('btnCari').onclick = () => loadList().catch(e => alert(e.message));
$('q').addEventListener('keydown', e => { if (e.key === 'Enter') loadList().catch(er => alert(er.message)); });

// ----- Rekap Excel -----
async function unduhXlsx(qs) {
  try {
    const res = await fetch('penyaluran_export.php?' + qs, { credentials: 'same-origin' });
    if (res.status === 401) { location.href = 'login.php'; return; }
    if (!res.ok) throw new Error((await res.text()) || 'Gagal membuat file Excel');
    const m = /filename="([^"]+)"/.exec(res.headers.get('Content-Disposition') || '');
    const url = URL.createObjectURL(await res.blob());
    const a = document.createElement('a');
    a.href = url; a.download = m ? m[1] : 'rekap-penyaluran.xlsx';
    document.body.append(a); a.click(); a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 2000);
  } catch (e) { alert(e.message); }
}
$('btnXlsTgl').onclick = () => unduhXlsx('mode=tanggal&dari=' + $('dari').value + '&sampai=' + $('sampai').value);
$('btnXlsBln').onclick = () => {
  if (!$('bulan').value) { alert('Pilih bulannya dulu.'); $('bulan').focus(); return; }
  unduhXlsx('mode=bulan&bulan=' + $('bulan').value);
};

(async () => {
  try {
    await loadAgen();
    resetForm();
    $('bulan').value = hariIni().slice(0, 7);
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
  <script src="app_ext.js"></script>
</body>
</html>
