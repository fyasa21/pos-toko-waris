@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional toko hari ini')

@section('content')

{{-- Greeting --}}
<div style="position:sticky;top:-24px;z-index:30;background:var(--bg);margin:-24px -24px -4px -24px;padding:24px 24px 16px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2 style="font-size:18px;font-weight:800;color:var(--text-1)">
      Halo, <span style="color:var(--teal)">{{ session('user.nama_lengkap', 'Pengguna') }}</span> 👋
    </h2>
    <p id="tanggal-hari-ini" style="font-size:13px;color:var(--text-3);margin-top:3px"></p>
  </div>
  <a href="{{ route('kasir') }}" class="btn btn-primary btn-lg">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px">
      <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
    </svg>
    Buka Kasir
  </a>
</div>

@if(session('user.role') === 'pemilik')
{{-- Stat cards --}}
<div class="stat-grid" id="stat-grid">
  @foreach(['','','',''] as $_)
  <div class="stat-card" style="animation:slideUp .4s ease both">
    <div class="skel" style="width:38px;height:38px;border-radius:10px;margin-bottom:14px"></div>
    <div class="skel" style="width:60%;height:11px;margin-bottom:8px"></div>
    <div class="skel" style="width:75%;height:26px;margin-bottom:8px"></div>
    <div class="skel" style="width:45%;height:11px"></div>
  </div>
  @endforeach
</div>

{{-- Chart + Terlaris --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:16px">

  {{-- Revenue chart --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Pendapatan 7 Hari Terakhir</div>
        <div class="card-subtitle">Tren penjualan mingguan</div>
      </div>
      <div style="font-family:'Geist Mono',monospace;font-size:13px;font-weight:600;color:var(--teal)" id="total-7d">—</div>
    </div>
    <div class="card-body">
      <canvas id="chart-revenue" style="height:200px;width:100%"></canvas>
    </div>
  </div>

  {{-- Terlaris --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Terlaris Hari Ini</div>
        <div class="card-subtitle">Berdasarkan qty terjual</div>
      </div>
    </div>
    <div class="card-body" style="padding:12px 16px" id="terlaris-box">
      <div class="empty-state" style="padding:24px 0">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
        <p>Belum ada penjualan</p>
      </div>
    </div>
  </div>
</div>
@endif

{{-- Bottom row --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

  {{-- Transaksi terbaru --}}
  <div class="card" style="overflow:hidden">
    <div class="card-header">
      <div>
        <div class="card-title">Transaksi Terbaru</div>
        <div class="card-subtitle">8 transaksi terakhir</div>
      </div>
      <a href="{{ route('transaksi') }}" class="btn btn-ghost btn-sm">Lihat Semua →</a>
    </div>
    <div id="recent-trx">
      <div class="empty-state"><div class="spinner"></div><p>Memuat...</p></div>
    </div>
  </div>

  {{-- Stok kritis --}}
  <div class="card" style="overflow:hidden">
    <div class="card-header">
      <div>
        <div class="card-title">Peringatan Stok</div>
        <div class="card-subtitle">Stok minimal & kedaluwarsa</div>
      </div>
      <a href="{{ route('produk') }}" class="btn btn-ghost btn-sm">Kelola →</a>
    </div>
    <div id="stok-alerts">
      <div class="empty-state"><div class="spinner"></div><p>Memuat...</p></div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.getElementById('tanggal-hari-ini').textContent =
  new Date().toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

// ── Stat cards ─────────────────────────────────────────────
async function loadStats() {
  const res = await api('/laporan/dashboard');
  if (!res?.success) return;
  const d = res.data;
  const CARDS = [
    { icon:`<path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>`, cls:'teal',
      label:'Pendapatan Hari Ini', value: rupiah(d.hari_ini.total_pendapatan),
      sub: `${d.hari_ini.jumlah_transaksi} transaksi selesai` },
    { icon:`<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>`, cls:'amber',
      label:'Transaksi Bulan Ini', value: d.bulan_ini.jumlah_transaksi.toLocaleString('id-ID'),
      sub: rupiah(d.bulan_ini.total_pendapatan) },
    { icon:`<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>`, cls:'green',
      label:'Produk Aktif', value: d.produk_aktif.toLocaleString('id-ID'),
      sub: 'produk tersedia' },
    { icon:`<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>`, cls: d.stok_minimal > 0 ? 'red' : 'green',
      label:'Stok Kritis', value: d.stok_minimal.toLocaleString('id-ID'),
      sub: d.stok_minimal > 0 ? 'produk perlu restok' : 'Semua stok aman' },
  ];
  document.getElementById('stat-grid').innerHTML = CARDS.map((c,i) => `
    <div class="stat-card ${c.cls}" style="animation:slideUp .4s ${i*.07}s ease both">
      <div class="stat-icon ${c.cls}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">${c.icon}</svg>
      </div>
      <div class="stat-label">${c.label}</div>
      <div class="stat-value">${c.value}</div>
      <div class="stat-sub">${c.sub}</div>
    </div>
  `).join('');
}

// ── Revenue chart ───────────────────────────────────────────
async function loadChart() {
  const today = new Date(), fmt = d => d.toISOString().split('T')[0];
  const from  = new Date(today); from.setDate(today.getDate()-6);
  const res   = await api(`/laporan/harian?dari=${fmt(from)}&sampai=${fmt(today)}`);
  const rows  = res?.data?.harian || [];
  const labels = rows.map(r => new Date(r.tanggal).toLocaleDateString('id-ID',{weekday:'short',day:'numeric'}));
  const vals   = rows.map(r => parseFloat(r.total_pendapatan||0));
  const total  = vals.reduce((a,b)=>a+b,0);
  document.getElementById('total-7d').textContent = rupiah(total);

  const ctx = document.getElementById('chart-revenue').getContext('2d');
  const grad = ctx.createLinearGradient(0,0,0,200);
  grad.addColorStop(0,'rgba(13,148,136,.18)'); grad.addColorStop(1,'rgba(13,148,136,0)');
  new Chart(ctx,{
    type:'line',
    data:{ labels, datasets:[{ data:vals, borderColor:'#0d9488', backgroundColor:grad,
      borderWidth:2, pointBackgroundColor:'#0d9488', pointBorderColor:'#fff', pointBorderWidth:2,
      pointRadius:4, pointHoverRadius:6, tension:.4, fill:true }]},
    options:{ responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{display:false}, tooltip:{
        backgroundColor:'#0f1729', titleColor:'#8898aa', bodyColor:'#fff',
        padding:12, cornerRadius:8, callbacks:{ label: c=>rupiah(c.raw) }}},
      scales:{
        x:{ grid:{color:'rgba(226,229,234,.5)'}, ticks:{color:'#8898aa',font:{size:11,family:'Plus Jakarta Sans'}}},
        y:{ grid:{color:'rgba(226,229,234,.5)'}, ticks:{color:'#8898aa',font:{size:11,family:'Plus Jakarta Sans'}, callback:v=>'Rp '+v.toLocaleString('id-ID')}}
      }}
  });
}

// ── Terlaris ────────────────────────────────────────────────
async function loadTerlaris() {
  const today = new Date().toISOString().split('T')[0];
  const res = await api(`/laporan/produk-terlaris?dari=${today}&sampai=${today}&limit=5`);
  const items = res?.data || [];
  const el = document.getElementById('terlaris-box');
  if(!items.length) return;
  const max = Math.max(...items.map(i=>i.total_terjual));
  el.innerHTML = `<div style="display:flex;flex-direction:column;gap:10px;padding:4px 0">` +
    items.map((p,i)=>`
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:20px;height:20px;border-radius:5px;background:${i===0?'var(--teal)':'var(--surface-2)'};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:${i===0?'#fff':'var(--text-3)'};flex-shrink:0">${i+1}</div>
      <div style="flex:1;min-width:0">
        <p style="font-size:12.5px;font-weight:600;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.nama_produk}</p>
        <div style="height:3px;background:var(--surface-2);border-radius:2px;margin-top:4px">
          <div style="height:3px;background:var(--teal);border-radius:2px;width:${(p.total_terjual/max*100).toFixed(0)}%;transition:width .6s ease"></div>
        </div>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <span style="font-family:'Geist Mono',monospace;font-size:13px;font-weight:600;color:var(--teal)">${p.total_terjual}</span>
        <span style="font-size:11px;color:var(--text-4)"> unit</span>
      </div>
    </div>`).join('') + `</div>`;
}

// ── Recent transaksi ────────────────────────────────────────
async function loadRecentTrx() {
  const res = await api('/transaksi?per_page=8&status=selesai');
  const items = res?.data || [];
  const el = document.getElementById('recent-trx');
  if(!items.length){ el.innerHTML = `<div class="empty-state"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><p>Belum ada transaksi</p></div>`; return; }
  const statusBadge = s => ({selesai:`<span class="badge badge-green">Selesai</span>`,pending:`<span class="badge badge-amber">Pending</span>`,batal:`<span class="badge badge-red">Batal</span>`}[s]||s);
  el.innerHTML = `<table class="table"><tbody>` +
    items.map(t=>`<tr onclick="window.location='/transaksi'" style="cursor:pointer">
      <td><span style="font-family:'Geist Mono',monospace;font-size:12px;font-weight:600;color:var(--teal)">${t.nomor_transaksi}</span></td>
      <td style="font-size:12px;color:var(--text-3)">${tanggal(t.tanggal_transaksi,true)}</td>
      <td style="font-weight:600;color:var(--text-1)">${rupiah(t.total_pembayaran)}</td>
      <td>${statusBadge(t.status)}</td>
    </tr>`).join('') + `</tbody></table>`;
}

// ── Stok alerts ─────────────────────────────────────────────
async function loadStokAlerts() {
  const res = await api('/stok/notifikasi');
  if(!res?.success) return;
  const el = document.getElementById('stok-alerts');
  const low = res.data.stok_minimal?.items || [];
  const exp = [...(res.data.kedaluwarsa?.akan_kedaluwarsa?.items||[]),...(res.data.kedaluwarsa?.sudah_kedaluwarsa?.items||[])];
  const all = [...low.map(i=>({nama:i.produk?.nama_produk,val:`Stok: ${i.jumlah_stok}/${i.stok_minimal}`,cls:'amber'})),
               ...exp.map(i=>({nama:i.produk?.nama_produk,val:`Exp: ${i.tanggal_kedaluwarsa}`,cls:'red'}))].slice(0,8);
  if(!all.length){el.innerHTML=`<div class="empty-state"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg><p>Semua stok aman</p></div>`;return;}
  el.innerHTML=`<div>`+all.map(a=>`<div style="display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--border)">
    <div style="width:7px;height:7px;border-radius:50%;background:var(--${a.cls});flex-shrink:0"></div>
    <div style="flex:1;min-width:0"><p style="font-size:13px;font-weight:600;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${a.nama||'—'}</p><p style="font-size:12px;color:var(--text-3)">${a.val}</p></div>
  </div>`).join('')+`</div>`;
}

loadStats(); loadChart(); loadTerlaris(); loadRecentTrx(); loadStokAlerts();
</script>
@endpush
