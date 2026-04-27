@extends('layouts.app')
@section('title', 'Transaksi')
@section('page-title', 'Transaksi')
@section('page-subtitle', 'Riwayat dan manajemen transaksi')

@section('content')
<div style="display:flex;flex-direction:column;gap:16px">

  <div class="card card-body" style="padding:14px 18px">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:6px">
        <input id="f-dari" type="date" class="input" style="width:155px">
        <span style="color:var(--text-4);font-size:13px">-</span>
        <input id="f-sampai" type="date" class="input" style="width:155px">
      </div>

      <select id="f-status" class="input" style="width:140px">
        <option value="">Semua Status</option>
        <option value="selesai">Selesai</option>
        <option value="pending">Pending</option>
        <option value="batal">Batal</option>
      </select>

      <button onclick="loadTrx(1)" class="btn btn-primary btn-sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Filter
      </button>
      <button onclick="resetFilter()" class="btn btn-ghost btn-sm">Reset</button>

      <div style="margin-left:auto;display:flex;gap:8px">
        <button onclick="exportPdfTrx()" class="btn btn-sm" style="background:var(--red-light);color:var(--red);border:1px solid rgba(239,68,68,.2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
          PDF
        </button>
        <button onclick="exportExcelTrx()" class="btn btn-sm" style="background:var(--green-light);color:#15803d;border:1px solid rgba(34,197,94,.2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Excel
        </button>
      </div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px" id="summary-cards">
    @foreach(['-','-','-','-'] as $_)
    <div class="stat-card teal" style="padding:14px 18px">
      <div class="skel" style="height:10px;width:55%;margin-bottom:8px"></div>
      <div class="skel" style="height:22px;width:70%"></div>
    </div>
    @endforeach
  </div>

  <div class="card" style="overflow:hidden">
    <div class="card-header">
      <div class="card-title">Daftar Transaksi</div>
      <button onclick="loadTrx(curPage)" class="btn btn-ghost btn-sm">Refresh</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>No. Transaksi</th>
            <th>Tanggal & Waktu</th>
            <th>Kasir</th>
            <th>Total</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="trx-tbody">
          <tr><td colspan="7" style="text-align:center;padding:48px"><div class="spinner" style="margin:0 auto"></div></td></tr>
        </tbody>
      </table>
    </div>

    <div style="padding:12px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span id="trx-pager-info" style="font-size:12px;color:var(--text-3)"></span>
      <div id="trx-pager" class="pager"></div>
    </div>
  </div>
</div>

<div id="modal-detail" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:620px">
    <div class="modal-header">
      <div>
        <div class="modal-title" id="detail-nomor">Detail Transaksi</div>
        <div style="font-size:12px;color:var(--text-3)" id="detail-tanggal"></div>
      </div>
      <button class="modal-close" onclick="closeModal('modal-detail')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div id="detail-body" style="padding:20px 24px;max-height:65vh;overflow-y:auto"></div>
    <div class="modal-footer" id="detail-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-detail')">Tutup</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let curPage = 1;
let transaksiProdukOptions = [];
let currentDetailTransaksi = null;

const now = new Date();
const fmtDate = value => value.toISOString().split('T')[0];
const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

document.getElementById('f-dari').value = fmtDate(monthStart);
document.getElementById('f-sampai').value = fmtDate(now);

function resetFilter() {
  document.getElementById('f-dari').value = fmtDate(monthStart);
  document.getElementById('f-sampai').value = fmtDate(now);
  document.getElementById('f-status').value = '';
  loadTrx(1);
}

function getStatusBadge(status) {
  return ({
    selesai: `<span class="badge badge-green">Selesai</span>`,
    pending: `<span class="badge badge-amber">Pending</span>`,
    batal: `<span class="badge badge-red">Batal</span>`,
  }[status] || `<span class="badge badge-gray">${status}</span>`);
}

function getMetodeBadge(metode) {
  return ({
    cash: `<span class="badge badge-blue">Cash</span>`,
    cashless: `<span class="badge badge-blue">Non-Tunai</span>`,
    qris: `<span class="badge badge-blue">QRIS</span>`,
    transfer: `<span class="badge badge-blue">Transfer</span>`,
  }[metode] || `<span class="badge badge-gray">${metode || '-'}</span>`);
}

function canEditTransaksiItems(status) {
  return ['pending', 'selesai'].includes(status);
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

async function ensureTransaksiProdukOptions() {
  if (transaksiProdukOptions.length) return;

  const res = await api('/produk?per_page=200&aktif_saja=1');
  transaksiProdukOptions = (res?.data || []).map(produk => ({
    produk_id: produk.produk_id,
    nama_produk: produk.nama_produk,
    harga_jual: produk.harga_jual,
    stok: produk.stok?.jumlah_stok ?? 0,
  }));
}

function renderProdukOptionItems() {
  if (!transaksiProdukOptions.length) {
    return `<option value="">Produk aktif tidak tersedia</option>`;
  }

  return [
    `<option value="">Pilih produk...</option>`,
    ...transaksiProdukOptions.map(produk => `
      <option value="${produk.produk_id}">
        ${escapeHtml(produk.nama_produk)} - ${rupiah(produk.harga_jual)} (stok: ${produk.stok})
      </option>
    `),
  ].join('');
}

async function loadTrx(page = 1) {
  curPage = page;

  const dari = document.getElementById('f-dari').value;
  const sampai = document.getElementById('f-sampai').value;
  const status = document.getElementById('f-status').value;
  const params = new URLSearchParams({ per_page: 15, page });

  if (dari) params.set('dari', dari);
  if (sampai) params.set('sampai', sampai);
  if (status) params.set('status', status);

  const tbody = document.getElementById('trx-tbody');
  tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:48px"><div class="spinner" style="margin:0 auto"></div></td></tr>`;

  const res = await api('/transaksi?' + params.toString());
  const items = res?.data || [];
  const pag = res?.pagination || {};

  updateSummary(items);

  if (!items.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:48px"><div class="empty-state" style="padding:0"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;opacity:.3"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg><p style="font-size:13px;color:var(--text-4);margin-top:8px">Tidak ada transaksi</p></div></td></tr>`;
    document.getElementById('trx-pager-info').textContent = '';
    document.getElementById('trx-pager').innerHTML = '';
    return;
  }

  tbody.innerHTML = items.map(t => `<tr>
    <td><span style="font-family:'Geist Mono',monospace;font-size:12.5px;font-weight:600;color:var(--teal)">${t.nomor_transaksi}</span></td>
    <td style="font-size:12.5px;color:var(--text-3)">${tanggal(t.tanggal_transaksi, true)}</td>
    <td style="font-size:13px;color:var(--text-1)">${t.user?.nama_lengkap || '-'}</td>
    <td><span style="font-family:'Geist Mono',monospace;font-weight:600;color:var(--text-1)">${rupiah(t.total_pembayaran)}</span></td>
    <td>${getMetodeBadge(t.metode_pembayaran)}</td>
    <td>${getStatusBadge(t.status)}</td>
    <td>
      <div style="display:flex;gap:5px">
        <button onclick="lihatDetail(${t.transaksi_id})" class="btn btn-ghost btn-sm">Detail</button>
        ${t.status === 'pending' ? `<button onclick="batalkanTrx(${t.transaksi_id}, '${t.nomor_transaksi}')" class="btn btn-danger btn-sm">Batal</button>` : ''}
      </div>
    </td>
  </tr>`).join('');

  document.getElementById('trx-pager-info').textContent = pag.from && pag.to ? `${pag.from}-${pag.to} dari ${pag.total}` : '';

  const lastPage = pag.last_page || 1;
  let pages = [];

  if (lastPage <= 5) {
    for (let i = 1; i <= lastPage; i++) pages.push(i);
  } else if (curPage <= 3) {
    pages = [1, 2, 3, 4, '...', lastPage];
  } else if (curPage >= lastPage - 2) {
    pages = [1, '...', lastPage - 3, lastPage - 2, lastPage - 1, lastPage];
  } else {
    pages = [1, '...', curPage - 1, curPage, curPage + 1, '...', lastPage];
  }

  let pagerHtml = '';
  if (curPage > 1) pagerHtml += `<button class="page-btn" onclick="loadTrx(${curPage - 1})">‹</button>`;

  pages.forEach(pageNo => {
    if (pageNo === '...') {
      pagerHtml += `<span style="padding:0 4px;color:var(--text-4);display:flex;align-items:end;font-weight:700">...</span>`;
      return;
    }

    pagerHtml += `<button class="page-btn${pageNo === curPage ? ' current' : ''}" onclick="loadTrx(${pageNo})">${pageNo}</button>`;
  });

  if (curPage < lastPage) pagerHtml += `<button class="page-btn" onclick="loadTrx(${curPage + 1})">›</button>`;

  document.getElementById('trx-pager').innerHTML = pagerHtml;

  window.trxDari = dari;
  window.trxSampai = sampai;
}

function updateSummary(items) {
  const selesai = items.filter(item => item.status === 'selesai');
  const total = selesai.reduce((sum, item) => sum + parseFloat(item.total_pembayaran), 0);
  const diskon = selesai.reduce((sum, item) => sum + parseFloat(item.total_diskon), 0);
  const rata = selesai.length ? total / selesai.length : 0;

  const cards = [
    { label: 'Transaksi Selesai', value: selesai.length.toLocaleString('id-ID'), cls: 'teal' },
    { label: 'Total Pendapatan', value: rupiah(total), cls: 'green' },
    { label: 'Total Diskon', value: rupiah(diskon), cls: 'amber' },
    { label: 'Rata-rata/Trx', value: rupiah(rata), cls: 'blue' },
  ];

  document.getElementById('summary-cards').innerHTML = cards.map(card => `
    <div class="stat-card ${card.cls}" style="padding:14px 18px">
      <div class="stat-label">${card.label}</div>
      <div class="stat-value" style="font-size:18px">${card.value}</div>
    </div>
  `).join('');
}

async function lihatDetail(id) {
  const res = await api(`/transaksi/${id}`);
  if (!res?.success) {
    toast(res?.message || 'Gagal memuat detail transaksi.', 'error');
    return;
  }

  await ensureTransaksiProdukOptions();
  renderDetail(res.data);
}

function renderDetail(transaksi) {
  currentDetailTransaksi = transaksi;
  document.getElementById('detail-nomor').textContent = transaksi.nomor_transaksi;
  document.getElementById('detail-tanggal').textContent = tanggal(transaksi.tanggal_transaksi, true);

  const canEditItems = canEditTransaksiItems(transaksi.status);
  const jumlahBayar = parseFloat(transaksi.jumlah_bayar || 0);
  const totalPembayaran = parseFloat(transaksi.total_pembayaran || 0);
  const sisaPembayaran = Math.max(0, totalPembayaran - jumlahBayar);
  const modePelunasan = (transaksi.metode_pembayaran || 'cash').toUpperCase();

  document.getElementById('detail-body').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px">
      ${[
        ['Kasir', transaksi.user?.nama_lengkap || '-'],
        ['Metode', transaksi.metode_pembayaran?.toUpperCase() || '-'],
        ['Status', ''],
        ['Device', transaksi.device_id || '-'],
      ].map(([label, value], index) => `
      <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:9px;padding:10px 14px">
        <p style="font-size:11px;color:var(--text-4);margin-bottom:3px">${label}</p>
        ${index === 2 ? getStatusBadge(transaksi.status) : `<p style="font-size:13.5px;font-weight:600;color:var(--text-1)">${value}</p>`}
      </div>`).join('')}
    </div>

    <p style="font-size:11.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Item Transaksi</p>
    <div style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px">
      ${(transaksi.details || []).map(detail => `
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:12px 14px;border-bottom:1px solid var(--border)">
        <div style="flex:1;min-width:0">
          <p style="font-size:13px;font-weight:600;color:var(--text-1)">${detail.produk?.nama_produk || '-'}</p>
          <p style="font-size:12px;color:var(--text-3)">${rupiah(detail.harga_satuan)} x ${detail.jumlah}</p>
          ${canEditItems ? `
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:10px">
            <div style="display:flex;align-items:center;gap:6px">
              <button onclick="stepQty(${detail.detail_id}, -1)" class="btn btn-ghost btn-sm" style="min-width:32px;justify-content:center">-</button>
              <input id="detail-qty-${detail.detail_id}" type="number" min="1" value="${detail.jumlah}" class="input" style="width:84px;text-align:center">
              <button onclick="stepQty(${detail.detail_id}, 1)" class="btn btn-ghost btn-sm" style="min-width:32px;justify-content:center">+</button>
            </div>
            <button id="btn-save-${detail.detail_id}" onclick="updateQtyItem(${transaksi.transaksi_id}, ${detail.detail_id})" class="btn btn-primary btn-sm">Simpan Qty</button>
            <button onclick="hapusItemTrx(${transaksi.transaksi_id}, ${detail.detail_id})" class="btn btn-danger btn-sm">Hapus Item</button>
          </div>` : ''}
        </div>
        <div style="text-align:right">
          <p style="font-weight:600;color:var(--text-1)">${rupiah(detail.subtotal)}</p>
          ${parseFloat(detail.diskon_item) > 0 ? `<p style="font-size:11px;color:var(--green)">-${rupiah(detail.diskon_item)}</p>` : ''}
        </div>
      </div>`).join('') || '<p style="text-align:center;padding:16px;color:var(--text-4);font-size:13px">Tidak ada detail</p>'}
    </div>

    ${canEditItems ? `
    <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:14px 18px;margin-bottom:16px">
      <p style="font-size:11.5px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Tambah Item</p>
      <div style="display:grid;grid-template-columns:minmax(0,1fr) 100px auto;gap:8px;align-items:end">
        <div>
          <label style="display:block;font-size:11px;color:var(--text-4);margin-bottom:6px">Produk</label>
          <select id="detail-add-produk" class="input">
            ${renderProdukOptionItems()}
          </select>
        </div>
        <div>
          <label style="display:block;font-size:11px;color:var(--text-4);margin-bottom:6px">Jumlah</label>
          <input id="detail-add-jumlah" type="number" min="1" value="1" class="input">
        </div>
        <button id="btn-add-item" onclick="tambahItemTrx(${transaksi.transaksi_id})" class="btn btn-primary btn-sm" style="height:40px">Tambah Item</button>
      </div>
      <p style="font-size:12px;color:var(--text-3);margin-top:10px">
        Edit tetap diizinkan walau transaksi sudah selesai. Jika total baru melebihi pembayaran lama, transaksi otomatis kembali ke status pending untuk pelunasan tambahan.
      </p>
    </div>` : ''}

    <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:14px 18px">
      ${[
        ['Subtotal', rupiah(transaksi.total_harga)],
        ['Diskon', '- ' + rupiah(transaksi.total_diskon)],
        ['Pajak', rupiah(transaksi.total_pajak)],
      ].map(([label, value]) => `
      <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px"><span style="color:var(--text-3)">${label}</span><span style="color:var(--text-2)">${value}</span></div>`).join('')}
      ${jumlahBayar > 0 ? `
      <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px"><span style="color:var(--text-3)">Sudah Dibayar</span><span style="color:var(--text-2)">${rupiah(jumlahBayar)}</span></div>` : ''}
      ${sisaPembayaran > 0 ? `
      <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px"><span style="color:var(--text-3)">Kurang Bayar</span><span style="color:var(--red);font-weight:600">${rupiah(sisaPembayaran)}</span></div>` : ''}
      <div style="height:1px;background:var(--border);margin:10px 0"></div>
      <div style="display:flex;justify-content:space-between"><span style="font-size:15px;font-weight:700">Total</span><span style="font-family:'Geist Mono',monospace;font-size:18px;font-weight:700;color:var(--teal)">${rupiah(transaksi.total_pembayaran)}</span></div>
      ${parseFloat(transaksi.kembalian) > 0 ? `<div style="display:flex;justify-content:space-between;margin-top:8px;font-size:13px"><span style="color:var(--text-3)">Kembalian</span><span style="color:var(--green);font-weight:600">${rupiah(transaksi.kembalian)}</span></div>` : ''}
      ${transaksi.status === 'pending' && jumlahBayar > 0 ? `<div style="margin-top:10px;padding:10px 12px;border-radius:8px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.18);font-size:12px;color:var(--text-2)">Transaksi ini sudah menerima pembayaran sebagian via ${modePelunasan}. Selesaikan lagi setelah perubahan item final.</div>` : ''}
    </div>
  `;

  const footer = document.getElementById('detail-footer');
  footer.innerHTML = `<button class="btn btn-secondary" onclick="closeModal('modal-detail')">Tutup</button>`;

  if (transaksi.status === 'selesai') {
    footer.innerHTML += `<a href="/kasir/struk/${transaksi.transaksi_id}" target="_blank" class="btn btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Cetak Struk</a>`;
  }

  if (transaksi.status === 'pending') {
    footer.innerHTML += `<button id="btn-complete-trx" onclick="selesaikanTrx(${transaksi.transaksi_id})" class="btn btn-primary">${jumlahBayar > 0 ? 'Selesaikan Ulang' : 'Selesaikan'}</button>`;
    footer.innerHTML += `<button onclick="batalkanTrx(${transaksi.transaksi_id}, '${transaksi.nomor_transaksi}')" class="btn btn-danger">Batalkan</button>`;
  }

  openModal('modal-detail');
}

function stepQty(detailId, delta) {
  const input = document.getElementById(`detail-qty-${detailId}`);
  if (!input) return;

  const nextValue = Math.max(1, (parseInt(input.value || '1', 10) || 1) + delta);
  input.value = nextValue;
}

async function updateQtyItem(transaksiId, detailId) {
  const input = document.getElementById(`detail-qty-${detailId}`);
  const button = document.getElementById(`btn-save-${detailId}`);
  const jumlah = parseInt(input?.value || '0', 10);

  if (!jumlah || jumlah < 1) {
    toast('Jumlah minimal 1.', 'error');
    return;
  }

  setLoading(button, true);

  try {
    const res = await api(`/transaksi/${transaksiId}/item/${detailId}`, {
      method: 'PATCH',
      body: JSON.stringify({ jumlah }),
    });

    if (!res?.success) {
      throw new Error(res?.message || 'Gagal memperbarui jumlah item.');
    }

    renderDetail(res.data);
    loadTrx(curPage);
    loadNotif();
    toast('Kuantitas item berhasil diperbarui.', 'success');
  } catch (error) {
    toast(error.message, 'error');
  } finally {
    setLoading(button, false);
  }
}

async function tambahItemTrx(transaksiId) {
  const produkId = parseInt(document.getElementById('detail-add-produk')?.value || '0', 10);
  const jumlah = parseInt(document.getElementById('detail-add-jumlah')?.value || '0', 10);
  const button = document.getElementById('btn-add-item');

  if (!produkId) {
    toast('Pilih produk yang ingin ditambahkan.', 'error');
    return;
  }

  if (!jumlah || jumlah < 1) {
    toast('Jumlah minimal 1.', 'error');
    return;
  }

  setLoading(button, true);

  try {
    const res = await api(`/transaksi/${transaksiId}/item`, {
      method: 'POST',
      body: JSON.stringify({ produk_id: produkId, jumlah }),
    });

    if (!res?.success) {
      throw new Error(res?.message || 'Gagal menambahkan item.');
    }

    renderDetail(res.data);
    loadTrx(curPage);
    loadNotif();
    toast('Item berhasil ditambahkan ke transaksi.', 'success');
  } catch (error) {
    toast(error.message, 'error');
  } finally {
    setLoading(button, false);
  }
}

async function hapusItemTrx(transaksiId, detailId) {
  if (!(await confirmDialog('Hapus item ini dari transaksi?'))) return;

  const res = await api(`/transaksi/${transaksiId}/item/${detailId}`, {
    method: 'DELETE',
  });

  if (!res?.success) {
    toast(res?.message || 'Gagal menghapus item.', 'error');
    return;
  }

  if (res.data?.details?.length) {
    renderDetail(res.data);
  } else {
    closeModal('modal-detail');
  }

  loadTrx(curPage);
  loadNotif();
  toast('Item transaksi berhasil diperbarui.', 'success');
}

async function selesaikanTrx(transaksiId) {
  let transaksi = currentDetailTransaksi;

  if (!transaksi || transaksi.transaksi_id !== transaksiId) {
    const detailRes = await api(`/transaksi/${transaksiId}`);
    if (!detailRes?.success) {
      toast(detailRes?.message || 'Gagal memuat transaksi.', 'error');
      return;
    }

    transaksi = detailRes.data;
  }

  const metode = transaksi.metode_pembayaran || 'cash';
  const jumlahBayarSaatIni = parseFloat(transaksi.jumlah_bayar || 0);
  const totalPembayaran = parseFloat(transaksi.total_pembayaran || 0);
  const sisaPembayaran = Math.max(0, totalPembayaran - jumlahBayarSaatIni);

  if (sisaPembayaran <= 0) {
    toast('Transaksi ini sudah lunas.', 'info');
    return;
  }

  let jumlahBayar = sisaPembayaran;

  if (metode === 'cash') {
    const input = window.prompt(
      jumlahBayarSaatIni > 0
        ? `Masukkan tambahan pembayaran tunai. Minimal ${rupiah(sisaPembayaran)}.`
        : `Masukkan pembayaran tunai. Minimal ${rupiah(totalPembayaran)}.`,
      Math.ceil(sisaPembayaran).toString()
    );

    if (input === null) return;

    jumlahBayar = parseFloat(String(input).replace(/[^0-9.,-]/g, '').replace(',', '.')) || 0;

    if (jumlahBayar < sisaPembayaran) {
      toast('Jumlah bayar masih kurang.', 'error');
      return;
    }
  } else {
    const konfirmasi = await confirmDialog(
      `Selesaikan transaksi ini dengan pelunasan <strong>${rupiah(sisaPembayaran)}</strong> via <strong>${escapeHtml(metode.toUpperCase())}</strong>?`
    );

    if (!konfirmasi) return;
  }

  const button = document.getElementById('btn-complete-trx');
  if (button) setLoading(button, true);

  try {
    const res = await api(`/transaksi/${transaksiId}/selesaikan`, {
      method: 'POST',
      body: JSON.stringify({
        jumlah_bayar: jumlahBayar,
        metode_pembayaran: metode,
      }),
    });

    if (!res?.success) {
      throw new Error(res?.message || 'Gagal menyelesaikan transaksi.');
    }

    renderDetail(res.data);
    loadTrx(curPage);
    loadNotif();
    toast('Transaksi berhasil diselesaikan kembali.', 'success');
  } catch (error) {
    toast(error.message, 'error');
  } finally {
    if (button) setLoading(button, false);
  }
}

async function batalkanTrx(id, nomor) {
  if (!(await confirmDialog(`Batalkan transaksi <strong>${nomor}</strong>?`))) return;

  const res = await api(`/transaksi/${id}/batalkan`, {
    method: 'POST',
    body: JSON.stringify({ alasan: 'Dibatalkan manual' }),
  });

  if (res?.success) {
    toast('Transaksi dibatalkan.', 'info');
    closeModal('modal-detail');
    loadTrx(curPage);
    loadNotif();
    return;
  }

  toast(res?.message || 'Gagal.', 'error');
}

async function downloadFileTrx(url, filename, preview = false, previewWindow = null) {
  const token = localStorage.getItem('pos_token');

  try {
    toast(preview ? 'Membuka preview PDF...' : 'Menyiapkan file...', 'info');
    const res = await fetch(url, { headers: { Authorization: 'Bearer ' + token } });

    if (!res.ok) {
      if (previewWindow) previewWindow.close();
      toast('Gagal memuat file atau sesi habis.', 'error');
      return;
    }

    const blob = await res.blob();

    if (preview && previewWindow) {
      const urlBlob = window.URL.createObjectURL(new Blob([blob], { type: 'application/pdf' }));
      previewWindow.location.href = urlBlob;
      setTimeout(() => window.URL.revokeObjectURL(urlBlob), 10000);
      return;
    }

    const urlBlob = window.URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = urlBlob;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.URL.revokeObjectURL(urlBlob);
  } catch (error) {
    if (previewWindow) previewWindow.close();
    toast('Error koneksi', 'error');
  }
}

function exportPdfTrx() {
  const previewWindow = window.open('', '_blank');
  downloadFileTrx(`/api/laporan/export/pdf?dari=${window.trxDari || ''}&sampai=${window.trxSampai || ''}&tipe=penjualan`, `Laporan_Transaksi_${window.trxDari || ''}_${window.trxSampai || ''}.pdf`, true, previewWindow);
}

function exportExcelTrx() {
  downloadFileTrx(`/api/laporan/export/excel?dari=${window.trxDari || ''}&sampai=${window.trxSampai || ''}`, `Laporan_Transaksi_${window.trxDari || ''}_${window.trxSampai || ''}.xlsx`, false);
}

loadTrx(1);
</script>
@endpush
