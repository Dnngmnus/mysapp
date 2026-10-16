<?php
require __DIR__ . '/auth.php';
$me = require_admin();
page_head('Kelola User');
?>
<div class="stripe"></div>
<div class="page">
  <div class="bar"><h1>Kelola User</h1><a class="btn ghost" href="index.php">&larr; Kembali</a></div>
  <p class="muted">Setujui pendaftar baru, atur peran, reset password, atau hapus akun.</p>
  <div class="scroll"><table>
    <thead><tr><th>Username</th><th>Nama</th><th>Peran</th><th>Status</th><th>Daftar</th><th>Aksi</th></tr></thead>
    <tbody id="tb"></tbody>
  </table></div>
</div>
<script>
const CSRF = <?= json_encode(csrf_token()) ?>;
const ME = <?= (int)$me['id'] ?>;

async function api(action, body) {
  const res = await fetch('api.php?action=' + action, body
    ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF }, body: JSON.stringify(body) }
    : undefined);
  if (res.status === 401) { location.href = 'login.php'; throw new Error('Sesi berakhir'); }
  const d = await res.json();
  if (!res.ok) throw new Error(d.error || 'Terjadi kesalahan');
  return d;
}

function cell(text) { const c = document.createElement('td'); c.textContent = text; return c; }
function btn(label, cls, fn) {
  const b = document.createElement('button');
  b.className = 'btn sm ' + cls; b.textContent = label;
  b.onclick = async () => { try { await fn(); } catch (e) { alert(e.message); } };
  return b;
}
async function set(id, data) { await api('user_set', Object.assign({ id }, data)); load(); }

async function load() {
  const rows = await api('users');
  const tb = document.getElementById('tb');
  tb.innerHTML = '';
  rows.forEach(u => {
    const tr = document.createElement('tr');
    const st = document.createElement('td');
    const bd = document.createElement('span');
    bd.className = 'badge ' + u.status; bd.textContent = u.status;
    st.append(bd);
    tr.append(cell(u.username), cell(u.nama), cell(u.role === 'admin' ? 'Master Admin' : 'User'), st, cell(u.created_at));

    const act = document.createElement('td');
    if (u.id == ME) {
      act.textContent = '(akun Anda)';
    } else {
      if (u.status === 'aktif') act.append(btn('Nonaktifkan', 'ghost', () => set(u.id, { status: 'nonaktif' })));
      else act.append(btn(u.status === 'pending' ? 'Setujui' : 'Aktifkan', 'primary', () => set(u.id, { status: 'aktif' })));
      act.append(' ');
      const toAdmin = u.role !== 'admin';
      act.append(btn(toAdmin ? 'Jadikan Admin' : 'Jadikan User', 'ghost', async () => {
        if (!confirm((toAdmin ? 'Jadikan master admin: ' : 'Turunkan jadi user biasa: ') + u.username + '?')) return;
        await set(u.id, { role: toAdmin ? 'admin' : 'user' });
      }), ' ');
      act.append(btn('Reset Password', 'ghost', async () => {
        if (!confirm('Reset password ' + u.username + '?')) return;
        const r = await api('user_reset', { id: u.id });
        alert('Password baru untuk ' + u.username + ':\n\n' + r.password + '\n\nCatat sekarang, tidak akan ditampilkan lagi.');
      }), ' ');
      act.append(btn('Hapus', 'ghost', async () => {
        if (!confirm('Hapus akun ' + u.username + '? Data transaksinya tetap ada.')) return;
        await api('user_delete', { id: u.id });
        load();
      }));
    }
    tr.append(act);
    tb.append(tr);
  });
}
load().catch(e => alert(e.message));
</script>
</body></html>
