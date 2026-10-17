/* Tambahan untuk app.php: tombol List Agen, tombol "Lihat" dokumen, tautan Media Beranda. */
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
