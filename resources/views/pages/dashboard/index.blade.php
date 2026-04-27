@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional toko hari ini')

@section('content')
<div style="display:flex;flex-direction:column;gap:24px">

    {{-- ── GREETING ─────────────────────────────────────── --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h2 class="font-display" style="font-size:22px;font-weight:700;color:#fff">
                Selamat datang, <span style="background:linear-gradient(135deg,#fbbf24,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent">{{ session('user.nama_lengkap', 'Pengguna') }}</span> 👋
            </h2>
            <p style="font-size:13px;color:#8b949e;margin-top:4px" id="today-date"></p>
        </div>
        <a href="{{ route('kasir.index') }}" style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#080b10;font-weight:700;font-size:13.5px;padding:10px 20px;border-radius:10px;text-decoration:none;box-shadow:0 4px 16px rgba(245,158,11,0.3);transition:all .2s" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform=''">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            Buka Kasir
        </a>
    </div>

    {{-- ── STAT CARDS ───────────────────────────────────── --}}
    <div id="stat-cards" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
        {{-- Skeleton --}}
        @for($i=0;$i<4;$i++)
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;padding:22px 24px;animation:pulse 1.5s ease infinite">
            <div style="height:12px;background:#161b22;border-radius:6px;width:60%;margin-bottom:16px"></div>
            <div style="height:28px;background:#21262d;border-radius:6px;width:80%;margin-bottom:12px"></div>
            <div style="height:10px;background:#161b22;border-radius:6px;width:40%"></div>
        </div>
        @endfor
    </div>

    {{-- ── CHARTS + RECENT ─────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px">

        {{-- Chart pendapatan --}}
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;padding:24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <div>
                    <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff">Pendapatan 7 Hari</h3>
                    <p style="font-size:12px;color:#8b949e;margin-top:2px">Tren penjualan mingguan</p>
                </div>
                <div id="total-7d" style="text-align:right">
                    <p style="font-size:11px;color:#8b949e;text-transform:uppercase;letter-spacing:.06em">Total</p>
                    <p class="font-display" style="font-size:16px;font-weight:700;color:#fbbf24">—</p>
                </div>
            </div>
            <div style="position:relative;height:200px">
                <canvas id="revenue-chart"></canvas>
            </div>
        </div>

        {{-- Produk terlaris --}}
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;padding:24px">
            <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff;margin-bottom:4px">Terlaris Hari Ini</h3>
            <p style="font-size:12px;color:#8b949e;margin-bottom:18px">Top 5 produk terjual</p>
            <div id="terlaris-list" style="display:flex;flex-direction:column;gap:10px">
                <p style="font-size:13px;color:#484f58;text-align:center;padding:20px 0">Memuat data...</p>
            </div>
        </div>
    </div>

    {{-- ── BOTTOM ROW ───────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        {{-- Transaksi terbaru --}}
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;overflow:hidden">
            <div style="padding:20px 24px;border-bottom:1px solid #161b22;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff">Transaksi Terbaru</h3>
                    <p style="font-size:12px;color:#8b949e;margin-top:2px">10 transaksi terakhir</p>
                </div>
                <a href="{{ route('transaksi.index') }}" style="font-size:12px;color:#f59e0b;text-decoration:none">Lihat semua →</a>
            </div>
            <div id="recent-trx" style="padding:8px 0">
                <p style="font-size:13px;color:#484f58;text-align:center;padding:24px">Memuat...</p>
            </div>
        </div>

        {{-- Notifikasi stok --}}
        <div style="background:#0d1117;border:1px solid #161b22;border-radius:14px;overflow:hidden">
            <div style="padding:20px 24px;border-bottom:1px solid #161b22">
                <h3 class="font-display" style="font-size:15px;font-weight:700;color:#fff">Peringatan Stok</h3>
                <p style="font-size:12px;color:#8b949e;margin-top:2px">Stok minimal & akan kedaluwarsa</p>
            </div>
            <div id="stok-alerts" style="padding:8px 0">
                <p style="font-size:13px;color:#484f58;text-align:center;padding:24px">Memuat...</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.getElementById('today-date').textContent = new Date().toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

// ── Stat cards ────────────────────────────────────────────
async function loadDashboard() {
    const res = await apiFetch('/laporan/dashboard');
    if (!res?.success) return;
    const d = res.data;

    const cards = [
        {
            label: 'Pendapatan Hari Ini',
            value: rupiah(d.hari_ini.total_pendapatan),
            sub: `${d.hari_ini.jumlah_transaksi} transaksi`,
            icon: `<path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>`,
            color: '#fbbf24', bg: 'rgba(251,191,36,0.08)',
        },
        {
            label: 'Transaksi Bulan Ini',
            value: d.bulan_ini.jumlah_transaksi.toLocaleString('id-ID'),
            sub: rupiah(d.bulan_ini.total_pendapatan),
            icon: `<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>`,
            color: '#38bdf8', bg: 'rgba(56,189,248,0.08)',
        },
        {
            label: 'Produk Aktif',
            value: d.produk_aktif.toLocaleString('id-ID'),
            sub: 'produk tersedia',
            icon: `<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>`,
            color: '#34d399', bg: 'rgba(52,211,153,0.08)',
        },
        {
            label: 'Stok Kritis',
            value: d.stok_minimal.toLocaleString('id-ID'),
            sub: 'produk perlu restok',
            icon: `<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>`,
            color: d.stok_minimal > 0 ? '#fb7185' : '#34d399',
            bg:    d.stok_minimal > 0 ? 'rgba(251,113,133,0.08)' : 'rgba(52,211,153,0.08)',
        },
    ];

    document.getElementById('stat-cards').innerHTML = cards.map((c, i) => `
        <div class="stat-card" style="animation:slideUp .4s ${i*0.08}s ease both">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
                <div style="width:40px;height:40px;border-radius:10px;background:${c.bg};border:1px solid ${c.color}20;display:flex;align-items:center;justify-content:center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="${c.color}" stroke-width="1.8" style="width:18px;height:18px">${c.icon}</svg>
                </div>
                <div style="width:6px;height:6px;border-radius:50%;background:${c.color};margin-top:6px;box-shadow:0 0 8px ${c.color}"></div>
            </div>
            <p style="font-size:11.5px;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px">${c.label}</p>
            <p class="font-display" style="font-size:22px;font-weight:700;color:#fff;line-height:1">${c.value}</p>
            <p style="font-size:12px;color:#484f58;margin-top:6px">${c.sub}</p>
        </div>
    `).join('');
}

// ── Revenue chart ─────────────────────────────────────────
async function loadChart() {
    const today = new Date(); const sevenDaysAgo = new Date(); sevenDaysAgo.setDate(today.getDate()-6);
    const dari = sevenDaysAgo.toISOString().split('T')[0];
    const sampai = today.toISOString().split('T')[0];

    const res = await apiFetch(`/laporan/harian?dari=${dari}&sampai=${sampai}`);
    if (!res?.success) return;

    const rows = res.data.harian || [];
    const labels = rows.map(r => new Date(r.tanggal).toLocaleDateString('id-ID', { weekday:'short', day:'numeric' }));
    const values = rows.map(r => parseFloat(r.total_pendapatan || 0));
    const total  = values.reduce((a,b)=>a+b, 0);
    document.getElementById('total-7d').querySelector('.font-display').textContent = rupiah(total);

    const ctx = document.getElementById('revenue-chart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 200);
    grad.addColorStop(0, 'rgba(245,158,11,0.25)');
    grad.addColorStop(1, 'rgba(245,158,11,0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Pendapatan',
                data: values,
                borderColor: '#f59e0b',
                backgroundColor: grad,
                borderWidth: 2,
                pointBackgroundColor: '#f59e0b',
                pointBorderColor: '#080b10',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#0d1117', borderColor: '#30363d', borderWidth: 1,
                titleColor: '#8b949e', bodyColor: '#e6edf3', padding: 12,
                callbacks: { label: ctx => rupiah(ctx.raw) }
            }},
            scales: {
                x: { grid: { color: 'rgba(48,54,61,0.4)' }, ticks: { color: '#8b949e', font: { size: 11 } } },
                y: { grid: { color: 'rgba(48,54,61,0.4)' }, ticks: { color: '#8b949e', font: { size: 11 }, callback: v => 'Rp '+v.toLocaleString('id-ID') } }
            }
        }
    });
}

// ── Top produk terlaris ───────────────────────────────────
async function loadTerlaris() {
    const today = new Date().toISOString().split('T')[0];
    const res = await apiFetch(`/laporan/produk-terlaris?dari=${today}&sampai=${today}&limit=5`);
    const items = res?.data || [];
    const el = document.getElementById('terlaris-list');
    if (!items.length) { el.innerHTML = `<p style="font-size:13px;color:#484f58;text-align:center;padding:20px 0">Belum ada penjualan hari ini</p>`; return; }

    const maxQty = Math.max(...items.map(i => i.total_terjual));
    el.innerHTML = items.map((p, i) => `
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:22px;height:22px;border-radius:6px;background:${i===0?'linear-gradient(135deg,#f59e0b,#d97706)':'#161b22'};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:${i===0?'#080b10':'#8b949e'};flex-shrink:0">${i+1}</div>
            <div style="flex:1;min-width:0">
                <p style="font-size:13px;color:#c9d1d9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.nama_produk}</p>
                <div style="height:3px;background:#161b22;border-radius:2px;margin-top:5px">
                    <div style="height:3px;background:linear-gradient(90deg,#f59e0b,#d97706);border-radius:2px;width:${(p.total_terjual/maxQty*100).toFixed(0)}%;transition:width .6s ease"></div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <p style="font-size:12px;font-weight:600;color:#fbbf24">${p.total_terjual}</p>
                <p style="font-size:10px;color:#484f58">unit</p>
            </div>
        </div>
    `).join('');
}

// ── Transaksi terbaru ─────────────────────────────────────
async function loadRecentTrx() {
    const res = await apiFetch('/transaksi?per_page=8&status=selesai');
    const items = res?.data || [];
    const el = document.getElementById('recent-trx');
    if (!items.length) { el.innerHTML = `<p style="font-size:13px;color:#484f58;text-align:center;padding:24px">Belum ada transaksi</p>`; return; }

    const statusBadge = s => s === 'selesai' ? `<span class="badge badge-green">✓ Selesai</span>` : s === 'pending' ? `<span class="badge badge-amber">⏳ Pending</span>` : `<span class="badge badge-red">✗ Batal</span>`;

    el.innerHTML = items.map(t => `
        <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;transition:background .2s;cursor:pointer" onmouseover="this.style.background='rgba(22,27,34,0.5)'" onmouseout="this.style.background=''" onclick="window.location='/transaksi/${t.transaksi_id}'">
            <div style="width:36px;height:36px;border-radius:10px;background:#161b22;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg viewBox="0 0 24 24" fill="none" stroke="#8b949e" stroke-width="1.8" style="width:16px;height:16px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <p style="font-size:13px;color:#c9d1d9;font-weight:500">${t.nomor_transaksi}</p>
                <p style="font-size:11px;color:#8b949e">${new Date(t.tanggal_transaksi).toLocaleString('id-ID')}</p>
            </div>
            <div style="text-align:right">
                <p style="font-size:13px;font-weight:600;color:#fff">${rupiah(t.total_pembayaran)}</p>
                ${statusBadge(t.status)}
            </div>
        </div>
    `).join('');
}

// ── Stok alerts ────────────────────────────────────────────
async function loadStokAlerts() {
    const res = await apiFetch('/stok/notifikasi');
    if (!res?.success) return;
    const el = document.getElementById('stok-alerts');
    const lowItems = res.data.stok_minimal?.items || [];
    const expItems = res.data.kedaluwarsa?.akan_kedaluwarsa?.items || [];
    const all = [
        ...lowItems.map(i => ({ type: 'low', nama: i.produk?.nama_produk, val: `Stok: ${i.jumlah_stok} / min ${i.stok_minimal}`, color: '#fb7185', icon: '📦' })),
        ...expItems.map(i => ({ type: 'exp', nama: i.produk?.nama_produk, val: `Exp: ${i.tanggal_kedaluwarsa}`, color: '#fbbf24', icon: '⚠️' })),
    ].slice(0, 8);

    if (!all.length) {
        el.innerHTML = `<div style="text-align:center;padding:24px"><div style="font-size:24px;margin-bottom:8px">✅</div><p style="font-size:13px;color:#34d399">Semua stok dalam kondisi baik</p></div>`;
        return;
    }
    el.innerHTML = all.map(item => `
        <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-bottom:1px solid rgba(22,27,34,0.6)">
            <span style="font-size:16px">${item.icon}</span>
            <div style="flex:1;min-width:0">
                <p style="font-size:13px;color:#c9d1d9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.nama || '—'}</p>
                <p style="font-size:11px;color:${item.color}">${item.val}</p>
            </div>
        </div>
    `).join('');
}

// ── Init ───────────────────────────────────────────────────
loadDashboard();
loadChart();
loadTerlaris();
loadRecentTrx();
loadStokAlerts();
</script>
@endpush
