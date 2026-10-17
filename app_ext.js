/* Tambahan untuk app.php: List Agen, pilihan agen di form, tombol "Lihat" dokumen, tautan Media Beranda. */
(function () {
  'use strict';

  // 1) Tombol "List Agen" (dulu "Atur Harga") sekarang membuka halaman List Agen
  var btn = document.getElementById('btnHarga');
  if (!btn) {
    btn = Array.prototype.slice.call(document.querySelectorAll('button')).filter(function (b) {
      return /list agen/i.test(b.textContent);
    })[0];
  }
  if (btn) {
    btn.textContent = 'List Agen';
    btn.onclick = function () { location.href = 'agen.php'; };
    if (!btn.hidden) {            // tombol ini hanya tampil untuk master admin
      var a = document.createElement('a');
      a.className = 'btn ghost';
      a.href = 'media.php';
      a.textContent = 'Media Beranda';
      btn.insertAdjacentElement('afterend', a);
    }
  }

  // 2) Kolom "Agen" di form: pilihan dari List Agen
  var frm = document.getElementById('frm');
  var lab = frm && Array.prototype.slice.call(frm.querySelectorAll('label')).filter(function (l) {
    return /^\s*agen\b/i.test(l.textContent);
  })[0];
  var inp = (lab && lab.querySelector('input')) || document.getElementById('pelanggan');
  var daftar = [];

  if (inp) {
    var dl = document.createElement('datalist');
    dl.id = 'daftarAgen';
    document.body.appendChild(dl);
    inp.setAttribute('list', 'daftarAgen');
    inp.setAttribute('autocomplete', 'off');
    inp.placeholder = 'Pilih nama agen';

    fetch('agen_api.php?action=list', { credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : []; })
      .then(function (rows) {
        daftar = rows.map(function (x) { return String(x.nama).toLowerCase(); });
        rows.forEach(function (x) {
          var o = document.createElement('option');
          o.value = x.nama;
          dl.appendChild(o);
        });
      })
      .catch(function () {});

    // Hanya nama dari List Agen yang boleh disimpan (kalau daftarnya sudah terisi)
    frm.addEventListener('submit', function (ev) {
      if (daftar.length && daftar.indexOf(inp.value.trim().toLowerCase()) === -1) {
        ev.preventDefault();
        ev.stopImmediatePropagation();
        alert('Pilih nama agen dari List Agen. Master admin bisa menambah agen baru lewat tombol "List Agen".');
        inp.focus();
      }
    }, true);
  }

  // 3) Tombol "Lihat" (pratinjau) di daftar dokumen
  var body = document.getElementById('dokBody');
  if (body) {
    var tambahLihat = function () {
      Array.prototype.slice.call(body.querySelectorAll('a[href^="unduh.php?id="]')).forEach(function (u) {
        if (u.dataset.lihat) return;
        u.dataset.lihat = '1';
        var l = document.createElement('a');
        l.className = 'btn ghost sm';
        l.textContent = 'Lihat';
        l.href = u.getAttribute('href').replace('unduh.php', 'lihat.php');
        u.parentNode.insertBefore(l, u);
        u.parentNode.insertBefore(document.createTextNode(' '), u);
      });
    };
    new MutationObserver(tambahLihat).observe(body, { childList: true });
    tambahLihat();
  }
})();
