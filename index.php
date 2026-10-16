<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pencatatan BBM</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="top">
  <div class="brand">
    <span class="mark"><i class="r"></i><i class="b"></i><i class="g"></i></span>
    <div><h1>Pencatatan Penjualan BBM</h1><small>Sistem lokal &middot; server STB</small></div>
  </div>
  <button class="btn ghost" id="btnHarga">Atur Harga</button>
</header>
<div class="stripe"></div>

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

<script src="assets/app.js"></script>
</body>
</html>
