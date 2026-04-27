@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan & Analitik')
@section('page-subtitle', 'Laporan keuangan dan penjualan')

@section('content')
<div style="display:flex;flex-direction:column;gap:20px">

    {{-- Period selector --}}
    <div style="background:#0d1117;border:1px solid #161b22;border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <div style="display:flex;gap:6px">
            @foreach(['Hari Ini'=>'today','7 Hari'=>'week','Bulan Ini'=>'month','Kustom'=>'custom'] as $label => $val)
            <button onclick="setPeriod('{{ $val }}')" data-period="{{ $val }}" class="period-btn" style="padding:7px 14px;border-radius:7px;border:1px solid {{ $val==='month'?'rgba(245,158,11,.35)':'#30363d' }};background:{{ $val==='month'?'rgba(245,158,11,.1)':'transparent' }};color:{{ $val==='month'?'#fbbf24':'#8b949e' }};font-size:12.5px;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif">{{ $label }}</button>
            @endforeach
        </div>
        <div id="custom-range" style="display:none;align-items:center;gap:8px">
            <input type="date" id="custom-dari" class="pos-input" style="width:150px">
            <span style="color:#484f58">—</span>
            <input type="date" id="custom-sampai" class="pos-input" style="width:150px">
            <button onclick="loadLaporan()" class="btn-primary" style="padding:9px 16px">Tampilkan</button>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px">
            <button onclick="exportPdf()" style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;border:1px solid rgba(251,113,133,.25);background:rgba(251,113,133,.06);color:#fb7185;font-size:12.5px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s" onmouseover="this.style.background='rgba(251,113,133,.12)'" onmouseout="this.style.background='rgba(251,113,133,.06)'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                PDF
            </button>
            <button onclick="exportExcel()" style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;border:1px solid rgba(52,211,153,.25);background:rgba(52,211,153,.06);color:#34d399;font-size:12.5px;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s" onmouseover="this.style.background='rgba(52,211,153,.12)'" onmouseout="this.style.background='rgba(52,211,153,.06)'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Excel
            </button>
        </div>
    </div>

    {{-- KPI cards --}}
    <div id="kpi-cards" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px">
        @foreach(['Pendapatan Bersih','HPP','Laba Bruto','Margin (%)'] as $k)
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:12px;padding:18px 20px;animation:pulse 1.5s ease infinite">
            <div style="height:10px;background:#161b22;border-radius:4px;width:60%;margin-bottom:12px"></div>
            <div style="height:24px;background:#21262d;border-radius:4px;width:75%"></div>
        </div>
        @endforeach
    </div>

    {{-- Charts row --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;padding:22px">
            <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff;margin-bottom:4px">Grafik Pendapatan</h3>
            <p style="font-size:12px;color:#8b949e;margin-bottom:18px">Pendapatan per hari dalam periode</p>
            <div style="height:220px;position:relative"><canvas id="main-chart"></canvas></div>
        </div>
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;padding:22px">
            <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff;margin-bottom:4px">Metode Pembayaran</h3>
            <p style="font-size:12px;color:#8b949e;margin-bottom:18px">Distribusi per metode</p>
            <div style="height:180px;position:relative"><canvas id="pie-chart"></canvas></div>
            <div id="metode-legend" style="display:flex;flex-direction:column;gap:7px;margin-top:14px"></div>
        </div>
    </div>

    {{-- Produk terlaris --}}
    <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;overflow:hidden">
        <div style="padding:18px 22px;border-bottom:1px solid #161b22">
            <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff">Produk Terlaris</h3>
        </div>
        <div style="overflow-x:auto">
            <table class="pos-table" style="width:100%;border-collapse:collapse">
                <thead><tr><th>#</th><th>Produk</th><th>Qty Terjual</th><th>Total Pendapatan</th><th>Kontribusi</th></tr></thead>
                <tbody id="terlaris-tbody"><tr><td colspan="5" style="text-align:center;padding:32px;color:#484f58">Memuat...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let dari = '', sampai = '', mainChart = null, pieChart = null;

function setPeriod(p) {
    document.querySelectorAll('.period-btn').forEach(b => {
        const isActive = b.dataset.period === p;
        b.style.background  = isActive ? 'rgba(245,158,11,.1)' : 'transparent';
        b.style.borderColor = isActive ? 'rgba(245,158,11,.35)' : '#30363d';
        b.style.color       = isActive ? '#fbbf24' : '#8b949e';
    });
    const now = new Date(), fmt = d => d.toISOString().split('T')[0];
    document.getElementById('custom-range').style.display = 'none';
    if (p === 'today')  { dari = sampai = fmt(now); }
    else if (p === 'week')  { const s = new Date(now); s.setDate(now.getDate()-6); dari = fmt(s); sampai = fmt(now); }
    else if (p === 'month') { dari = fmt(new Date(now.getFullYear(), now.getMonth(), 1)); sampai = fmt(now); }
    else { document.getElementById('custom-range').style.display = 'flex'; return; }
    loadLaporan();
}

async function loadLaporan() {
    if (!dari || !sampai) {
        dari = document.getElementById('custom-dari').value;
        sampai = document.getElementById('custom-sampai').value;
    }
    if (!dari || !sampai) return;

    const [keuRes, harianRes, terlarisRes, trxRes] = await Promise.all([
        apiFetch(`/laporan/keuangan?dari=${dari}&sampai=${sampai}`),
        apiFetch(`/laporan/harian?dari=${dari}&sampai=${sampai}`),
        apiFetch(`/laporan/produk-terlaris?dari=${dari}&sampai=${sampai}&limit=10`),
        apiFetch(`/transaksi?dari=${dari}&sampai=${sampai}&per_page=200`),
    ]);

    renderKPI(keuRes?.data || {});
    renderMainChart(harianRes?.data?.harian || []);
    renderTerlaris(terlarisRes?.data || []);
    renderMetodePie(trxRes?.data || []);
}

function renderKPI(d) {
    const cards = [
        { label:'Pendapatan Bersih', value: rupiah(d.total_penjualan_bersih), color:'#fbbf24' },
        { label:'HPP (Harga Pokok)', value: rupiah(d.total_hpp), color:'#fb7185' },
        { label:'Laba Bruto',        value: rupiah(d.laba_bruto), color:'#34d399' },
        { label:'Margin Laba',       value: (d.margin_persen || 0) + '%', color:'#38bdf8' },
    ];
    document.getElementById('kpi-cards').innerHTML = cards.map((c,i) => `
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:12px;padding:18px 20px;animation:slideUp .4s ${i*.08}s ease both">
            <p style="font-size:11px;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;margin-bottom:8px">${c.label}</p>
            <p class="font-display" style="font-size:20px;font-weight:700;color:${c.color}">${c.value}</p>
        </div>
    `).join('');
}

function renderMainChart(rows) {
    const labels = rows.map(r => new Date(r.tanggal).toLocaleDateString('id-ID', { day:'numeric', month:'short' }));
    const values = rows.map(r => parseFloat(r.total_pendapatan || 0));
    const ctx = document.getElementById('main-chart').getContext('2d');
    if (mainChart) mainChart.destroy();
    const grad = ctx.createLinearGradient(0,0,0,220);
    grad.addColorStop(0,'rgba(245,158,11,.2)'); grad.addColorStop(1,'rgba(245,158,11,0)');
    mainChart = new Chart(ctx, {
        type:'bar',
        data: { labels, datasets:[
            { type:'line', label:'Trend', data:values, borderColor:'#f97316', borderWidth:2, pointRadius:0, tension:.4, yAxisID:'y', fill:false },
            { label:'Pendapatan', data:values, backgroundColor:grad, borderColor:'#f59e0b', borderWidth:1.5, borderRadius:4, yAxisID:'y' },
        ]},
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#0d1117', borderColor:'#30363d', borderWidth:1, titleColor:'#8b949e', bodyColor:'#e6edf3', callbacks:{ label: c => rupiah(c.raw) } } },
            scales:{
                x:{ grid:{color:'rgba(48,54,61,.4)'}, ticks:{color:'#8b949e', font:{size:10}} },
                y:{ grid:{color:'rgba(48,54,61,.4)'}, ticks:{color:'#8b949e', font:{size:10}, callback: v => 'Rp '+v.toLocaleString('id-ID')} }
            }
        }
    });
}

function renderMetodePie(trxItems) {
    const selesai = trxItems.filter(t => t.status === 'selesai');
    const grouped = {};
    selesai.forEach(t => { grouped[t.metode_pembayaran] = (grouped[t.metode_pembayaran] || 0) + 1; });
    const labels = Object.keys(grouped);
    const values = Object.values(grouped);
    const colors = ['#f59e0b','#38bdf8','#34d399','#f97316'];
    const ctx = document.getElementById('pie-chart').getContext('2d');
    if (pieChart) pieChart.destroy();
    pieChart = new Chart(ctx, {
        type:'doughnut',
        data:{ labels, datasets:[{ data:values, backgroundColor:colors.slice(0,labels.length), borderColor:'#080b10', borderWidth:3, hoverBorderWidth:4 }]},
        options:{ responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{display:false}, tooltip:{ backgroundColor:'#0d1117', borderColor:'#30363d', borderWidth:1, bodyColor:'#e6edf3' } } }
    });
    const total = values.reduce((a,b)=>a+b,0);
    document.getElementById('metode-legend').innerHTML = labels.map((l,i) => `
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px"><div style="width:8px;height:8px;border-radius:2px;background:${colors[i]}"></div><span style="font-size:12.5px;color:#8b949e;text-transform:capitalize">${l}</span></div>
            <div style="text-align:right"><span style="font-size:12.5px;font-weight:600;color:#e6edf3">${values[i]}</span><span style="font-size:11px;color:#484f58;margin-left:4px">(${((values[i]/total)*100).toFixed(0)}%)</span></div>
        </div>
    `).join('');
}

function renderTerlaris(items) {
    if (!items.length) { document.getElementById('terlaris-tbody').innerHTML = `<tr><td colspan="5" style="text-align:center;padding:32px;color:#484f58;font-size:13px">Belum ada data penjualan</td></tr>`; return; }
    const maxRev = Math.max(...items.map(i => parseFloat(i.total_pendapatan)));
    const totalRev = items.reduce((s,i) => s + parseFloat(i.total_pendapatan), 0);
    document.getElementById('terlaris-tbody').innerHTML = items.map((p,i) => `
        <tr>
            <td style="text-align:center">
                <div style="width:26px;height:26px;border-radius:7px;background:${i===0?'linear-gradient(135deg,#f59e0b,#d97706)':i===1?'rgba(245,158,11,.15)':i===2?'rgba(245,158,11,.08)':'#161b22'};display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:${i<3?'#fbbf24':'#484f58'}">${i+1}</div>
            </td>
            <td>
                <span style="font-size:13.5px;font-weight:500;color:#e6edf3">${p.nama_produk}</span>
                <span style="font-size:11px;color:#484f58;display:block">${p.kode_produk}</span>
            </td>
            <td><span class="font-display" style="font-size:14px;font-weight:700;color:#fbbf24">${parseInt(p.total_terjual).toLocaleString('id-ID')}</span><span style="font-size:11px;color:#484f58"> unit</span></td>
            <td class="font-display" style="font-size:13.5px;font-weight:700;color:#fff">${rupiah(p.total_pendapatan)}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="flex:1;height:5px;background:#161b22;border-radius:3px;overflow:hidden"><div style="height:5px;background:linear-gradient(90deg,#f59e0b,#d97706);border-radius:3px;width:${((parseFloat(p.total_pendapatan)/totalRev)*100).toFixed(1)}%"></div></div>
                    <span style="font-size:12px;color:#8b949e;min-width:36px;text-align:right">${((parseFloat(p.total_pendapatan)/totalRev)*100).toFixed(1)}%</span>
                </div>
            </td>
        </tr>
    `).join('');
}

function exportPdf() { window.open(`/api/laporan/export/pdf?dari=${dari}&sampai=${sampai}&tipe=penjualan&token=${localStorage.getItem('pos_token')}`, '_blank'); }
function exportExcel() { window.open(`/api/laporan/export/excel?dari=${dari}&sampai=${sampai}&token=${localStorage.getItem('pos_token')}`, '_blank'); }

setPeriod('month');
</script>
@endpush
