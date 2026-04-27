<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; background: #fff; color: #000; width: 300px; padding: 12px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; margin: 3px 0; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin: 4px 0; }
        @media print {
            body { width: 58mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div id="struk-content">
    <p class="center" style="font-size:14px;font-weight:bold" id="store-name">Toko Waris</p>
    <p class="center" id="store-address" style="font-size:11px"></p>
    <p class="center" id="store-phone" style="font-size:11px"></p>
    <div class="line"></div>
    <p class="center bold" id="trx-nomor">—</p>
    <p class="center" id="trx-tanggal" style="font-size:11px"></p>
    <p class="center" id="trx-kasir" style="font-size:11px"></p>
    <div class="line"></div>
    <div id="items-list"></div>
    <div class="line"></div>
    <div class="row"><span>Subtotal</span><span id="s-subtotal">—</span></div>
    <div class="row"><span>Diskon</span><span id="s-diskon">—</span></div>
    <div class="row"><span>Pajak</span><span id="s-pajak">—</span></div>
    <div class="line"></div>
    <div class="total-row"><span>TOTAL</span><span id="s-total">—</span></div>
    <div class="row"><span>Bayar</span><span id="s-bayar">—</span></div>
    <div class="row"><span>Kembali</span><span id="s-kembali">—</span></div>
    <div class="line"></div>
    <p class="center" id="store-footer" style="font-size:11px;margin-top:4px"></p>
    <p class="center" style="font-size:10px;margin-top:6px">*** Terima Kasih ***</p>
</div>
<br>
<div class="no-print" style="text-align:center;margin-top:12px">
    <button onclick="window.print()" style="padding:8px 20px;background:#000;color:#fff;border:none;cursor:pointer;border-radius:4px;font-size:13px">🖨 Cetak</button>
    <button onclick="window.close()" style="padding:8px 20px;background:#eee;border:none;cursor:pointer;border-radius:4px;font-size:13px;margin-left:8px">Tutup</button>
</div>

<script>
const trxId = {{ $id ?? 0 }};
const token = localStorage.getItem('pos_token');
const rupiah = n => 'Rp ' + parseFloat(n||0).toLocaleString('id-ID');

async function loadStruk() {
    const res = await fetch(`/api/transaksi/${trxId}/struk`, {
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (!data.success) { document.body.innerHTML = '<p>Struk tidak tersedia.</p>'; return; }

    const { toko, transaksi: t } = data.data;
    document.getElementById('store-name').textContent    = toko.nama;
    document.getElementById('store-address').textContent = toko.alamat;
    document.getElementById('store-phone').textContent   = toko.telepon;
    document.getElementById('store-footer').textContent  = toko.footer;
    document.getElementById('trx-nomor').textContent     = t.nomor_transaksi;
    document.getElementById('trx-tanggal').textContent   = new Date(t.tanggal_transaksi).toLocaleString('id-ID');
    document.getElementById('trx-kasir').textContent     = 'Kasir: ' + (t.user?.nama_lengkap || '—');
    document.getElementById('s-subtotal').textContent    = rupiah(t.total_harga);
    document.getElementById('s-diskon').textContent      = '-' + rupiah(t.total_diskon);
    document.getElementById('s-pajak').textContent       = rupiah(t.total_pajak);
    document.getElementById('s-total').textContent       = rupiah(t.total_pembayaran);
    document.getElementById('s-bayar').textContent       = rupiah(t.jumlah_bayar);
    document.getElementById('s-kembali').textContent     = rupiah(t.kembalian);

    document.getElementById('items-list').innerHTML = (t.details || []).map(d => `
        <div style="margin:3px 0">
            <div>${d.produk?.nama_produk || '—'}</div>
            <div class="row" style="padding-left:8px">
                <span>${rupiah(d.harga_satuan)} x ${d.jumlah}</span>
                <span>${rupiah(d.subtotal)}</span>
            </div>
        </div>
    `).join('');

    document.title = 'Struk - ' + t.nomor_transaksi;
}

loadStruk();
</script>
</body>
</html>
