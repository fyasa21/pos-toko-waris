@extends('layouts.app')
@section('title', 'Produk')
@section('page-title', 'Manajemen Produk')
@section('page-subtitle', 'Kelola daftar produk dan stok')

@section('content')
<div style="display:flex;flex-direction:column;gap:20px">

    {{-- Header action --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div style="display:flex;gap:10px;flex:1;max-width:500px">
            <div style="position:relative;flex:1">
                <svg style="position:absolute;left:13px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#484f58;pointer-events:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input id="search-input" type="text" placeholder="Cari produk, kode, barcode..." class="pos-input" style="padding-left:40px">
            </div>
            <select id="filter-kat" class="pos-input" style="width:150px">
                <option value="">Semua Kategori</option>
            </select>
        </div>
        <button onclick="openModal()" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Produk
        </button>
    </div>

    {{-- Table --}}
    <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid #161b22;display:flex;align-items:center;justify-content:space-between">
            <p style="font-size:13px;color:#8b949e"><span id="total-count">—</span> produk ditemukan</p>
            <div style="display:flex;gap:8px">
                <button onclick="loadProduk()" style="padding:6px 12px;border-radius:7px;background:#161b22;border:1px solid #30363d;color:#8b949e;font-size:12px;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#8b949e'">
                    ↻ Refresh
                </button>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse" class="pos-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th style="width:130px">Aksi</th>
                    </tr>
                </thead>
                <tbody id="produk-tbody">
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:#484f58">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div id="pagination" style="padding:14px 20px;border-top:1px solid #161b22;display:flex;align-items:center;justify-content:space-between">
            <p style="font-size:12px;color:#484f58" id="pagination-info"></p>
            <div id="pagination-buttons" style="display:flex;gap:6px"></div>
        </div>
    </div>
</div>

{{-- ═══ MODAL PRODUK ═══ --}}
<div id="modal-produk" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <div style="padding:20px 24px;border-bottom:1px solid #161b22;display:flex;align-items:center;justify-content:space-between">
            <h3 class="font-display" id="modal-title" style="font-size:16px;font-weight:700;color:#fff">Tambah Produk</h3>
            <button onclick="closeModal()" style="width:30px;height:30px;border-radius:7px;border:1px solid #30363d;background:transparent;color:#8b949e;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s" onmouseover="this.style.background='#161b22'" onmouseout="this.style.background='transparent'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
            <input type="hidden" id="edit-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Kode Produk *</label>
                    <input type="text" id="f-kode" class="pos-input" placeholder="MKN-001">
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Barcode</label>
                    <input type="text" id="f-barcode" class="pos-input" placeholder="8996001234001">
                </div>
            </div>
            <div>
                <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Nama Produk *</label>
                <input type="text" id="f-nama" class="pos-input" placeholder="Nama produk">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Kategori</label>
                    <select id="f-kategori" class="pos-input"><option value="">Tanpa Kategori</option></select>
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Satuan</label>
                    <select id="f-satuan" class="pos-input">
                        <option value="pcs">pcs</option><option value="kg">kg</option><option value="liter">liter</option><option value="lusin">lusin</option><option value="pack">pack</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Harga Beli *</label>
                    <input type="number" id="f-harga-beli" class="pos-input" placeholder="0" min="0">
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Harga Jual *</label>
                    <input type="number" id="f-harga-jual" class="pos-input" placeholder="0" min="0">
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Diskon (%)</label>
                    <input type="number" id="f-diskon" class="pos-input" placeholder="0" min="0" max="100">
                </div>
            </div>
            <div id="stok-fields" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Stok Awal</label>
                    <input type="number" id="f-stok-awal" class="pos-input" placeholder="0" min="0">
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Stok Minimal</label>
                    <input type="number" id="f-stok-min" class="pos-input" placeholder="5" min="0">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Tgl Kedaluwarsa</label>
                    <input type="date" id="f-exp" class="pos-input">
                </div>
                <div>
                    <label style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:7px">Lokasi Rak</label>
                    <input type="text" id="f-rak" class="pos-input" placeholder="Rak-A1">
                </div>
            </div>
            <div id="form-error" style="display:none;background:rgba(251,113,133,.07);border:1px solid rgba(251,113,133,.2);border-radius:8px;padding:10px 14px;font-size:13px;color:#fb7185"></div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #161b22;display:flex;gap:10px">
            <button onclick="closeModal()" class="btn-secondary" style="flex:1;justify-content:center">Batal</button>
            <button onclick="saveProduk()" id="btn-save" class="btn-primary" style="flex:2;justify-content:center">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg>
                Simpan Produk
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = 1; let kategoriMap = {};

// ── Load kategori ────────────────────────────────────────
async function loadKategori() {
    const res = await apiFetch('/kategori');
    const items = res?.data || [];
    const opts = items.map(k => { kategoriMap[k.kategori_id] = k.nama_kategori; return `<option value="${k.kategori_id}">${k.nama_kategori}</option>`; }).join('');
    ['f-kategori','filter-kat'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML += opts;
    });
}

// ── Load produk ──────────────────────────────────────────
async function loadProduk(page = 1) {
    currentPage = page;
    const search = document.getElementById('search-input').value;
    const kat    = document.getElementById('filter-kat').value;
    const params = new URLSearchParams({ per_page: 15, page, search, aktif_saja: 1 });
    if (kat) params.set('kategori_id', kat);

    document.getElementById('produk-tbody').innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px"><div class="spinner" style="margin:0 auto"></div></td></tr>`;
    const res = await apiFetch('/produk?' + params);
    const items = res?.data || [];
    const pag = res?.pagination || {};

    document.getElementById('total-count').textContent = pag.total || items.length;

    if (!items.length) {
        document.getElementById('produk-tbody').innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:#484f58;font-size:13px">Tidak ada produk ditemukan</td></tr>`;
        return;
    }

    const stokBadge = s => {
        const q = s?.jumlah_stok ?? 0;
        const m = s?.stok_minimal ?? 5;
        if (q <= 0) return `<span class="badge badge-red">Habis</span>`;
        if (q <= m) return `<span class="badge badge-amber">Tipis: ${q}</span>`;
        return `<span class="badge badge-green">${q}</span>`;
    };

    const from = (pag.current_page - 1) * pag.per_page + 1;
    document.getElementById('produk-tbody').innerHTML = items.map((p, i) => `
        <tr>
            <td style="color:#484f58;font-size:12px">${from + i}</td>
            <td>
                <div style="display:flex;flex-direction:column;gap:2px">
                    <span style="font-size:13.5px;font-weight:500;color:#e6edf3">${p.nama_produk}</span>
                    <span style="font-size:11px;color:#484f58">${p.kode_produk}${p.barcode ? ' · ' + p.barcode : ''}</span>
                </div>
            </td>
            <td><span class="badge badge-gray">${p.kategori?.nama_kategori || '—'}</span></td>
            <td>
                <span class="font-display" style="font-size:13.5px;font-weight:700;color:#fbbf24">${rupiah(p.harga_jual)}</span>
                ${parseFloat(p.diskon_persen) > 0 ? `<span style="font-size:11px;color:#34d399;display:block">-${p.diskon_persen}%</span>` : ''}
            </td>
            <td>${stokBadge(p.stok)}</td>
            <td>${p.is_active ? `<span class="badge badge-green">Aktif</span>` : `<span class="badge badge-gray">Nonaktif</span>`}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button onclick="editProduk(${p.produk_id})" style="padding:5px 10px;border-radius:6px;border:1px solid #30363d;background:#161b22;color:#8b949e;font-size:12px;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif" onmouseover="this.style.color='#fff';this.style.borderColor='#484f58'" onmouseout="this.style.color='#8b949e';this.style.borderColor='#30363d'">Edit</button>
                    <button onclick="hapusProduk(${p.produk_id}, '${p.nama_produk.replace(/'/g,"\\'")}')" class="btn-danger" style="padding:5px 10px;font-size:12px">Hapus</button>
                </div>
            </td>
        </tr>
    `).join('');

    // Pagination
    document.getElementById('pagination-info').textContent = `Menampilkan ${pag.from || 1}–${pag.to || items.length} dari ${pag.total || items.length}`;
    const btns = [];
    for (let p = 1; p <= (pag.last_page || 1); p++) {
        btns.push(`<button onclick="loadProduk(${p})" style="width:30px;height:30px;border-radius:6px;border:1px solid ${p===currentPage?'rgba(245,158,11,.4)':'#30363d'};background:${p===currentPage?'rgba(245,158,11,.12)':'#161b22'};color:${p===currentPage?'#fbbf24':'#8b949e'};font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">${p}</button>`);
    }
    document.getElementById('pagination-buttons').innerHTML = btns.join('');
}

// ── Modal ─────────────────────────────────────────────────
function openModal(data = null) {
    document.getElementById('modal-title').textContent = data ? 'Edit Produk' : 'Tambah Produk';
    ['edit-id','f-kode','f-barcode','f-nama','f-harga-beli','f-harga-jual','f-diskon','f-stok-awal','f-stok-min','f-exp','f-rak'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('f-stok-min').value = 5;
    document.getElementById('stok-fields').style.display = data ? 'none' : 'grid';
    if (data) {
        document.getElementById('edit-id').value = data.produk_id;
        document.getElementById('f-kode').value  = data.kode_produk || '';
        document.getElementById('f-nama').value  = data.nama_produk || '';
        document.getElementById('f-barcode').value = data.barcode || '';
        document.getElementById('f-harga-beli').value = data.harga_beli || '';
        document.getElementById('f-harga-jual').value = data.harga_jual || '';
        document.getElementById('f-diskon').value = data.diskon_persen || 0;
        document.getElementById('f-kategori').value = data.kategori_id || '';
        document.getElementById('f-satuan').value = data.satuan || 'pcs';
        document.getElementById('f-stok-min').value = data.stok?.stok_minimal || 5;
        document.getElementById('f-exp').value = data.stok?.tanggal_kedaluwarsa || '';
        document.getElementById('f-rak').value = data.stok?.lokasi_rak || '';
    }
    document.getElementById('form-error').style.display = 'none';
    document.getElementById('modal-produk').style.display = 'flex';
}
function closeModal() { document.getElementById('modal-produk').style.display = 'none'; }

async function editProduk(id) {
    const res = await apiFetch(`/produk/${id}`);
    if (res?.success) openModal(res.data);
}

async function saveProduk() {
    const id      = document.getElementById('edit-id').value;
    const isEdit  = !!id;
    const errEl   = document.getElementById('form-error');
    errEl.style.display = 'none';

    const payload = {
        kode_produk:   document.getElementById('f-kode').value.trim(),
        nama_produk:   document.getElementById('f-nama').value.trim(),
        harga_beli:    parseFloat(document.getElementById('f-harga-beli').value) || 0,
        harga_jual:    parseFloat(document.getElementById('f-harga-jual').value) || 0,
        diskon_persen: parseFloat(document.getElementById('f-diskon').value) || 0,
        kategori_id:   document.getElementById('f-kategori').value || null,
        satuan:        document.getElementById('f-satuan').value,
        barcode:       document.getElementById('f-barcode').value.trim() || null,
        stok_minimal:  parseInt(document.getElementById('f-stok-min').value) || 5,
        tanggal_kedaluwarsa: document.getElementById('f-exp').value || null,
        lokasi_rak:    document.getElementById('f-rak').value || null,
    };
    if (!isEdit) payload.stok_awal = parseInt(document.getElementById('f-stok-awal').value) || 0;

    if (!payload.kode_produk || !payload.nama_produk || !payload.harga_jual) {
        errEl.textContent = 'Kode produk, nama, dan harga jual wajib diisi.';
        errEl.style.display = '';
        return;
    }

    const btn = document.getElementById('btn-save');
    btn.disabled = true; btn.innerHTML = `<span class="spinner"></span> Menyimpan...`;

    const res = await apiFetch(isEdit ? `/produk/${id}` : '/produk', {
        method: isEdit ? 'PUT' : 'POST',
        body: JSON.stringify(payload),
    });

    btn.disabled = false;
    btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><polyline points="20 6 9 17 4 12"/></svg> Simpan Produk`;

    if (res?.success) {
        showToast(`Produk berhasil ${isEdit ? 'diperbarui' : 'ditambahkan'}!`, 'success');
        closeModal(); loadProduk(currentPage);
    } else {
        const msgs = res?.errors ? Object.values(res.errors).flat().join(' ') : (res?.message || 'Gagal menyimpan.');
        errEl.textContent = msgs; errEl.style.display = '';
    }
}

async function hapusProduk(id, nama) {
    if (!confirm(`Nonaktifkan produk "${nama}"?`)) return;
    const res = await apiFetch(`/produk/${id}`, { method: 'DELETE' });
    if (res?.success) { showToast('Produk dinonaktifkan.', 'info'); loadProduk(currentPage); }
    else showToast(res?.message || 'Gagal.', 'error');
}

// ── Search ────────────────────────────────────────────────
let st;
document.getElementById('search-input').addEventListener('input', () => { clearTimeout(st); st = setTimeout(() => loadProduk(1), 350); });
document.getElementById('filter-kat').addEventListener('change', () => loadProduk(1));

loadKategori(); loadProduk();
</script>
@endpush
