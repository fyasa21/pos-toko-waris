<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') · POS {{ config('pos.store_name', 'Toko Waris') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
  <script>
    window.POS_SESSION = @json([
      'token' => session('pos_token'),
      'user' => session('user'),
    ]);
  </script>
</head>
<body>

<div id="app">

  {{-- ════════════ SIDEBAR ════════════ --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" style="width:18px;height:18px">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
      </div>
      <div class="logo-text">
        <strong>{{ config('pos.store_name', 'Toko Waris') }}</strong>
        <span>Point of Sale</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <span class="nav-section-label">Menu</span>

      <a href="{{ route('dashboard') }}"
         class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="3" y="3" width="7" height="7" rx="1.5"/>
          <rect x="14" y="3" width="7" height="7" rx="1.5"/>
          <rect x="3" y="14" width="7" height="7" rx="1.5"/>
          <rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </svg>
        Dashboard
      </a>

      <a href="{{ route('kasir') }}"
         class="nav-link {{ request()->routeIs('kasir') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        Kasir / POS
      </a>

      <a href="{{ route('produk') }}"
         class="nav-link {{ request()->routeIs('produk') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
          <line x1="12" y1="22.08" x2="12" y2="12"/>
        </svg>
        Produk
      </a>

      <a href="{{ route('transaksi') }}"
         class="nav-link {{ request()->routeIs('transaksi') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
          <line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        Transaksi
        <span class="nav-badge" id="pending-badge" style="display:none">0</span>
      </a>

      @if(session('user.role') === 'pemilik')
      <span class="nav-section-label">Laporan</span>
      <a href="{{ route('laporan') }}"
         class="nav-link {{ request()->routeIs('laporan') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <line x1="18" y1="20" x2="18" y2="10"/>
          <line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6"  y1="20" x2="6"  y2="14"/>
          <path d="M2 20h20"/>
        </svg>
        Laporan
      </a>
      @endif
    </nav>

    <div class="sidebar-user">
      <div class="user-avatar">
        {{ strtoupper(substr(session('user.nama_lengkap', 'U'), 0, 1)) }}
      </div>
      <div class="user-info">
        <strong>{{ session('user.nama_lengkap', 'Pengguna') }}</strong>
        <span>{{ session('user.role', 'kasir') }}</span>
      </div>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn-logout" title="Keluar" onclick="localStorage.removeItem('pos_token'); localStorage.removeItem('pos_user');">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </form>
    </div>
  </aside>

  {{-- ════════════ MAIN ════════════ --}}
  <div class="main-wrapper">

    {{-- Topbar --}}
    <header class="topbar">
      <button class="topbar-btn" id="btn-mobile-menu" style="display:none"
              onclick="document.getElementById('sidebar').classList.toggle('open')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px">
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="6"  x2="21" y2="6"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>

      <div>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-sub">@yield('page-subtitle', '')</div>
      </div>

      <div class="topbar-right">
        <div class="topbar-clock">
          <span class="clock-dot"></span>
          <span id="clock-display">00:00:00</span>
        </div>

        <button class="topbar-btn" id="btn-notif" title="Notifikasi stok">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 01-3.46 0"/>
          </svg>
          <span class="notif-dot" id="notif-dot" style="display:none"></span>
        </button>
      </div>
    </header>

    {{-- Content --}}
    <main class="page-content page-enter">
      @yield('content')
    </main>

  </div>
</div>

{{-- Toast stack --}}
<div id="toast-stack"></div>

{{-- Notifikasi panel --}}
<div id="notif-panel" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:440px;position:fixed;top:64px;right:20px;margin:0;max-height:70vh">
    <div class="modal-header">
      <div><div class="card-title">Peringatan Stok</div></div>
      <button class="modal-close" onclick="closeModal('notif-panel')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div id="notif-content" style="overflow-y:auto;max-height:55vh"></div>
  </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
<script>
// ── Load notifikasi stok ──────────────────────────────────
async function loadNotif() {
  const res = await api('/stok/notifikasi');
  if (!res?.success) return;
  const low  = res.data.stok_minimal?.count || 0;
  const exp  = (res.data.kedaluwarsa?.akan_kedaluwarsa?.count || 0)
             + (res.data.kedaluwarsa?.sudah_kedaluwarsa?.count || 0);
  const total = low + exp;
  const dot  = document.getElementById('notif-dot');
  if (dot && total > 0) dot.style.display = '';

  // Pending badge
  const pending = await api('/transaksi?status=pending&per_page=1');
  const pb = document.getElementById('pending-badge');
  if (pb && pending?.pagination?.total > 0) {
    pb.textContent = pending.pagination.total;
    pb.style.display = '';
  }

  // Build panel
  const lowItems = res.data.stok_minimal?.items || [];
  const expItems = [...(res.data.kedaluwarsa?.akan_kedaluwarsa?.items || []),
                    ...(res.data.kedaluwarsa?.sudah_kedaluwarsa?.items || [])];
  const nc = document.getElementById('notif-content');
  if (!nc) return;

  if (!lowItems.length && !expItems.length) {
    nc.innerHTML = `<div class="empty-state"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg><p>Semua stok dalam kondisi baik</p></div>`;
    return;
  }

  const rows = [...lowItems.map(i => ({
    nama: i.produk?.nama_produk, kode: i.produk?.kode_produk,
    info: `Stok: ${i.jumlah_stok} (min ${i.stok_minimal})`, type: 'amber'
  })), ...expItems.map(i => ({
    nama: i.produk?.nama_produk, kode: i.produk?.kode_produk,
    info: `Exp: ${i.tanggal_kedaluwarsa}`, type: 'red'
  }))];

  nc.innerHTML = rows.map(r => `
    <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border)">
      <div style="width:8px;height:8px;border-radius:50%;background:var(--${r.type});flex-shrink:0"></div>
      <div style="flex:1;min-width:0">
        <p style="font-size:13px;font-weight:600;color:var(--text-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${r.nama || '—'}</p>
        <p style="font-size:12px;color:var(--text-3)">${r.kode} · ${r.info}</p>
      </div>
    </div>
  `).join('');
}

document.getElementById('btn-notif')?.addEventListener('click', () => openModal('notif-panel'));
document.addEventListener('DOMContentLoaded', () => { loadNotif(); });
</script>
@stack('scripts')
</body>
</html>
