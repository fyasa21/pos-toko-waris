<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Struk</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Courier New', monospace; font-size:12px; background:#fff; color:#000; padding:10px; width:300px; }
    .center { text-align:center; }
    .bold { font-weight:bold; }
    .row { display:flex; justify-content:space-between; margin:3px 0; }
    .total-row { display:flex; justify-content:space-between; font-weight:bold; font-size:14px; margin:4px 0; }
    .dash { border-top:1px dashed #000; margin:6px 0; }
    .dot  { border-top:1px dotted #000; margin:4px 0; }
    @media print {
      .no-print { display:none; }
    }
  </style>
</head>
<body>
<div id="struk">
  <p class="center bold" id="s-toko" style="font-size:14px">Toko Waris</p>
  <p class="center" id="s-alamat" style="font-size:11px"></p>
  <p class="center" id="s-telp" style="font-size:11px"></p>
  <div class="dash"></div>
  <p class="center bold" id="s-nomor"></p>
  <p class="center" id="s-tgl" style="font-size:11px"></p>
  <p class="center" id="s-kasir" style="font-size:11px"></p>
  <div class="dash"></div>
  <div id="s-items"></div>
  <div class="dash"></div>
  <div class="row"><span>Subtotal</span><span id="s-sub"></span></div>
  <div class="row"><span>Diskon</span><span id="s-dis"></span></div>
  <div class="row"><span>Pajak</span><span id="s-pjk"></span></div>
  <div class="dot"></div>
  <div class="total-row"><span>TOTAL</span><span id="s-tot"></span></div>
  <div class="row"><span>Dibayar</span><span id="s-bayar"></span></div>
  <div class="row"><span>Kembali</span><span id="s-kmbli"></span></div>
  <div class="dash"></div>
  <p class="center" id="s-footer" style="font-size:11px;margin-top:4px"></p>
  <p class="center bold" style="margin-top:6px">*** Terima Kasih ***</p>
</div>

<div class="no-print" style="margin-top:16px;text-align:center;display:flex;gap:8px;justify-content:center">
  <button onclick="window.print()" style="padding:8px 20px;background:#0d9488;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px">🖨 Cetak</button>
  <button onclick="window.close()" style="padding:8px 20px;background:#f5f6f8;border:1px solid #e2e5ea;border-radius:6px;cursor:pointer;font-size:13px">Tutup</button>
</div>

<script>
const trxId = {{ $id ?? 0 }};
const token = localStorage.getItem('pos_token');
const rp = n => 'Rp ' + parseFloat(n||0).toLocaleString('id-ID');

async function load() {
  const res = await fetch(`/api/transaksi/${trxId}/struk`, {
    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
  });
  const data = await res.json();
  if (!data.success) { document.body.innerHTML = '<p>Struk tidak tersedia.</p>'; return; }
  const { toko, transaksi: t } = data.data;
  document.getElementById('s-toko').textContent   = toko.nama;
  document.getElementById('s-alamat').textContent = toko.alamat;
  document.getElementById('s-telp').textContent   = toko.telepon;
  document.getElementById('s-footer').textContent = toko.footer;
  document.getElementById('s-nomor').textContent  = t.nomor_transaksi;
  document.getElementById('s-tgl').textContent    = new Date(t.tanggal_transaksi).toLocaleString('id-ID');
  document.getElementById('s-kasir').textContent  = 'Kasir: ' + (t.user?.nama_lengkap||'—');
  document.getElementById('s-sub').textContent    = rp(t.total_harga);
  document.getElementById('s-dis').textContent    = '- ' + rp(t.total_diskon);
  document.getElementById('s-pjk').textContent    = rp(t.total_pajak);
  document.getElementById('s-tot').textContent    = rp(t.total_pembayaran);
  document.getElementById('s-bayar').textContent  = rp(t.jumlah_bayar);
  document.getElementById('s-kmbli').textContent  = rp(t.kembalian);
  document.getElementById('s-items').innerHTML =
    (t.details||[]).map(d=>`<div style="margin:2px 0"><div>${d.produk?.nama_produk||'—'}</div><div style="display:flex;justify-content:space-between;padding-left:8px;font-size:11px"><span>${rp(d.harga_satuan)} x ${d.jumlah}</span><span>${rp(d.subtotal)}</span></div></div>`).join('');
  document.title = 'Struk ' + t.nomor_transaksi;
  
  // Otomatis memunculkan dialog print browser setelah data selesai dirender
  setTimeout(() => {
    window.print();
  }, 200);
}
load();
</script>
</body>
</html>
