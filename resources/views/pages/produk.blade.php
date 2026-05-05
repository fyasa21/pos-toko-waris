@extends('layouts.app')
@section('title', 'Produk')
@section('page-title', 'Produk')
@section('page-subtitle', 'Kelola katalog produk toko')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
  <div style="display:flex;gap:8px;flex:1;max-width:480px">
    <div class="input-icon-wrap" style="flex:1">
      <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input id="s-search" type="text" class="input" placeholder="Cari nama, kode, barcode…">
    </div>
    <select id="s-kat" class="input" style="width:160px">
      <option value="">Semua Kategori</option>
    </select>
  </div>
  @if(session('user.role') === 'pemilik')
  <button onclick="openProdukModal()" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Tambah Produk
  </button>
  @endif
</div>

<div class="card" style="overflow:hidden">
  <div class="card-header">
    <div>
      <div class="card-title">Daftar Produk</div>
      <div class="card-subtitle" id="produk-count">Memuat data…</div>
    </div>
    <button onclick="loadProduk()" class="btn btn-ghost btn-sm">↻ Refresh</button>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th>Harga Beli</th>
          <th>Harga Jual</th>
          <th>Stok</th>
          <th>Status</th>
          @if(session('user.role') === 'pemilik')<th>Aksi</th>@endif
        </tr>
      </thead>
      <tbody id="produk-tbody">
        <tr><td colspan="8" style="text-align:center;padding:48px"><div class="spinner" style="margin:0 auto"></div></td></tr>
      </tbody>
    </table>
  </div>
  <div style="padding:12px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
    <span id="pager-info" style="font-size:12px;color:var(--text-3)"></span>
    <div id="pager" class="pager"></div>
  </div>
</div>

{{-- Modal tambah/edit produk --}}
@if(session('user.role') === 'pemilik')
<div id="modal-produk" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-produk-title">Tambah Produk</div>
      <button class="modal-close" onclick="closeModal('modal-produk')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="f-id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="field"><label class="field-label">Kode Produk *</label><input id="f-kode" class="input" placeholder="MKN-001"></div>
        <div class="field"><label class="field-label">Barcode</label><input id="f-barcode" class="input" placeholder="8996…"></div>
      </div>
      <div class="field"><label class="field-label">Nama Produk *</label><input id="f-nama" class="input" placeholder="Nama produk"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="field">
          <label class="field-label">Kategori</label>
          <select id="f-kat" class="input"><option value="">Tanpa Kategori</option></select>
        </div>
        <div class="field">
          <label class="field-label">Satuan</label>
          <select id="f-satuan" class="input">
            <option>pcs</option><option>kg</option><option>liter</option><option>pack</option><option>lusin</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
        <div class="field"><label class="field-label">Harga Beli *</label><input id="f-hbeli" class="input" type="number" min="0" placeholder="0"></div>
        <div class="field"><label class="field-label">Harga Jual *</label><input id="f-hjual" class="input" type="number" min="0" placeholder="0"></div>
        <div class="field"><label class="field-label">Diskon (%)</label><input id="f-diskon" class="input" type="number" min="0" max="100" placeholder="0"></div>
      </div>
      <div id="stok-new-fields" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="field"><label class="field-label">Stok Awal</label><input id="f-stok" class="input" type="number" min="0" placeholder="0"></div>
        <div class="field"><label class="field-label">Stok Minimal</label><input id="f-stokmin" class="input" type="number" min="0" placeholder="5"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div class="field"><label class="field-label">Tgl Kedaluwarsa</label><input id="f-exp" class="input" type="date"></div>
        <div class="field"><label class="field-label">Lokasi Rak</label><input id="f-rak" class="input" placeholder="Rak-A1"></div>
      </div>
      <div id="form-err" style="display:none;padding:10px 14px;background:var(--red-light);border:1px solid rgba(239,68,68,.2);border-radius:8px;font-size:13px;color:var(--red)"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-produk')">Batal</button>
      <button id="btn-save-produk" class="btn btn-primary" onclick="saveProduk()" style="min-width:130px;justify-content:center">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><polyline points="20 6 9 17 4 12"/></svg>
        Simpan
      </button>
    </div>
  </div>
</div>
@endif

@endsection

@push('scripts')
<script>
let curPage = 1;
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#39;',
}[char]));

async function loadKat() {
  const res = await api('/kategori');
  const opts = (res?.data||[]).map(k=>`<option value="${k.kategori_id}">${k.nama_kategori}</option>`).join('');
  ['s-kat','f-kat'].forEach(id=>{ const el=document.getElementById(id); if(el) el.innerHTML += opts; });
}

async function loadProduk(page=1) {
  curPage = page;
  const search = document.getElementById('s-search').value;
  const kat    = document.getElementById('s-kat').value;
  const params = new URLSearchParams({ per_page:15, page, search, aktif_saja:1 });
  if (kat) params.set('kategori_id', kat);

  const tbody = document.getElementById('produk-tbody');
  tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px"><div class="spinner" style="margin:0 auto"></div></td></tr>`;

  const res   = await api('/produk?' + params);
  const items = res?.data || [];
  const pag   = res?.pagination || {};

  document.getElementById('produk-count').textContent = `${pag.total || items.length} produk`;

  if (!items.length) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:48px"><div class="empty-state" style="padding:0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;opacity:.3"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg><p style="font-size:13px;color:var(--text-4);margin-top:8px">Tidak ada produk</p></div></td></tr>`;
    return;
  }

  const stokBadge = s => {
    const q = s?.jumlah_stok??0, m = s?.stok_minimal??5;
    if (q<=0) return `<span class="badge badge-red">Habis</span>`;
    if (q<=m) return `<span class="badge badge-amber">Sisa ${q}</span>`;
    return `<span class="badge badge-teal">${q}</span>`;
  };

  const from = ((pag.current_page||1)-1)*(pag.per_page||15)+1;
  const isPemilik = {{ session('user.role')==='pemilik' ? 'true' : 'false' }};

  tbody.innerHTML = items.map((p,i) => `<tr>
    <td style="color:var(--text-4);font-size:12px">${from+i}</td>
    <td>
      <p style="font-weight:600;color:var(--text-1)">${escapeHtml(p.nama_produk)}</p>
      <p style="font-size:11.5px;color:var(--text-4);font-family:'Geist Mono',monospace">${escapeHtml(p.kode_produk)}${p.barcode?' · '+escapeHtml(p.barcode):''}</p>
    </td>
    <td><span class="badge badge-gray">${escapeHtml(p.kategori?.nama_kategori||'—')}</span></td>
    <td style="font-family:'Geist Mono',monospace;color:var(--text-2)">${rupiah(p.harga_beli)}</td>
    <td>
      <span style="font-family:'Geist Mono',monospace;font-weight:600;color:var(--teal)">${rupiah(p.harga_jual)}</span>
      ${parseFloat(p.diskon_persen)>0?`<span style="font-size:11px;color:var(--green);display:block">-${p.diskon_persen}%</span>`:''}
    </td>
    <td>${stokBadge(p.stok)}</td>
    <td>${p.is_active?`<span class="badge badge-green">Aktif</span>`:`<span class="badge badge-gray">Nonaktif</span>`}</td>
    ${isPemilik?`<td><div style="display:flex;gap:6px">
      <button onclick="editProduk(${p.produk_id})" class="btn btn-ghost btn-sm">Edit</button>
      <button type="button" data-delete-produk="${p.produk_id}" data-produk-nama="${escapeHtml(p.nama_produk)}" class="btn btn-danger btn-sm">Hapus</button>
    </div></td>`:''}
  </tr>`).join('');

  // Pager
  document.getElementById('pager-info').textContent = pag.from&&pag.to ? `Menampilkan ${pag.from}–${pag.to} dari ${pag.total}` : '';
  const lastPage = pag.last_page || 1;
  
  let pages = [];
  if (lastPage <= 5) {
      for (let i = 1; i <= lastPage; i++) pages.push(i);
  } else {
      if (curPage <= 3) {
          pages = [1, 2, 3, 4, '...', lastPage];
      } else if (curPage >= lastPage - 2) {
          pages = [1, '...', lastPage - 3, lastPage - 2, lastPage - 1, lastPage];
      } else {
          pages = [1, '...', curPage - 1, curPage, curPage + 1, '...', lastPage];
      }
  }

  let pagerHtml = '';
  if (curPage > 1) pagerHtml += `<button class="page-btn" onclick="loadProduk(${curPage - 1})">‹</button>`;
  
  pages.forEach(p => {
    if (p === '...') {
      pagerHtml += `<span style="padding:0 4px;color:var(--text-4);display:flex;align-items:end;font-weight:700">...</span>`;
    } else {
      pagerHtml += `<button class="page-btn${p===curPage?' current':''}" onclick="loadProduk(${p})">${p}</button>`;
    }
  });
  
  if (curPage < lastPage) pagerHtml += `<button class="page-btn" onclick="loadProduk(${curPage + 1})">›</button>`;
  
  document.getElementById('pager').innerHTML = pagerHtml;
}

function openProdukModal(data=null) {
  document.getElementById('modal-produk-title').textContent = data ? 'Edit Produk' : 'Tambah Produk';
  ['f-id','f-kode','f-barcode','f-nama','f-hbeli','f-hjual','f-diskon','f-stok','f-stokmin','f-exp','f-rak'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
  document.getElementById('f-stokmin').value = 5;
  document.getElementById('stok-new-fields').style.display = data ? 'none' : 'grid';
  document.getElementById('form-err').style.display = 'none';
  if (data) {
    document.getElementById('f-id').value      = data.produk_id;
    document.getElementById('f-kode').value    = data.kode_produk||'';
    document.getElementById('f-barcode').value = data.barcode||'';
    document.getElementById('f-nama').value    = data.nama_produk||'';
    document.getElementById('f-kat').value     = data.kategori_id||'';
    document.getElementById('f-satuan').value  = data.satuan||'pcs';
    document.getElementById('f-hbeli').value   = data.harga_beli||'';
    document.getElementById('f-hjual').value   = data.harga_jual||'';
    document.getElementById('f-diskon').value  = data.diskon_persen||0;
    document.getElementById('f-stokmin').value = data.stok?.stok_minimal||5;
    document.getElementById('f-exp').value     = data.stok?.tanggal_kedaluwarsa||'';
    document.getElementById('f-rak').value     = data.stok?.lokasi_rak||'';
  }
  openModal('modal-produk');
}

async function editProduk(id) {
  const res = await api(`/produk/${id}`);
  if (res?.success) openProdukModal(res.data);
}

async function saveProduk() {
  const id     = document.getElementById('f-id').value;
  const isEdit = !!id;
  const errEl  = document.getElementById('form-err');
  errEl.style.display = 'none';

  const body = {
    kode_produk:   document.getElementById('f-kode').value.trim(),
    nama_produk:   document.getElementById('f-nama').value.trim(),
    harga_beli:    parseFloat(document.getElementById('f-hbeli').value)||0,
    harga_jual:    parseFloat(document.getElementById('f-hjual').value)||0,
    diskon_persen: parseFloat(document.getElementById('f-diskon').value)||0,
    kategori_id:   document.getElementById('f-kat').value||null,
    satuan:        document.getElementById('f-satuan').value,
    barcode:       document.getElementById('f-barcode').value.trim()||null,
    stok_minimal:  parseInt(document.getElementById('f-stokmin').value)||5,
    tanggal_kedaluwarsa: document.getElementById('f-exp').value||null,
    lokasi_rak:    document.getElementById('f-rak').value||null,
  };
  if (!isEdit) body.stok_awal = parseInt(document.getElementById('f-stok').value)||0;

  if (!body.kode_produk || !body.nama_produk || !body.harga_jual) {
    errEl.textContent = 'Kode produk, nama, dan harga jual wajib diisi.';
    errEl.style.display = ''; return;
  }

  const btn = document.getElementById('btn-save-produk');
  setLoading(btn, true);
  const res = await api(isEdit?`/produk/${id}`:'/produk', { method:isEdit?'PUT':'POST', body:JSON.stringify(body) });
  setLoading(btn, false);

  if (res?.success) {
    toast(`Produk berhasil ${isEdit?'diperbarui':'ditambahkan'}!`, 'success');
    closeModal('modal-produk'); loadProduk(curPage);
  } else {
    const msg = res?.errors ? Object.values(res.errors).flat().join(' ') : (res?.message||'Gagal.');
    errEl.textContent = msg; errEl.style.display = '';
  }
}

async function hapusProduk(id, nama, btn=null) {
  if (!(await confirmDialog(`Nonaktifkan produk "<strong>${escapeHtml(nama)}</strong>"?`))) return;
  if (btn) setLoading(btn, true);
  const res = await api(`/produk/${id}`, { method:'DELETE' });
  if (btn) setLoading(btn, false);
  if (res?.success) { toast('Produk dinonaktifkan.', 'info'); loadProduk(curPage); }
  else toast(res?.message||'Gagal.', 'error');
}

const dSearch = debounce(()=>loadProduk(1), 320);
document.getElementById('s-search').addEventListener('input', dSearch);
document.getElementById('s-kat').addEventListener('change', ()=>loadProduk(1));
document.getElementById('produk-tbody').addEventListener('click', e => {
  const btn = e.target.closest('[data-delete-produk]');
  if (!btn) return;
  hapusProduk(parseInt(btn.dataset.deleteProduk, 10), btn.dataset.produkNama || 'produk ini', btn);
});

loadKat(); loadProduk();
</script>
@endpush
