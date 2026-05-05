@extends('layouts.app')
@section('title', 'Kasir')
@section('page-title', 'Kasir')
@section('page-subtitle', 'Proses transaksi penjualan')

@push('styles')
<style>
/* ── Layout utama POS ────────────────────────────────────── */
.pos-wrap {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 16px;
  height: calc(100vh - var(--nav-h) - 96px);
  min-height: 0;
}

/* ── Kiri: area produk ───────────────────────────────────── */
.produk-wrap {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-height: 0;
  overflow: hidden;
}

.produk-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
  gap: 16px;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
  padding-right: 4px;
  padding-bottom: 10px;
  align-content: stretch;
}
/* ── Produk card ─────────────────────────────────────────── */
.produk-card {
  background: var(--surface);
  border: 1.5px solid var(--border);
  border-radius: 12px;
  padding: 14px;

  display: flex;              
  flex-direction: column;     

  height: 100%;               
  min-height: 140px;          

  cursor: pointer;
  transition: all .18s ease;
  user-select: none;
  position: relative;
  overflow: hidden;
}
.produk-card:hover:not(.sold-out) {
  border-color: var(--teal);
  box-shadow: var(--shadow);
  transform: translateY(-2px);
}
.produk-card:active:not(.sold-out) { transform: scale(.97); }
.produk-card.sold-out { opacity: .45; cursor: not-allowed; }

.produk-kat {
  font-size: 10px; font-weight: 600; color: var(--text-4);
  text-transform: uppercase; letter-spacing: .07em; margin-bottom: 7px;
}
.produk-nama {
  font-size: 13px; font-weight: 600; color: var(--text-1);
  line-height: 1.3; margin-bottom: 8px;
  display: -webkit-box; -webkit-line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden;
}
.produk-harga {
  font-family: 'Geist Mono', monospace;
  font-size: 13.5px; font-weight: 600; color: var(--teal);
}
.produk-stok { font-size: 11px; color: var(--text-4); margin-top: auto; }
.badge-diskon {
  position: absolute; top: 8px; right: 8px;
  background: var(--green-light); color: var(--green);
  font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 20px;
}

/* ── Kanan: panel keranjang ──────────────────────────────── */
.cart-panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  min-height: 0;
}

.cart-head {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
}

.cart-items-wrap {
  flex: 1;
  overflow-y: auto;
  min-height: 0;
}

.cart-empty {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 10px; height: 100%; padding: 40px 20px;
  color: var(--text-4); text-align: center;
}
.cart-empty svg { width: 36px; height: 36px; opacity: .3; }
.cart-empty p   { font-size: 13px; line-height: 1.5; }

.cart-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 14px;
  border-bottom: 1px solid var(--border);
  transition: background .1s;
}
.cart-item:hover { background: var(--surface-2); }
.cart-item:last-child { border-bottom: none; }

.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name {
  font-size: 13px; font-weight: 600; color: var(--text-1);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.cart-item-price { font-size: 12px; color: var(--text-3); margin-top: 1px; }

.qty-control { display: flex; align-items: center; gap: 5px; flex-shrink: 0; }
.qty-btn {
  width: 24px; height: 24px; border-radius: 6px;
  border: 1.5px solid var(--border); background: var(--surface);
  color: var(--text-2); font-size: 15px; font-weight: 600;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all .12s; line-height: 1; padding: 0;
}
.qty-btn:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-light); }
.qty-val {
  font-family: 'Geist Mono', monospace;
  font-size: 13px; font-weight: 600;
  min-width: 22px; text-align: center; color: var(--text-1);
}

.cart-item-sub {
  font-family: 'Geist Mono', monospace;
  font-size: 13px; font-weight: 600; color: var(--text-1);
  min-width: 72px; text-align: right; flex-shrink: 0;
}

.cart-footer {
  padding: 14px 16px;
  border-top: 1px solid var(--border);
  flex-shrink: 0;
}
.summary-row {
  display: flex; justify-content: space-between;
  align-items: center; padding: 4px 0;
}
.summary-label { font-size: 13px; color: var(--text-3); }
.summary-val   { font-family: 'Geist Mono', monospace; font-size: 13px; color: var(--text-2); }
.divider-thin  { height: 1px; background: var(--border); margin: 8px 0; }
.total-label   { font-size: 14px; font-weight: 700; color: var(--text-1); }
.total-val     { font-family: 'Geist Mono', monospace; font-size: 20px; font-weight: 700; color: var(--teal); }

.metode-row { display: flex; gap: 6px; margin: 12px 0; }
.metode-btn {
  flex: 1; padding: 9px 8px; border-radius: 8px;
  border: 1.5px solid var(--border); background: var(--surface);
  color: var(--text-3); font-size: 12.5px; font-weight: 500;
  cursor: pointer; transition: all .15s; font-family: inherit;
  display: flex; align-items: center; justify-content: center; gap: 5px;
}
.metode-btn svg   { width: 13px; height: 13px; flex-shrink: 0; }
.metode-btn:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-light); }
.metode-btn.active { border-color: var(--teal); color: var(--teal); background: var(--teal-light); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="pos-wrap">

  {{-- ══════════ KIRI: Daftar Produk ══════════ --}}
  <div class="produk-wrap">
    <div style="display:flex;gap:8px;flex-shrink:0">
      <div class="input-icon-wrap" style="flex:1">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input id="s-search" type="text" class="input"
               placeholder="Cari produk atau scan barcode…" autofocus>
      </div>
      <select id="s-kat" class="input" style="width:170px">
        <option value="">Semua Kategori</option>
      </select>
    </div>

    <div id="produk-grid" class="produk-grid">
      @for ($i = 0; $i < 12; $i++)
      <div class="produk-card" style="animation:slideUp .4s {{ $i * .04 }}s ease both">
        <div class="skel" style="height:10px;width:50%;margin-bottom:8px"></div>
        <div class="skel" style="height:13px;width:85%;margin-bottom:6px"></div>
        <div class="skel" style="height:13px;width:65%;margin-bottom:10px"></div>
        <div class="skel" style="height:16px;width:55%"></div>
      </div>
      @endfor
    </div>
  </div>

  {{-- ══════════ KANAN: Keranjang ══════════ --}}
  <div class="cart-panel">

    <div class="cart-head">
      <div style="display:flex;align-items:center;gap:9px">
        <div style="width:32px;height:32px;border-radius:9px;background:var(--teal-light);
                    border:1px solid var(--teal-mid);display:flex;align-items:center;justify-content:center">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2"
               style="width:15px;height:15px">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
        </div>
        <div>
          <p style="font-size:13.5px;font-weight:700;color:var(--text-1)">Keranjang</p>
          <p id="cart-count" style="font-size:11.5px;color:var(--text-3)">0 item</p>
        </div>
      </div>
      <button id="btn-clear" onclick="clearCart()" class="btn btn-danger btn-sm" style="display:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             style="width:13px;height:13px">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
        </svg>
        Kosongkan
      </button>
    </div>

    {{-- Area item — diisi JS, TIDAK ada cart-empty di HTML --}}
    <div class="cart-items-wrap" id="cart-items"></div>

    <div class="cart-footer">
      <div class="summary-row">
        <span class="summary-label">Subtotal</span>
        <span class="summary-val" id="s-subtotal">Rp 0</span>
      </div>
      <div class="summary-row">
        <span class="summary-label">Diskon</span>
        <span class="summary-val" id="s-diskon" style="color:var(--green)">- Rp 0</span>
      </div>
      <div class="divider-thin"></div>
      <div class="summary-row">
        <span class="total-label">Total</span>
        <span class="total-val" id="s-total">Rp 0</span>
      </div>

      <div class="metode-row">
        <button class="metode-btn active" data-m="cash" onclick="setMetode(this)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/>
          </svg>
          Cash
        </button>
        <button class="metode-btn" data-m="cashless" onclick="setMetode(this)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
          </svg>
          Non-Tunai
        </button>
        <button class="metode-btn" data-m="qris" onclick="setMetode(this)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
            <path d="M14 14h.01M18 14h3M14 18v3M18 18h3v3"/>
          </svg>
          QRIS
        </button>
      </div>

      <button id="btn-checkout" onclick="openCheckout()" class="btn btn-primary btn-lg"
              style="width:100%;justify-content:center;opacity:.4;pointer-events:none" disabled>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             style="width:16px;height:16px">
          <polyline points="9 11 12 14 22 4"/>
          <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
        </svg>
        Proses Pembayaran
      </button>
    </div>
  </div>
</div>

{{-- ══════════ MODAL: Checkout ══════════ --}}
<div id="modal-checkout" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <div class="modal-title">Konfirmasi Pembayaran</div>
      <button class="modal-close" onclick="closeModal('modal-checkout')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="modal-body">
      <div style="background:var(--surface-2);border:1px solid var(--border);
                  border-radius:10px;padding:14px 18px;margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px">
          <span style="color:var(--text-3)">Subtotal</span>
          <span id="m-sub" style="color:var(--text-2)">—</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px">
          <span style="color:var(--text-3)">Diskon</span>
          <span id="m-dis" style="color:var(--green)">—</span>
        </div>
        <div style="height:1px;background:var(--border);margin-bottom:8px"></div>
        <div style="display:flex;justify-content:space-between;align-items:baseline">
          <span style="font-size:15px;font-weight:700;color:var(--text-1)">Total</span>
          <span id="m-tot"
                style="font-family:'Geist Mono',monospace;font-size:22px;font-weight:700;color:var(--teal)">—</span>
        </div>
      </div>
      <div id="cash-field">
        <div class="field" style="margin-bottom:10px">
          <label class="field-label">Uang Diterima</label>
          <input type="number" id="f-bayar" class="input"
                 placeholder="Masukkan jumlah uang"
                 oninput="hitungKembali()"
                 style="font-size:16px;font-weight:600">
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px" id="quick-amounts"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;
                    padding:10px 14px;background:var(--green-light);
                    border:1px solid rgba(34,197,94,.2);border-radius:9px">
          <span style="font-size:13px;color:var(--text-3)">Kembalian</span>
          <span id="kembali-val"
                style="font-family:'Geist Mono',monospace;font-size:18px;font-weight:700;color:var(--green)">
            Rp 0
          </span>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-checkout')">Batal</button>
      <button id="btn-bayar" class="btn btn-primary" onclick="prosesBayar()"
              style="min-width:140px;justify-content:center">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             style="width:15px;height:15px">
          <polyline points="9 11 12 14 22 4"/>
        </svg>
        Bayar Sekarang
      </button>
    </div>
  </div>
</div>

{{-- ══════════ MODAL: Sukses ══════════ --}}
<div id="modal-sukses" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:380px">
    <div style="padding:32px 28px;text-align:center">
      <div style="width:56px;height:56px;background:var(--green-light);
                  border:1px solid rgba(34,197,94,.25);border-radius:16px;
                  display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <svg viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"
             style="width:26px;height:26px">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <h3 style="font-size:18px;font-weight:800;margin-bottom:6px">Transaksi Berhasil!</h3>
      <p id="sukses-nomor"
         style="font-size:12.5px;color:var(--text-3);font-family:'Geist Mono',monospace"></p>
    </div>
    <div id="sukses-detail" style="padding:0 24px 20px;font-size:13px"></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeStruk()">Tutup</button>
      <button class="btn btn-primary" onclick="cetakStruk()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             style="width:14px;height:14px">
          <polyline points="6 9 6 2 18 2 18 9"/>
          <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
          <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Cetak Struk
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// ── STATE ──────────────────────────────────────────────────
let cart       = [];
let metode     = 'cash';
let grandTotal = 0;
let lastTrxId  = null;

// ── INIT ───────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderCart();      // tampilkan empty state saat pertama load
  loadKategori();
  loadProduk();
});

// ── Load kategori ──────────────────────────────────────────
async function loadKategori() {
  const res = await api('/kategori');
  const sel = document.getElementById('s-kat');
  (res?.data || []).forEach(k => {
    const opt       = document.createElement('option');
    opt.value       = k.kategori_id;
    opt.textContent = k.nama_kategori;
    sel.appendChild(opt);
  });
}

// ── Load produk dari API ───────────────────────────────────
async function loadProduk(search = '', katId = '') {
  const grid   = document.getElementById('produk-grid');
  const params = new URLSearchParams({ per_page: 80, search, aktif_saja: 1 });
  if (katId) params.set('kategori_id', katId);

  const res   = await api('/produk?' + params);
  const items = res?.data || [];

  if (!items.length) {
    grid.innerHTML = `
      <div style="grid-column:1/-1">
        <div class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p>Produk tidak ditemukan</p>
        </div>
      </div>`;
    return;
  }

  grid.innerHTML = items.map(p => {
    const stok  = p.stok?.jumlah_stok ?? 0;
    const isOut = stok <= 0;
    const isLow = stok > 0 && stok <= (p.stok?.stok_minimal ?? 5);

    const stokLabel = isOut
      ? `<span style="color:var(--red);font-weight:600">Habis</span>`
      : isLow
        ? `<span style="color:var(--amber);font-weight:600">Sisa ${stok}</span>`
        : `Stok: ${stok}`;

    // Escape nama untuk onclick string attribute
    const namaEsc = p.nama_produk
      .replace(/\\/g, '\\\\')
      .replace(/'/g, "\\'")
      .replace(/"/g, '&quot;');

    return `
  <div class="produk-card${isOut ? ' sold-out' : ''}"
       onclick='addToCart(${p.produk_id}, ${JSON.stringify(p.nama_produk)}, ${p.harga_jual}, ${p.diskon_persen || 0}, ${stok})'>
    <div class="produk-kat">${p.kategori?.nama_kategori || 'Umum'}</div>
    <div class="produk-nama">${p.nama_produk}</div>
    <div class="produk-harga">${rupiah(p.harga_jual)}</div>
    <div class="produk-stok">${stokLabel}</div>
    ${parseFloat(p.diskon_persen) > 0
      ? `<span class="badge-diskon">-${p.diskon_persen}%</span>`
      : ''}
  </div>`;
  }).join('');
}

// ── Tambah ke keranjang ────────────────────────────────────
function addToCart(id, nama, harga, diskon, stok) {
  const existing = cart.find(i => i.produk_id === id);

  if (existing) {
    // Sudah ada → naikkan qty
    if (existing.jumlah >= stok) {
      toast('Stok tidak mencukupi!', 'error');
      return;
    }
    existing.jumlah++;
  } else {
    // Belum ada → tambahkan baru
    cart.push({
      produk_id    : id,
      nama         : nama,
      harga        : parseFloat(harga),
      diskon_persen: parseFloat(diskon || 0),
      jumlah       : 1,
      stok         : parseInt(stok),
    });
  }

  renderCart();
}

// ── Ubah qty dengan tombol +/- ─────────────────────────────
function changeQty(id, delta) {
  const idx = cart.findIndex(i => i.produk_id === id);
  if (idx === -1) return;

  cart[idx].jumlah += delta;

  if (cart[idx].jumlah <= 0) {
    cart.splice(idx, 1);
  } else if (cart[idx].jumlah > cart[idx].stok) {
    cart[idx].jumlah = cart[idx].stok;
    toast('Jumlah melebihi stok tersedia!', 'error');
  }

  renderCart();
}

// ── Hapus satu item ────────────────────────────────────────
function removeItem(id) {
  cart = cart.filter(i => i.produk_id !== id);
  renderCart();
}

// ── Kosongkan semua ────────────────────────────────────────
async function clearCart() {
  if (!cart.length) return;
  if (!(await confirmDialog('Kosongkan semua item dari keranjang?'))) return;
  cart = [];
  renderCart();
}

// ── Render ulang tampilan keranjang ────────────────────────
function renderCart() {
  const wrap = document.getElementById('cart-items');
  const btn  = document.getElementById('btn-checkout');
  const clr  = document.getElementById('btn-clear');

  // Bersihkan selalu dulu
  wrap.innerHTML = '';

  if (!cart.length) {
    // Tampilkan empty state
    wrap.innerHTML = `
      <div class="cart-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <p>Keranjang kosong.<br>Pilih produk untuk mulai.</p>
      </div>`;
    btn.disabled            = true;
    btn.style.opacity       = '.4';
    btn.style.pointerEvents = 'none';
    clr.style.display       = 'none';
    document.getElementById('cart-count').textContent = '0 item';
    updateTotals(0, 0);
    return;
  }

  // Ada isi
  clr.style.display       = '';
  btn.disabled            = false;
  btn.style.opacity       = '1';
  btn.style.pointerEvents = '';

  let sub = 0;
  let dis = 0;

  wrap.innerHTML = cart.map(item => {
    const diskonAmt = (item.diskon_persen / 100) * item.harga * item.jumlah;
    const subtotal  = (item.harga * item.jumlah) - diskonAmt;
    sub += item.harga * item.jumlah;
    dis += diskonAmt;

    return `
      <div class="cart-item">
        <div class="cart-item-info">
          <div class="cart-item-name">${item.nama}</div>
          <div class="cart-item-price">
            ${rupiah(item.harga)}
            ${item.diskon_persen > 0
              ? `<span style="color:var(--green)"> -${item.diskon_persen}%</span>`
              : ''}
          </div>
        </div>
        <div class="qty-control">
          <button class="qty-btn" onclick="changeQty(${item.produk_id}, -1)">−</button>
          <span class="qty-val">${item.jumlah}</span>
          <button class="qty-btn" onclick="changeQty(${item.produk_id}, 1)">+</button>
        </div>
        <div style="flex-shrink:0">
          <div class="cart-item-sub">${rupiah(subtotal)}</div>
          <div style="text-align:right;margin-top:3px">
            <button onclick="removeItem(${item.produk_id})"
                    style="font-size:11px;color:var(--red);background:none;
                           border:none;cursor:pointer;padding:0;font-family:inherit">
              hapus
            </button>
          </div>
        </div>
      </div>`;
  }).join('');

  document.getElementById('cart-count').textContent =
    cart.reduce((s, i) => s + i.jumlah, 0) + ' item';

  updateTotals(sub, dis);
}

// ── Update label total ─────────────────────────────────────
function updateTotals(sub, dis) {
  grandTotal = sub - dis;
  document.getElementById('s-subtotal').textContent = rupiah(sub);
  document.getElementById('s-diskon').textContent   = '- ' + rupiah(dis);
  document.getElementById('s-total').textContent    = rupiah(grandTotal);
}

// ── Pilih metode bayar ─────────────────────────────────────
function setMetode(btn) {
  document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  metode = btn.dataset.m;
}

// ── Buka modal checkout ────────────────────────────────────
function openCheckout() {
  if (!cart.length) return;

  let sub = 0, dis = 0;
  cart.forEach(i => {
    sub += i.harga * i.jumlah;
    dis += (i.diskon_persen / 100) * i.harga * i.jumlah;
  });
  grandTotal = sub - dis;

  document.getElementById('m-sub').textContent = rupiah(sub);
  document.getElementById('m-dis').textContent = '- ' + rupiah(dis);
  document.getElementById('m-tot').textContent = rupiah(grandTotal);

  // Tombol nominal cepat
  const rounds  = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
  const amounts = [...new Set(
    rounds.map(r => Math.ceil(grandTotal / r) * r).filter(a => a >= grandTotal)
  )].slice(0, 4);

  document.getElementById('quick-amounts').innerHTML = amounts.map(a =>
    `<button onclick="document.getElementById('f-bayar').value=${a};hitungKembali()"
             class="chip">${rupiah(a)}</button>`
  ).join('');

  document.getElementById('cash-field').style.display = metode === 'cash' ? '' : 'none';
  document.getElementById('f-bayar').value            = '';
  document.getElementById('kembali-val').textContent  = 'Rp 0';

  openModal('modal-checkout');
  if (metode === 'cash') {
    setTimeout(() => document.getElementById('f-bayar').focus(), 150);
  }
}

// ── Hitung kembalian realtime ──────────────────────────────
function hitungKembali() {
  const bayar = parseFloat(document.getElementById('f-bayar').value) || 0;
  const kemb  = bayar - grandTotal;
  const el    = document.getElementById('kembali-val');
  el.textContent = rupiah(Math.max(0, kemb));
  el.style.color = kemb < 0 ? 'var(--red)' : 'var(--green)';
}

// ── Proses pembayaran ──────────────────────────────────────
async function prosesBayar() {
  const bayar = metode === 'cash'
    ? parseFloat(document.getElementById('f-bayar').value) || 0
    : grandTotal;

  if (metode === 'cash' && bayar < grandTotal) {
    toast('Uang yang diterima kurang dari total!', 'error');
    return;
  }

  const btn = document.getElementById('btn-bayar');
  setLoading(btn, true);

  try {
    // 1. Buat transaksi pending
    const r1 = await api('/transaksi', {
      method: 'POST',
      body: JSON.stringify({
        items            : cart.map(i => ({ produk_id: i.produk_id, jumlah: i.jumlah })),
        metode_pembayaran: metode,
      }),
    });
    if (!r1?.success) throw new Error(r1?.message || 'Gagal membuat transaksi');

    // 2. Selesaikan — stok berkurang otomatis
    const r2 = await api(`/transaksi/${r1.data.transaksi_id}/selesaikan`, {
      method: 'POST',
      body: JSON.stringify({ jumlah_bayar: bayar, metode_pembayaran: metode }),
    });
    if (!r2?.success) throw new Error(r2?.message || 'Gagal menyelesaikan transaksi');

    lastTrxId = r1.data.transaksi_id;
    closeModal('modal-checkout');
    showSukses(r2.data, bayar);
    cart = [];
    renderCart();
    // Reload produk agar stok terupdate
    loadProduk(
      document.getElementById('s-search').value,
      document.getElementById('s-kat').value
    );
    toast('Transaksi berhasil!', 'success');

  } catch (err) {
    toast(err.message, 'error');
  } finally {
    setLoading(btn, false);
  }
}

// ── Tampilkan modal sukses + detail ───────────────────────
function showSukses(trx, bayar) {
  document.getElementById('sukses-nomor').textContent = trx.nomor_transaksi;
  const kemb = bayar - parseFloat(trx.total_pembayaran);

  document.getElementById('sukses-detail').innerHTML = `
    <div style="border:1px solid var(--border);border-radius:10px;
                padding:12px 14px;margin-bottom:12px">
      ${(trx.details || []).map(d => `
        <div style="display:flex;justify-content:space-between;
                    margin-bottom:5px;font-size:12.5px">
          <span style="color:var(--text-3)">${d.produk?.nama_produk} ×${d.jumlah}</span>
          <span style="color:var(--text-1);font-weight:600">${rupiah(d.subtotal)}</span>
        </div>`).join('')}
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:13px">
      <span style="color:var(--text-3)">Total</span>
      <span style="font-weight:700">${rupiah(trx.total_pembayaran)}</span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:13px">
      <span style="color:var(--text-3)">Dibayar</span>
      <span>${rupiah(bayar)}</span>
    </div>
    ${kemb > 0 ? `
    <div style="display:flex;justify-content:space-between;align-items:center;
                background:var(--green-light);border:1px solid rgba(34,197,94,.2);
                border-radius:8px;padding:8px 12px;font-size:14px;margin-top:8px">
      <span style="color:var(--text-3)">Kembalian</span>
      <span style="font-weight:700;color:var(--green);
                   font-family:'Geist Mono',monospace">${rupiah(kemb)}</span>
    </div>` : ''}`;

  openModal('modal-sukses');
}

function closeStruk() { closeModal('modal-sukses'); }
function cetakStruk()  {
  if (lastTrxId) window.open(`/kasir/struk/${lastTrxId}`, '_blank', 'width=420,height=600');
}

const dSearch = debounce((search, kat) => loadProduk(search, kat), 300);

const searchInput = document.getElementById('s-search');
const katSelect = document.getElementById('s-kat');

searchInput.addEventListener('input', e =>
  dSearch(e.target.value, katSelect.value)
);

katSelect.addEventListener('change', e =>
  loadProduk(searchInput.value, e.target.value)
);

// ── Smart Barcode Scanner Intercept ──────────────────────────
// Alat scanner fisik (Barcode/QR) selalu mengirimkan tombol "Enter" secara otomatis setelah mengetik.
searchInput.addEventListener('keypress', async (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    const val = searchInput.value.trim();
    if (!val) return;
    
    // Jangan izinkan spam enter
    searchInput.disabled = true;
    
    try {
      // Lookup exact barcode agar scan tidak tertukar dengan hasil pencarian nama/kode.
      const res = await api(`/produk/barcode/${encodeURIComponent(val)}`);

      if (res?.success && res.data) {
        const p = res.data;
        const stok = parseInt(p.stok || 0);

        if (stok > 0) {
          addToCart(p.produk_id, p.nama_produk, p.harga_jual, p.diskon_persen, stok);
          toast(`(+) ${p.nama_produk}`, 'success');

          // Reset kolom input agar siap scan barang berikutnya.
          searchInput.value = '';
          loadProduk('', katSelect.value);
        } else {
          toast('Stok produk habis!', 'error');
        }
      } else {
        toast('Barcode/Produk tidak ditemukan!', 'error');
      }
    } catch (e) {
      toast('Terjadi kesalahan scan', 'error');
    } finally {
      searchInput.disabled = false;
      searchInput.focus(); // kembalikan fokus ke scanner
    }
  }
});
</script>
@endpush
