@extends('layouts.app')
@section('title', 'Kasir')
@section('page-title', 'Point of Sale')
@section('page-subtitle', 'Proses transaksi penjualan')

@push('styles')
<style>
.produk-card {
    background: #0d1117; border: 1px solid #161b22; border-radius: 12px;
    padding: 14px; cursor: pointer; transition: all .2s; user-select: none;
}
.produk-card:hover { border-color: rgba(245,158,11,0.3); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.4); }
.produk-card:active { transform: translateY(0) scale(.98); }
.produk-card.out-of-stock { opacity:.4; pointer-events:none; }

.cart-item { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #161b22; }
.cart-item:last-child { border-bottom:none; }
.qty-btn {
    width:26px; height:26px; border-radius:6px; border:1px solid #30363d;
    background:#161b22; color:#c9d1d9; font-size:15px; cursor:pointer;
    display:flex;align-items:center;justify-content:center; transition:all .15s;
}
.qty-btn:hover { background:#21262d; border-color:#484f58; color:#fff; }

.metode-btn {
    flex:1; padding:11px; border-radius:9px; border:1px solid #30363d; background:transparent;
    color:#8b949e; font-size:13px; font-weight:500; cursor:pointer; transition:all .2s;
    display:flex; align-items:center; justify-content:center; gap:7px;
    font-family:'DM Sans',sans-serif;
}
.metode-btn.active { background:rgba(245,158,11,.12); border-color:rgba(245,158,11,.35); color:#fbbf24; }
.metode-btn:hover:not(.active) { background:#161b22; border-color:#484f58; color:#c9d1d9; }

#search-produk:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.08); }
</style>
@endpush

@section('content')
<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;height:calc(100vh - 110px)">

    {{-- ═══ KIRI: Produk ═══ --}}
    <div style="display:flex;flex-direction:column;gap:14px;overflow:hidden">

        {{-- Search + filter --}}
        <div style="display:flex;gap:10px">
            <div style="position:relative;flex:1">
                <svg style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#484f58;pointer-events:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="search-produk" type="text" placeholder="Cari produk / scan barcode..." class="pos-input" style="padding-left:40px" autofocus>
            </div>
            <select id="filter-kategori" class="pos-input" style="width:160px">
                <option value="">Semua Kategori</option>
            </select>
        </div>

        {{-- Produk grid --}}
        <div id="produk-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:12px;overflow-y:auto;flex:1;padding-right:2px">
            @for($i=0;$i<8;$i++)
            <div style="background:#0d1117;border:1px solid #161b22;border-radius:12px;padding:14px;animation:pulse 1.5s ease infinite">
                <div style="height:10px;background:#161b22;border-radius:4px;width:80%;margin-bottom:10px"></div>
                <div style="height:16px;background:#21262d;border-radius:4px;width:60%;margin-bottom:8px"></div>
                <div style="height:10px;background:#161b22;border-radius:4px;width:40%"></div>
            </div>
            @endfor
        </div>
    </div>

    {{-- ═══ KANAN: Keranjang ═══ --}}
    <div style="display:flex;flex-direction:column;background:#0d1117;border:1px solid #161b22;border-radius:14px;overflow:hidden">

        {{-- Header cart --}}
        <div style="padding:16px 20px;border-bottom:1px solid #161b22;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:9px">
                <div style="width:34px;height:34px;border-radius:9px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);display:flex;align-items:center;justify-content:center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="width:16px;height:16px"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                </div>
                <div>
                    <p class="font-display" style="font-size:14px;font-weight:700;color:#fff">Keranjang</p>
                    <p style="font-size:11px;color:#8b949e" id="cart-count-label">0 item</p>
                </div>
            </div>
            <button onclick="clearCart()" id="btn-clear" title="Kosongkan" style="width:30px;height:30px;border-radius:7px;background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.15);color:#fb7185;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;display:none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
            </button>
        </div>

        {{-- Cart items --}}
        <div id="cart-items" style="flex:1;overflow-y:auto;padding:8px 16px">
            <div id="cart-empty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:10px;padding:40px 0">
                <div style="width:52px;height:52px;border-radius:14px;background:#161b22;border:1px solid #21262d;display:flex;align-items:center;justify-content:center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#484f58" stroke-width="1.5" style="width:24px;height:24px"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                </div>
                <p style="font-size:13px;color:#484f58;text-align:center">Keranjang kosong.<br>Pilih produk untuk mulai.</p>
            </div>
        </div>

        {{-- Summary --}}
        <div style="padding:16px 20px;border-top:1px solid #161b22">
            <div style="display:flex;flex-direction:column;gap:7px;margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:#8b949e">Subtotal</span>
                    <span id="subtotal" style="color:#c9d1d9">Rp 0</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:#8b949e">Diskon</span>
                    <span id="total-diskon" style="color:#34d399">- Rp 0</span>
                </div>
                <div style="height:1px;background:#161b22;margin:4px 0"></div>
                <div style="display:flex;justify-content:space-between">
                    <span class="font-display" style="font-size:15px;font-weight:700;color:#fff">Total</span>
                    <span class="font-display" id="grand-total" style="font-size:18px;font-weight:700;color:#fbbf24">Rp 0</span>
                </div>
            </div>

            {{-- Metode bayar --}}
            <div style="display:flex;gap:8px;margin-bottom:12px" id="metode-buttons">
                <button class="metode-btn active" data-metode="cash" onclick="selectMetode(this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                    Cash
                </button>
                <button class="metode-btn" data-metode="cashless" onclick="selectMetode(this)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    QRIS/Non-Tunai
                </button>
            </div>

            <button onclick="openCheckout()" id="btn-checkout" class="btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:14px;border-radius:10px;opacity:.4;pointer-events:none" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Proses Pembayaran
            </button>
        </div>
    </div>
</div>

{{-- ═══ MODAL CHECKOUT ═══ --}}
<div id="modal-checkout" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:420px">
        <div style="padding:24px 28px;border-bottom:1px solid #161b22">
            <h3 class="font-display" style="font-size:17px;font-weight:700;color:#fff">Konfirmasi Pembayaran</h3>
        </div>
        <div style="padding:24px 28px;display:flex;flex-direction:column;gap:16px">
            <div style="background:#080b10;border:1px solid #161b22;border-radius:10px;padding:16px 20px">
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px"><span style="color:#8b949e">Subtotal</span><span id="m-subtotal" style="color:#c9d1d9">Rp 0</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13px"><span style="color:#8b949e">Diskon</span><span id="m-diskon" style="color:#34d399">- Rp 0</span></div>
                <div style="height:1px;background:#21262d;margin-bottom:10px"></div>
                <div style="display:flex;justify-content:space-between">
                    <span class="font-display" style="font-size:15px;font-weight:700;color:#fff">Total Bayar</span>
                    <span class="font-display" id="m-total" style="font-size:18px;font-weight:700;color:#fbbf24">Rp 0</span>
                </div>
            </div>

            <div id="cash-section">
                <label style="font-size:12px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:8px">Uang Diterima</label>
                <input type="number" id="jumlah-bayar" class="pos-input" placeholder="Masukkan jumlah uang" oninput="hitungKembalian()" style="font-size:16px;font-weight:600">
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap" id="quick-amounts"></div>
                <div style="display:flex;justify-content:space-between;margin-top:14px;padding:12px 16px;background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.15);border-radius:9px">
                    <span style="font-size:13px;color:#8b949e">Kembalian</span>
                    <span id="kembalian" class="font-display" style="font-size:16px;font-weight:700;color:#34d399">Rp 0</span>
                </div>
            </div>
        </div>
        <div style="padding:16px 28px 24px;display:flex;gap:10px">
            <button onclick="closeCheckout()" class="btn-secondary" style="flex:1;justify-content:center">Batal</button>
            <button onclick="prosesCheckout()" id="btn-bayar" class="btn-primary" style="flex:2;justify-content:center;padding:11px">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Bayar Sekarang
            </button>
        </div>
    </div>
</div>

{{-- ═══ MODAL STRUK ═══ --}}
<div id="modal-struk" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:380px">
        <div style="padding:28px;text-align:center">
            <div style="width:52px;height:52px;border-radius:14px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                <svg viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2" style="width:24px;height:24px"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="font-display" style="font-size:18px;font-weight:700;color:#fff;margin-bottom:6px">Transaksi Berhasil!</h3>
            <p style="font-size:13px;color:#8b949e" id="struk-nomor"></p>
        </div>
        <div id="struk-detail" style="padding:0 24px 20px;font-size:13px;max-height:300px;overflow-y:auto"></div>
        <div style="padding:16px 24px 24px;display:flex;gap:10px;border-top:1px solid #161b22">
            <button onclick="closeStruk()" class="btn-secondary" style="flex:1;justify-content:center">Tutup</button>
            <button onclick="printStruk()" class="btn-primary" style="flex:1;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = []; // [{produk_id, nama, harga, diskon, jumlah, stok}]
let currentTrxId = null;
let selectedMetode = 'cash';
let grandTotalVal = 0;

// ── Load produk ──────────────────────────────────────────
async function loadProduk(search = '', kategoriId = '') {
    const params = new URLSearchParams({ per_page: 60, search, aktif_saja: 1 });
    if (kategoriId) params.set('kategori_id', kategoriId);
    const res = await apiFetch('/produk?' + params);
    const items = res?.data || [];
    const grid = document.getElementById('produk-grid');

    if (!items.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#484f58;font-size:13px">Produk tidak ditemukan</div>`;
        return;
    }

    grid.innerHTML = items.map(p => {
        const stok = p.stok?.jumlah_stok ?? 0;
        const isOut = stok <= 0;
        const isLow = stok > 0 && stok <= (p.stok?.stok_minimal ?? 5);
        return `
        <div class="produk-card ${isOut ? 'out-of-stock' : ''}" onclick="addToCart(${JSON.stringify(p).replace(/"/g,"'")})">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:9.5px;background:#161b22;color:#8b949e;padding:2px 7px;border-radius:20px">${p.kategori?.nama_kategori || '—'}</span>
                ${isOut ? `<span style="font-size:9.5px;background:rgba(251,113,133,.1);color:#fb7185;padding:2px 7px;border-radius:20px;border:1px solid rgba(251,113,133,.2)">Habis</span>` :
                  isLow ? `<span style="font-size:9.5px;background:rgba(251,191,36,.1);color:#fbbf24;padding:2px 7px;border-radius:20px;border:1px solid rgba(251,191,36,.2)">Tipis: ${stok}</span>` :
                  `<span style="font-size:9.5px;color:#484f58">${stok}</span>`}
            </div>
            <p style="font-size:13px;font-weight:600;color:#e6edf3;line-height:1.3;margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${p.nama_produk}</p>
            <p class="font-display" style="font-size:14px;font-weight:700;color:#fbbf24">${rupiah(p.harga_jual)}</p>
            ${parseFloat(p.diskon_persen) > 0 ? `<p style="font-size:10px;color:#34d399;margin-top:2px">Diskon ${p.diskon_persen}%</p>` : ''}
        </div>`;
    }).join('');
}

// ── Load kategori ────────────────────────────────────────
async function loadKategori() {
    const res = await apiFetch('/kategori');
    const sel = document.getElementById('filter-kategori');
    (res?.data || []).forEach(k => {
        sel.innerHTML += `<option value="${k.kategori_id}">${k.nama_kategori}</option>`;
    });
}

// ── Cart logic ────────────────────────────────────────────
function addToCart(produk) {
    // Parse jika string
    if (typeof produk === 'string') produk = JSON.parse(produk.replace(/'/g,'"'));
    const exist = cart.find(i => i.produk_id === produk.produk_id);
    const stok = produk.stok?.jumlah_stok ?? 99;
    if (exist) {
        if (exist.jumlah >= stok) { showToast('Stok tidak mencukupi!', 'error'); return; }
        exist.jumlah++;
    } else {
        cart.push({ produk_id: produk.produk_id, nama: produk.nama_produk, harga: parseFloat(produk.harga_jual), diskon_persen: parseFloat(produk.diskon_persen || 0), jumlah: 1, stok });
    }
    renderCart();
}

function removeFromCart(produkId) {
    cart = cart.filter(i => i.produk_id !== produkId);
    renderCart();
}

function changeQty(produkId, delta) {
    const item = cart.find(i => i.produk_id === produkId);
    if (!item) return;
    item.jumlah += delta;
    if (item.jumlah <= 0) removeFromCart(produkId);
    else renderCart();
}

function clearCart() {
    if (!cart.length) return;
    if (!confirm('Kosongkan keranjang?')) return;
    cart = []; currentTrxId = null; renderCart();
}

function renderCart() {
    const el = document.getElementById('cart-items');
    const empty = document.getElementById('cart-empty');
    const checkout = document.getElementById('btn-checkout');
    const clearBtn = document.getElementById('btn-clear');

    if (!cart.length) {
        empty.style.display = 'flex'; el.innerHTML = ''; el.appendChild(empty);
        checkout.disabled = true; checkout.style.opacity = '.4'; checkout.style.pointerEvents = 'none';
        clearBtn.style.display = 'none';
        document.getElementById('cart-count-label').textContent = '0 item';
        updateTotals(0, 0); return;
    }

    empty.style.display = 'none';
    clearBtn.style.display = 'flex';
    checkout.disabled = false; checkout.style.opacity = '1'; checkout.style.pointerEvents = '';

    let subtotal = 0, totalDiskon = 0;
    el.innerHTML = cart.map(item => {
        const diskon = (item.diskon_persen / 100) * item.harga * item.jumlah;
        const sub = (item.harga * item.jumlah) - diskon;
        subtotal += item.harga * item.jumlah; totalDiskon += diskon;
        return `
        <div class="cart-item">
            <div style="flex:1;min-width:0">
                <p style="font-size:13px;font-weight:500;color:#e6edf3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.nama}</p>
                <p style="font-size:11.5px;color:#fbbf24;margin-top:2px">${rupiah(item.harga)}/pcs ${item.diskon_persen > 0 ? `<span style="color:#34d399">(-${item.diskon_persen}%)</span>` : ''}</p>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <button class="qty-btn" onclick="changeQty(${item.produk_id}, -1)">−</button>
                <span style="font-size:13px;font-weight:600;color:#fff;min-width:20px;text-align:center">${item.jumlah}</span>
                <button class="qty-btn" onclick="changeQty(${item.produk_id}, 1)">+</button>
            </div>
            <div style="text-align:right;min-width:70px">
                <p style="font-size:13px;font-weight:600;color:#fff">${rupiah(sub)}</p>
                <button onclick="removeFromCart(${item.produk_id})" style="font-size:11px;color:#fb7185;background:none;border:none;cursor:pointer;margin-top:2px">hapus</button>
            </div>
        </div>`;
    }).join('');
    el.appendChild(document.createElement('div')); // spacer

    document.getElementById('cart-count-label').textContent = cart.reduce((s,i) => s+i.jumlah, 0) + ' item';
    updateTotals(subtotal, totalDiskon);
}

function updateTotals(subtotal, diskon) {
    grandTotalVal = subtotal - diskon;
    document.getElementById('subtotal').textContent    = rupiah(subtotal);
    document.getElementById('total-diskon').textContent = `- ${rupiah(diskon)}`;
    document.getElementById('grand-total').textContent = rupiah(grandTotalVal);
}

// ── Metode bayar ─────────────────────────────────────────
function selectMetode(btn) {
    document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedMetode = btn.dataset.metode;
}

// ── Checkout ─────────────────────────────────────────────
function openCheckout() {
    if (!cart.length) return;
    let sub = 0, dis = 0;
    cart.forEach(i => { sub += i.harga*i.jumlah; dis += (i.diskon_persen/100)*i.harga*i.jumlah; });
    const total = sub - dis;
    document.getElementById('m-subtotal').textContent = rupiah(sub);
    document.getElementById('m-diskon').textContent   = `- ${rupiah(dis)}`;
    document.getElementById('m-total').textContent    = rupiah(total);
    grandTotalVal = total;

    // Quick amounts
    const qAmounts = [Math.ceil(total/1000)*1000, Math.ceil(total/5000)*5000, Math.ceil(total/10000)*10000, Math.ceil(total/50000)*50000];
    document.getElementById('quick-amounts').innerHTML = [...new Set(qAmounts)].map(a =>
        `<button onclick="document.getElementById('jumlah-bayar').value=${a};hitungKembalian()" style="padding:5px 10px;border-radius:6px;border:1px solid #30363d;background:#161b22;color:#c9d1d9;font-size:11.5px;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif" onmouseover="this.style.borderColor='#f59e0b';this.style.color='#fbbf24'" onmouseout="this.style.borderColor='#30363d';this.style.color='#c9d1d9'">${rupiah(a)}</button>`
    ).join('');

    document.getElementById('cash-section').style.display = selectedMetode === 'cash' ? '' : 'none';
    document.getElementById('jumlah-bayar').value = '';
    document.getElementById('kembalian').textContent = 'Rp 0';
    document.getElementById('modal-checkout').style.display = 'flex';
    if (selectedMetode === 'cash') setTimeout(() => document.getElementById('jumlah-bayar').focus(), 100);
}

function closeCheckout() { document.getElementById('modal-checkout').style.display = 'none'; }

function hitungKembalian() {
    const bayar = parseFloat(document.getElementById('jumlah-bayar').value) || 0;
    const kembalian = bayar - grandTotalVal;
    const el = document.getElementById('kembalian');
    el.textContent = rupiah(Math.max(0, kembalian));
    el.style.color = kembalian < 0 ? '#fb7185' : '#34d399';
}

async function prosesCheckout() {
    const jumlahBayar = selectedMetode === 'cash' ? parseFloat(document.getElementById('jumlah-bayar').value) || 0 : grandTotalVal;
    if (selectedMetode === 'cash' && jumlahBayar < grandTotalVal) {
        showToast('Uang yang diterima kurang!', 'error'); return;
    }

    const btn = document.getElementById('btn-bayar');
    btn.disabled = true; btn.innerHTML = `<span style="width:15px;height:15px;border:2px solid rgba(8,11,16,.3);border-top-color:#080b10;border-radius:50%;animation:spin .7s linear infinite;display:inline-block"></span> Memproses...`;

    try {
        // 1. Buat transaksi
        const resBuat = await apiFetch('/transaksi', {
            method: 'POST',
            body: JSON.stringify({
                items: cart.map(i => ({ produk_id: i.produk_id, jumlah: i.jumlah })),
                metode_pembayaran: selectedMetode,
            }),
        });
        if (!resBuat?.success) throw new Error(resBuat?.message || 'Gagal membuat transaksi');
        const trxId = resBuat.data.transaksi_id;

        // 2. Selesaikan
        const resSelesai = await apiFetch(`/transaksi/${trxId}/selesaikan`, {
            method: 'POST',
            body: JSON.stringify({ jumlah_bayar: jumlahBayar, metode_pembayaran: selectedMetode }),
        });
        if (!resSelesai?.success) throw new Error(resSelesai?.message || 'Gagal menyelesaikan transaksi');

        currentTrxId = trxId;
        closeCheckout();
        showStruk(resSelesai.data, jumlahBayar);
        cart = []; renderCart();
        showToast('Transaksi berhasil!', 'success');
    } catch(err) {
        showToast(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg> Bayar Sekarang`;
    }
}

// ── Struk ─────────────────────────────────────────────────
function showStruk(trx, jumlahBayar) {
    document.getElementById('struk-nomor').textContent = trx.nomor_transaksi;
    const kembalian = jumlahBayar - parseFloat(trx.total_pembayaran);
    document.getElementById('struk-detail').innerHTML = `
        <div style="border:1px solid #161b22;border-radius:8px;padding:14px;margin-bottom:12px">
            ${(trx.details || []).map(d => `
            <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                <span style="color:#8b949e;font-size:12px">${d.produk?.nama_produk} x${d.jumlah}</span>
                <span style="color:#c9d1d9;font-size:12px">${rupiah(d.subtotal)}</span>
            </div>`).join('')}
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px"><span style="color:#8b949e">Total</span><span style="color:#fff;font-weight:600">${rupiah(trx.total_pembayaran)}</span></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px"><span style="color:#8b949e">Dibayar</span><span style="color:#fff">${rupiah(jumlahBayar)}</span></div>
        ${kembalian > 0 ? `<div style="display:flex;justify-content:space-between;font-size:13px;background:rgba(52,211,153,.06);border:1px solid rgba(52,211,153,.15);border-radius:7px;padding:8px 12px"><span style="color:#8b949e">Kembalian</span><span style="color:#34d399;font-weight:700">${rupiah(kembalian)}</span></div>` : ''}
    `;
    document.getElementById('modal-struk').style.display = 'flex';
}
function closeStruk() { document.getElementById('modal-struk').style.display = 'none'; }
function printStruk() { window.open(`/transaksi/${currentTrxId}/struk`, '_blank'); }

// ── Search & filter ───────────────────────────────────────
let searchTimeout;
document.getElementById('search-produk').addEventListener('input', e => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadProduk(e.target.value, document.getElementById('filter-kategori').value), 350);
});
document.getElementById('filter-kategori').addEventListener('change', e => {
    loadProduk(document.getElementById('search-produk').value, e.target.value);
});

// ── Init ──────────────────────────────────────────────────
loadKategori(); loadProduk();
</script>
@endpush
