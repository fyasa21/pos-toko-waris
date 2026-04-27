@extends('layouts.app')
@section('title', 'Transaksi')
@section('page-title', 'Riwayat Transaksi')
@section('page-subtitle', 'Daftar semua transaksi penjualan')

@section('content')
<div style="display:flex;flex-direction:column;gap:20px">

    {{-- Filter bar --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div style="position:relative">
            <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#484f58;pointer-events:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" id="f-dari" class="pos-input" style="padding-left:34px;width:160px">
        </div>
        <span style="color:#484f58;font-size:13px">s/d</span>
        <input type="date" id="f-sampai" class="pos-input" style="width:160px">
        <select id="f-status" class="pos-input" style="width:140px">
            <option value="">Semua Status</option>
            <option value="selesai">Selesai</option>
            <option value="pending">Pending</option>
            <option value="batal">Batal</option>
        </select>
        <button onclick="loadTransaksi(1)" class="btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Filter
        </button>
        <button onclick="resetFilter()" style="padding:9px 14px;border-radius:8px;border:1px solid #30363d;background:transparent;color:#8b949e;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#8b949e'">Reset</button>

        <div style="margin-left:auto;display:flex;gap:8px">
            <a href="/api/laporan/export/excel?dari={{ date('Y-m-01') }}&sampai={{ date('Y-m-d') }}&token=" id="btn-excel" style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;border:1px solid rgba(52,211,153,.25);background:rgba(52,211,153,.06);color:#34d399;font-size:13px;font-weight:500;text-decoration:none;transition:all .2s" onmouseover="this.style.background='rgba(52,211,153,.12)'" onmouseout="this.style.background='rgba(52,211,153,.06)'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                Export Excel
            </a>
        </div>
    </div>

    {{-- Summary strip --}}
    <div id="summary-strip" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
        @foreach([['—','Total Transaksi','#38bdf8'],['—','Total Pendapatan','#fbbf24'],['—','Total Diskon','#34d399'],['—','Rata-rata','#f97316']] as $s)
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:10px;padding:14px 18px">
            <p style="font-size:11px;color:#8b949e;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">{{ $s[1] }}</p>
            <p class="font-display" style="font-size:18px;font-weight:700;color:{{ $s[2] }}">{{ $s[0] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;overflow:hidden">
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse" class="pos-table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal & Waktu</th>
                        <th>Kasir</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="trx-tbody">
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:#484f58">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <div style="padding:14px 20px;border-top:1px solid #161b22;display:flex;align-items:center;justify-content:space-between">
            <p style="font-size:12px;color:#484f58" id="trx-pagination-info"></p>
            <div id="trx-pagination" style="display:flex;gap:6px"></div>
        </div>
    </div>
</div>

{{-- ═══ MODAL DETAIL ═══ --}}
<div id="modal-detail" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:560px">
        <div style="padding:20px 24px;border-bottom:1px solid #161b22;display:flex;justify-content:space-between;align-items:center">
            <h3 class="font-display" style="font-size:16px;font-weight:700;color:#fff" id="detail-nomor">Detail Transaksi</h3>
            <button onclick="document.getElementById('modal-detail').style.display='none'" style="width:30px;height:30px;border-radius:7px;border:1px solid #30363d;background:transparent;color:#8b949e;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s" onmouseover="this.style.background='#161b22'" onmouseout="this.style.background='transparent'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div id="detail-content" style="padding:20px 24px;max-height:65vh;overflow-y:auto"></div>
        <div style="padding:14px 24px;border-top:1px solid #161b22;display:flex;gap:8px" id="detail-actions"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Set default tanggal
const today = new Date().toISOString().split('T')[0];
const monthStart = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
document.getElementById('f-dari').value   = monthStart;
document.getElementById('f-sampai').value = today;

function resetFilter() {
    document.getElementById('f-dari').value   = monthStart;
    document.getElementById('f-sampai').value = today;
    document.getElementById('f-status').value = '';
    loadTransaksi(1);
}

async function loadTransaksi(page = 1) {
    const dari   = document.getElementById('f-dari').value;
    const sampai = document.getElementById('f-sampai').value;
    const status = document.getElementById('f-status').value;
    const params = new URLSearchParams({ per_page: 15, page });
    if (dari)   params.set('dari',   dari);
    if (sampai) params.set('sampai', sampai);
    if (status) params.set('status', status);

    document.getElementById('trx-tbody').innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px"><div class="spinner" style="margin:0 auto"></div></td></tr>`;
    const res = await apiFetch('/transaksi?' + params);
    const items = res?.data || [];
    const pag   = res?.pagination || {};

    if (!items.length) {
        document.getElementById('trx-tbody').innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:#484f58;font-size:13px">Tidak ada transaksi</td></tr>`;
        updateSummary([]); return;
    }

    const statusBadge = s => ({
        selesai: `<span class="badge badge-green">✓ Selesai</span>`,
        pending: `<span class="badge badge-amber">⏳ Pending</span>`,
        batal:   `<span class="badge badge-red">✗ Batal</span>`,
    }[s] || `<span class="badge badge-gray">${s}</span>`);

    const metodeBadge = m => ({
        cash:     `<span class="badge badge-sky">💵 Cash</span>`,
        cashless: `<span class="badge badge-sky">💳 Non-Tunai</span>`,
        qris:     `<span class="badge badge-sky">📱 QRIS</span>`,
    }[m] || `<span class="badge badge-gray">${m}</span>`);

    document.getElementById('trx-tbody').innerHTML = items.map(t => `
        <tr>
            <td><span style="font-size:12.5px;font-weight:600;color:#fbbf24;font-family:'Syne',sans-serif">${t.nomor_transaksi}</span></td>
            <td style="font-size:12px;color:#8b949e">${new Date(t.tanggal_transaksi).toLocaleString('id-ID')}</td>
            <td style="font-size:13px;color:#c9d1d9">${t.user?.nama_lengkap || '—'}</td>
            <td style="text-align:center;color:#c9d1d9">—</td>
            <td><span class="font-display" style="font-size:13.5px;font-weight:700;color:#fff">${rupiah(t.total_pembayaran)}</span></td>
            <td>${metodeBadge(t.metode_pembayaran)}</td>
            <td>${statusBadge(t.status)}</td>
            <td>
                <div style="display:flex;gap:6px">
                    <button onclick="lihatDetail(${t.transaksi_id})" style="padding:5px 10px;border-radius:6px;border:1px solid #30363d;background:#161b22;color:#8b949e;font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .15s" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#8b949e'">Detail</button>
                    ${t.status === 'pending' ? `<button onclick="batalkanTrx(${t.transaksi_id}, '${t.nomor_transaksi}')" class="btn-danger" style="padding:5px 10px;font-size:12px">Batal</button>` : ''}
                </div>
            </td>
        </tr>
    `).join('');

    updateSummary(items);

    document.getElementById('trx-pagination-info').textContent = `Halaman ${pag.current_page || 1} dari ${pag.last_page || 1}`;
    const btns = [];
    for (let p = 1; p <= (pag.last_page || 1); p++) btns.push(`<button onclick="loadTransaksi(${p})" style="width:30px;height:30px;border-radius:6px;border:1px solid ${p===page?'rgba(245,158,11,.4)':'#30363d'};background:${p===page?'rgba(245,158,11,.12)':'#161b22'};color:${p===page?'#fbbf24':'#8b949e'};font-size:12px;cursor:pointer;font-family:'DM Sans',sans-serif">${p}</button>`);
    document.getElementById('trx-pagination').innerHTML = btns.join('');
}

function updateSummary(items) {
    const selesai = items.filter(t => t.status === 'selesai');
    const total   = selesai.reduce((s,t) => s + parseFloat(t.total_pembayaran), 0);
    const diskon  = selesai.reduce((s,t) => s + parseFloat(t.total_diskon), 0);
    const rata    = selesai.length ? total / selesai.length : 0;
    const cards   = document.querySelectorAll('#summary-strip .font-display');
    if (cards[0]) cards[0].textContent = selesai.length.toLocaleString('id-ID');
    if (cards[1]) cards[1].textContent = rupiah(total);
    if (cards[2]) cards[2].textContent = rupiah(diskon);
    if (cards[3]) cards[3].textContent = rupiah(rata);
}

async function lihatDetail(id) {
    const res = await apiFetch(`/transaksi/${id}`);
    if (!res?.success) return;
    const t = res.data;
    document.getElementById('detail-nomor').textContent = t.nomor_transaksi;

    const statusBadge = s => ({
        selesai: `<span class="badge badge-green">✓ Selesai</span>`,
        pending: `<span class="badge badge-amber">⏳ Pending</span>`,
        batal:   `<span class="badge badge-red">✗ Batal</span>`,
    }[s] || s);

    document.getElementById('detail-content').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
            <div style="background:#080b10;border:1px solid #161b22;border-radius:9px;padding:12px 16px">
                <p style="font-size:11px;color:#484f58;margin-bottom:4px">Kasir</p>
                <p style="font-size:13.5px;color:#e6edf3;font-weight:500">${t.user?.nama_lengkap || '—'}</p>
            </div>
            <div style="background:#080b10;border:1px solid #161b22;border-radius:9px;padding:12px 16px">
                <p style="font-size:11px;color:#484f58;margin-bottom:4px">Tanggal</p>
                <p style="font-size:13px;color:#e6edf3">${new Date(t.tanggal_transaksi).toLocaleString('id-ID')}</p>
            </div>
            <div style="background:#080b10;border:1px solid #161b22;border-radius:9px;padding:12px 16px">
                <p style="font-size:11px;color:#484f58;margin-bottom:4px">Metode</p>
                <p style="font-size:13.5px;color:#e6edf3;text-transform:uppercase">${t.metode_pembayaran}</p>
            </div>
            <div style="background:#080b10;border:1px solid #161b22;border-radius:9px;padding:12px 16px">
                <p style="font-size:11px;color:#484f58;margin-bottom:4px">Status</p>
                ${statusBadge(t.status)}
            </div>
        </div>

        <p style="font-size:11.5px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;margin-bottom:10px">Item Transaksi</p>
        <div style="border:1px solid #161b22;border-radius:10px;overflow:hidden;margin-bottom:16px">
            ${(t.details || []).map(d => `
            <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 16px;border-bottom:1px solid rgba(22,27,34,0.6)">
                <div>
                    <p style="font-size:13px;color:#e6edf3;font-weight:500">${d.produk?.nama_produk || '—'}</p>
                    <p style="font-size:11.5px;color:#8b949e">${rupiah(d.harga_satuan)} × ${d.jumlah}</p>
                </div>
                <div style="text-align:right">
                    <p style="font-size:13.5px;font-weight:600;color:#fbbf24">${rupiah(d.subtotal)}</p>
                    ${parseFloat(d.diskon_item) > 0 ? `<p style="font-size:11px;color:#34d399">-${rupiah(d.diskon_item)}</p>` : ''}
                </div>
            </div>`).join('') || '<p style="padding:16px;text-align:center;color:#484f58;font-size:13px">Tidak ada detail</p>'}
        </div>

        <div style="display:flex;flex-direction:column;gap:7px;background:#080b10;border:1px solid #161b22;border-radius:10px;padding:14px 18px">
            <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:#8b949e">Subtotal</span><span style="color:#c9d1d9">${rupiah(t.total_harga)}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:#8b949e">Diskon</span><span style="color:#34d399">- ${rupiah(t.total_diskon)}</span></div>
            <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:#8b949e">Pajak</span><span style="color:#c9d1d9">${rupiah(t.total_pajak)}</span></div>
            <div style="height:1px;background:#21262d;margin:4px 0"></div>
            <div style="display:flex;justify-content:space-between"><span class="font-display" style="font-size:15px;font-weight:700;color:#fff">Total</span><span class="font-display" style="font-size:17px;font-weight:700;color:#fbbf24">${rupiah(t.total_pembayaran)}</span></div>
            ${parseFloat(t.kembalian) > 0 ? `<div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:#8b949e">Kembalian</span><span style="color:#34d399;font-weight:600">${rupiah(t.kembalian)}</span></div>` : ''}
        </div>
    `;

    const actionsEl = document.getElementById('detail-actions');
    actionsEl.innerHTML = `<button onclick="document.getElementById('modal-detail').style.display='none'" class="btn-secondary" style="flex:1;justify-content:center">Tutup</button>`;
    if (t.status === 'selesai') {
        actionsEl.innerHTML += `<a href="/transaksi/${id}/struk" target="_blank" class="btn-primary" style="flex:1;justify-content:center;text-decoration:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Struk</a>`;
    }
    if (t.status === 'pending') {
        actionsEl.innerHTML += `<button onclick="batalkanTrx(${t.transaksi_id},'${t.nomor_transaksi}')" class="btn-danger" style="flex:1;justify-content:center;padding:9px 14px">Batalkan</button>`;
    }
    document.getElementById('modal-detail').style.display = 'flex';
}

async function batalkanTrx(id, nomor) {
    if (!confirm(`Batalkan transaksi ${nomor}?`)) return;
    const res = await apiFetch(`/transaksi/${id}/batalkan`, { method: 'POST', body: JSON.stringify({ alasan: 'Dibatalkan manual' }) });
    if (res?.success) { showToast('Transaksi dibatalkan.', 'info'); document.getElementById('modal-detail').style.display = 'none'; loadTransaksi(1); }
    else showToast(res?.message || 'Gagal.', 'error');
}

loadTransaksi(1);
</script>
@endpush
