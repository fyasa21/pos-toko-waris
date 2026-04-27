@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Analitik penjualan dan keuangan')

@section('content')

{{-- Period selector --}}
<div class="card card-body" style="padding:14px 18px">
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <div class="chip-row">
      @foreach(['today'=>'Hari Ini','week'=>'7 Hari','month'=>'Bulan Ini','custom'=>'Kustom'] as $k=>$v)
      <button class="chip{{ $k==='month'?' active':'' }}" data-period="{{ $k }}" onclick="setPeriod('{{ $k }}',this)">{{ $v }}</button>
      @endforeach
    </div>
    <div id="custom-range" style="display:none;align-items:center;gap:8px">
      <input id="c-dari" type="date" class="input" style="width:150px">
      <span style="color:var(--text-4)">—</span>
      <input id="c-sampai" type="date" class="input" style="width:150px">
      <button onclick="loadLaporan()" class="btn btn-primary btn-sm">Tampilkan</button>
    </div>
    <div style="margin-left:auto;display:flex;gap:8px">
      <button onclick="exportPdf()" class="btn btn-sm" style="background:var(--red-light);color:var(--red);border:1px solid rgba(239,68,68,.2)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
        PDF
      </button>
      <button onclick="exportExcel()" class="btn btn-sm" style="background:var(--green-light);color:#15803d;border:1px solid rgba(34,197,94,.2)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
        Excel
      </button>
    </div>
  </div>
</div>

{{-- KPI cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px" id="kpi-grid">
  @foreach(range(0,3) as $_)
  <div class="stat-card teal">
    <div class="skel" style="height:10px;width:55%;margin-bottom:10px"></div>
    <div class="skel" style="height:24px;width:70%;margin-bottom:6px"></div>
    <div class="skel" style="height:10px;width:40%"></div>
  </div>
  @endforeach
</div>

{{-- Charts --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
  <div class="card">
    <div class="card-header">
      <div><div class="card-title">Pendapatan Harian</div><div class="card-subtitle">Trend dalam periode</div></div>
    </div>
    <div class="card-body"><canvas id="chart-bar" style="height:200px;width:100%"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">Metode Pembayaran</div></div>
    <div class="card-body" style="padding:16px">
      <div style="height:160px;position:relative"><canvas id="chart-pie"></canvas></div>
      <div id="pie-legend" style="margin-top:14px;display:flex;flex-direction:column;gap:6px"></div>
    </div>
  </div>
</div>

{{-- Terlaris --}}
<div class="card" style="overflow:hidden">
  <div class="card-header"><div class="card-title">Produk Terlaris</div></div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>#</th><th>Produk</th><th>Qty Terjual</th><th>Total Pendapatan</th><th>Kontribusi</th></tr></thead>
      <tbody id="terlaris-tbody"><tr><td colspan="5" style="text-align:center;padding:32px"><div class="spinner" style="margin:0 auto"></div></td></tr></tbody>
    </table>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let periodeFrom='', periodeTo='', barChart=null, pieChart=null;

function setPeriod(p, btn) {
  document.querySelectorAll('.chip').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  const now=new Date(), fmt=d=>d.toISOString().split('T')[0];
  document.getElementById('custom-range').style.display='none';
  if (p==='today') { periodeFrom=periodeTo=fmt(now); }
  else if (p==='week') { const s=new Date(now);s.setDate(now.getDate()-6);periodeFrom=fmt(s);periodeTo=fmt(now); }
  else if (p==='month') { periodeFrom=fmt(new Date(now.getFullYear(),now.getMonth(),1));periodeTo=fmt(now); }
  else { document.getElementById('custom-range').style.display='flex'; return; }
  loadLaporan();
}

async function loadLaporan() {
  if (!periodeFrom||!periodeTo) { periodeFrom=document.getElementById('c-dari').value; periodeTo=document.getElementById('c-sampai').value; }
  if (!periodeFrom||!periodeTo) return;
  const [keu,harian,terlaris,trx] = await Promise.all([
    api(`/laporan/keuangan?dari=${periodeFrom}&sampai=${periodeTo}`),
    api(`/laporan/harian?dari=${periodeFrom}&sampai=${periodeTo}`),
    api(`/laporan/produk-terlaris?dari=${periodeFrom}&sampai=${periodeTo}&limit=10`),
    api(`/transaksi?dari=${periodeFrom}&sampai=${periodeTo}&per_page=200`),
  ]);
  renderKPI(keu?.data||{});
  renderBar(harian?.data?.harian||[]);
  renderPie(trx?.data||[]);
  renderTerlaris(terlaris?.data||[]);
}

function renderKPI(d) {
  const cards=[
    {label:'Pendapatan Bersih',val:rupiah(d.total_penjualan_bersih||0),sub:'setelah diskon',cls:'teal'},
    {label:'HPP',val:rupiah(d.total_hpp||0),sub:'harga pokok penjualan',cls:'red'},
    {label:'Laba Bruto',val:rupiah(d.laba_bruto||0),sub:`margin ${d.margin_persen||0}%`,cls:'green'},
    {label:'Total Diskon',val:rupiah(d.total_diskon||0),sub:'diberikan ke pelanggan',cls:'amber'},
  ];
  document.getElementById('kpi-grid').innerHTML=cards.map((c,i)=>`
    <div class="stat-card ${c.cls}" style="animation:slideUp .4s ${i*.07}s ease both">
      <div class="stat-label">${c.label}</div>
      <div class="stat-value" style="font-size:20px">${c.val}</div>
      <div class="stat-sub">${c.sub}</div>
    </div>`).join('');
}

function renderBar(rows) {
  const labels=rows.map(r=>new Date(r.tanggal).toLocaleDateString('id-ID',{day:'numeric',month:'short'}));
  const vals=rows.map(r=>parseFloat(r.total_pendapatan||0));
  const ctx=document.getElementById('chart-bar').getContext('2d');
  if (barChart) barChart.destroy();
  const grad=ctx.createLinearGradient(0,0,0,200);
  grad.addColorStop(0,'rgba(13,148,136,.2)');grad.addColorStop(1,'rgba(13,148,136,0)');
  barChart=new Chart(ctx,{
    type:'bar',
    data:{labels,datasets:[
      {type:'line',data:vals,borderColor:'#0f766e',borderWidth:2,pointRadius:0,tension:.4,fill:false},
      {label:'Pendapatan',data:vals,backgroundColor:grad,borderColor:'#0d9488',borderWidth:1.5,borderRadius:5,borderSkipped:false},
    ]},
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{display:false},tooltip:{backgroundColor:'#0f1729',titleColor:'#8898aa',bodyColor:'#fff',padding:12,cornerRadius:8,callbacks:{label:c=>rupiah(c.raw)}}},
      scales:{
        x:{grid:{color:'rgba(226,229,234,.5)'},ticks:{color:'#8898aa',font:{size:10,family:'Plus Jakarta Sans'}}},
        y:{grid:{color:'rgba(226,229,234,.5)'},ticks:{color:'#8898aa',font:{size:10,family:'Plus Jakarta Sans'},callback:v=>'Rp'+v.toLocaleString('id-ID')}}
      }}
  });
}

function renderPie(items) {
  const done=items.filter(t=>t.status==='selesai');
  const grp={};done.forEach(t=>{grp[t.metode_pembayaran]=(grp[t.metode_pembayaran]||0)+1;});
  const labels=Object.keys(grp),vals=Object.values(grp);
  const colors=['#0d9488','#3b82f6','#22c55e','#f59e0b'];
  const ctx=document.getElementById('chart-pie').getContext('2d');
  if (pieChart) pieChart.destroy();
  pieChart=new Chart(ctx,{type:'doughnut',data:{labels,datasets:[{data:vals,backgroundColor:colors.slice(0,labels.length),borderColor:'#fff',borderWidth:2,hoverBorderWidth:3}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'72%',plugins:{legend:{display:false},tooltip:{backgroundColor:'#0f1729',bodyColor:'#fff',padding:10,cornerRadius:8}}}});
  const total=vals.reduce((a,b)=>a+b,0);
  document.getElementById('pie-legend').innerHTML=labels.map((l,i)=>`
    <div style="display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:7px"><div style="width:8px;height:8px;border-radius:2px;background:${colors[i]};flex-shrink:0"></div><span style="font-size:12.5px;color:var(--text-2);text-transform:capitalize">${l}</span></div>
      <span style="font-family:'Geist Mono',monospace;font-size:12.5px;font-weight:600;color:var(--text-1)">${vals[i]} <span style="color:var(--text-4);font-weight:400">(${((vals[i]/total)*100).toFixed(0)}%)</span></span>
    </div>`).join('');
}

function renderTerlaris(items) {
  const tbody=document.getElementById('terlaris-tbody');
  if(!items.length){tbody.innerHTML=`<tr><td colspan="5" style="text-align:center;padding:32px"><div class="empty-state" style="padding:0"><p style="color:var(--text-4);font-size:13px">Belum ada data</p></div></td></tr>`;return;}
  const totRev=items.reduce((s,i)=>s+parseFloat(i.total_pendapatan),0);
  tbody.innerHTML=items.map((p,i)=>`<tr>
    <td style="text-align:center"><div style="width:24px;height:24px;border-radius:7px;background:${i===0?'var(--teal)':i===1?'var(--teal-mid)':'var(--surface-2)'};display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:${i===0?'#fff':'var(--text-3)'}">${i+1}</div></td>
    <td><p style="font-weight:600;color:var(--text-1)">${p.nama_produk}</p><p style="font-size:11.5px;color:var(--text-4);font-family:'Geist Mono',monospace">${p.kode_produk}</p></td>
    <td><span style="font-family:'Geist Mono',monospace;font-size:14px;font-weight:700;color:var(--teal)">${parseInt(p.total_terjual).toLocaleString('id-ID')}</span><span style="font-size:11px;color:var(--text-4)"> unit</span></td>
    <td style="font-family:'Geist Mono',monospace;font-weight:600;color:var(--text-1)">${rupiah(p.total_pendapatan)}</td>
    <td>
      <div style="display:flex;align-items:center;gap:8px;min-width:120px">
        <div style="flex:1;height:5px;background:var(--surface-2);border-radius:3px;overflow:hidden"><div style="height:5px;background:var(--teal);border-radius:3px;width:${((parseFloat(p.total_pendapatan)/totRev)*100).toFixed(1)}%"></div></div>
        <span style="font-size:12px;color:var(--text-3);min-width:36px;text-align:right">${((parseFloat(p.total_pendapatan)/totRev)*100).toFixed(1)}%</span>
      </div>
    </td>
  </tr>`).join('');
}

async function downloadFile(url, filename, preview = false, previewWindow = null) {
  const token = localStorage.getItem('pos_token');
  try {
    toast(preview ? 'Membuka preview PDF...' : 'Menyiapkan file...', 'info');
    const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
    if (!res.ok) {
      if (previewWindow) previewWindow.close();
      return toast('Gagal memuat file atau sesi habis.', 'error');
    }
    const blob = await res.blob();
    
    if (preview && previewWindow) {
      const urlBlob = window.URL.createObjectURL(new Blob([blob], {type: 'application/pdf'}));
      previewWindow.location.href = urlBlob;
      setTimeout(() => window.URL.revokeObjectURL(urlBlob), 10000);
    } else {
      const urlBlob = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = urlBlob; a.download = filename;
      document.body.appendChild(a); a.click(); a.remove();
      window.URL.revokeObjectURL(urlBlob);
      if (previewWindow) previewWindow.close();
    }
  } catch (e) { 
    if (previewWindow) previewWindow.close();
    toast('Error koneksi', 'error'); 
  }
}

function exportPdf() { 
  const previewWindow = window.open('', '_blank');
  downloadFile(`/api/laporan/export/pdf?dari=${periodeFrom}&sampai=${periodeTo}&tipe=penjualan`, `Laporan_Penjualan_${periodeFrom}_${periodeTo}.pdf`, true, previewWindow); 
}
function exportExcel() { 
  downloadFile(`/api/laporan/export/excel?dari=${periodeFrom}&sampai=${periodeTo}`, `Laporan_Pos_${periodeFrom}_${periodeTo}.xlsx`, false); 
}

// Init
setPeriod('month', document.querySelector('[data-period="month"]'));
</script>
@endpush