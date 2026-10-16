<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pencatatan BBM</title>
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

</style>
</head>
<body>
<header class="top">
  <div class="brand">
    <span class="mark"><i class="r"></i><i class="b"></i><i class="g"></i></span>
    <div><h1>Pencatatan Penjualan BBM</h1><small>SERVER LOKAL &middot; HG680P</small></div>
  </div>
  <button class="btn ghost" id="btnHarga">Atur Harga</button>
</header>
<div class="stripe"></div>
<div class="ticker">
  <div class="ticker-track">
    <span>Selamat datang di sistem pencatatan penjualan BBM &bullet; Pastikan data liter dan plat nomor diisi dengan benar &bullet; Harga dapat diubah lewat tombol Atur Harga &bullet;</span>
    <span>Selamat datang di sistem pencatatan penjualan BBM &bullet; Pastikan data liter dan plat nomor diisi dengan benar &bullet; Harga dapat diubah lewat tombol Atur Harga &bullet;</span>
  </div>
</div>

<main>
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
        <thead><tr><th>Tanggal</th><th>Pelanggan</th><th>Plat</th><th>BBM</th>
          <th class="num">Liter</th><th class="num">Harga</th><th class="num">Total</th><th></th></tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
  </section>
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

async function api(action, body, qs = '') {
  const res = await fetch(`api.php?action=${action}${qs}`, body
    ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }
    : undefined);
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
    tb.innerHTML = '<tr><td class="empty" colspan="8">Belum ada data</td></tr>';
    return;
  }
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.append(td(r.tanggal), td(r.pelanggan), td(r.plat || '-'), td(r.bbm),
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

</script>
</body>
</html>
