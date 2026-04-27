<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login · POS {{ config('pos.store_name', 'Toko Waris') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { font-size: 15px; -webkit-font-smoothing: antialiased; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f5f6f8; color: #0f1729; min-height: 100vh; }

    .login-wrap {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 480px;
    }

    /* Left panel — illustrated */
    .login-left {
      background: #0f1729;
      display: flex; flex-direction: column; justify-content: center; align-items: flex-start;
      padding: 60px 64px;
      position: relative; overflow: hidden;
    }

    /* Geometric pattern */
    .login-left::before {
      content: '';
      position: absolute; inset: 0;
      background-image:
        radial-gradient(circle at 20% 80%, rgba(13,148,136,.18) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(13,148,136,.1) 0%, transparent 40%);
    }

    /* Grid lines */
    .login-left::after {
      content: '';
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
      background-size: 40px 40px;
    }

    .left-content { position: relative; z-index: 2; }

    .left-logo {
      display: flex; align-items: center; gap: 12px;
      margin-bottom: 56px;
    }
    .left-logo-icon {
      width: 42px; height: 42px;
      background: linear-gradient(135deg, #0d9488, #0f766e);
      border-radius: 11px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 6px 20px rgba(13,148,136,.4);
    }
    .left-logo span { font-size: 16px; font-weight: 700; color: #fff; }

    .left-headline {
      font-size: 36px; font-weight: 800; color: #fff;
      line-height: 1.15; margin-bottom: 16px;
      letter-spacing: -.02em;
    }
    .left-headline em { color: #2dd4bf; font-style: normal; }

    .left-sub { font-size: 15px; color: rgba(255,255,255,.5); line-height: 1.7; max-width: 360px; }

    .feature-list { margin-top: 40px; display: flex; flex-direction: column; gap: 14px; }
    .feature-item { display: flex; align-items: center; gap: 12px; }
    .feature-dot {
      width: 28px; height: 28px; border-radius: 8px;
      background: rgba(13,148,136,.2); border: 1px solid rgba(13,148,136,.35);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .feature-dot svg { width: 14px; height: 14px; color: #2dd4bf; }
    .feature-item span { font-size: 13.5px; color: rgba(255,255,255,.6); }

    .left-version {
      position: absolute; bottom: 24px; left: 64px; z-index: 2;
      font-family: 'Geist Mono', monospace;
      font-size: 11px; color: rgba(255,255,255,.2);
    }

    /* Right panel — form */
    .login-right {
      background: #fff;
      display: flex; flex-direction: column; justify-content: center;
      padding: 56px 48px;
      border-left: 1px solid #e2e5ea;
    }

    .form-eyebrow {
      font-size: 11.5px; font-weight: 700; letter-spacing: .1em;
      text-transform: uppercase; color: #0d9488; margin-bottom: 10px;
    }
    .form-title { font-size: 26px; font-weight: 800; color: #0f1729; margin-bottom: 6px; }
    .form-sub   { font-size: 13.5px; color: #8898aa; margin-bottom: 32px; }

    /* Role tabs */
    .role-tabs {
      display: flex; gap: 6px;
      background: #f5f6f8;
      border: 1px solid #e2e5ea;
      padding: 6px;
      border-radius: 12px;
      margin-bottom: 24px;
    }
    .role-tab {
      flex: 1; padding: 11px 14px;
      background: transparent; border: none; cursor: pointer;
      font-size: 13.5px; font-weight: 600; font-family: inherit;
      color: #8898aa; display: flex; align-items: center; justify-content: center; gap: 8px;
      transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 8px;
    }
    .role-tab svg { width: 16px; height: 16px; transition: transform .2s; }
    .role-tab:hover:not(.active) { color: #4a5568; }
    .role-tab.active { 
      background: #fff; 
      color: #0d9488; 
      box-shadow: 0 4px 12px rgba(13,148,136,.1), 0 1px 3px rgba(15,23,41,.05);
      border: 1px solid rgba(13,148,136,.2);
    }
    .role-tab.active svg { transform: scale(1.1); }

    /* Fields */
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .field label {
      font-size: 12px; font-weight: 700; color: #4a5568;
      text-transform: uppercase; letter-spacing: .06em;
    }
    .field-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      color: #b0bec5; pointer-events: none; width: 16px; height: 16px;
    }
    .finput {
      width: 100%; padding: 11px 12px 11px 40px;
      background: #f5f6f8; border: 1.5px solid #e2e5ea;
      border-radius: 9px; font-size: 13.5px; font-family: inherit; color: #0f1729;
      outline: none; transition: all .15s;
    }
    .finput:focus { border-color: #0d9488; background: #fff; box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
    .finput::placeholder { color: #b0bec5; }

    .pw-toggle {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #b0bec5;
      display: flex; transition: color .15s; padding: 2px;
    }
    .pw-toggle:hover { color: #4a5568; }
    .pw-toggle svg { width: 15px; height: 15px; }

    /* Error */
    .error-box {
      display: none; align-items: center; gap: 9px;
      background: #fef2f2; border: 1px solid rgba(239,68,68,.2);
      border-radius: 9px; padding: 11px 14px;
      font-size: 13px; color: #b91c1c; margin-bottom: 16px;
    }
    .error-box svg { width: 15px; height: 15px; flex-shrink: 0; }

    /* Submit */
    .submit-btn {
      width: 100%; padding: 13px;
      background: linear-gradient(135deg, #0d9488, #0f766e);
      color: #fff; font-size: 14px; font-weight: 700; font-family: inherit;
      border: none; border-radius: 10px; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      transition: all .18s;
      box-shadow: 0 4px 14px rgba(13,148,136,.3);
    }
    .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(13,148,136,.4); }
    .submit-btn:active { transform: translateY(0); }
    .submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    .spinner {
      width: 16px; height: 16px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff; border-radius: 50%;
      animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .form-footer { margin-top: 24px; font-size: 12px; color: #b0bec5; text-align: center; }

    @media (max-width: 860px) {
      .login-wrap { grid-template-columns: 1fr; }
      .login-left { display: none; }
      .login-right { padding: 40px 28px; }
    }
  </style>
</head>
<body>

<div class="login-wrap">

  {{-- Left --}}
  <div class="login-left">
    <div class="left-content">
      <div class="left-logo">
        <div class="left-logo-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" style="width:20px;height:20px">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
        </div>
        <span>{{ config('pos.store_name', 'Toko Waris') }}</span>
      </div>

      <h1 class="left-headline">Kelola toko<br>dengan <em>lebih cerdas</em></h1>
      <p class="left-sub">Sistem kasir modern untuk operasional toko yang lebih cepat, akurat, dan terorganisir setiap hari.</p>

      <div class="feature-list">
        @foreach(['Transaksi real-time yang cepat & akurat', 'Laporan penjualan otomatis', 'Notifikasi stok & kedaluwarsa', 'Manajemen multi-kasir & pemilik'] as $f)
        <div class="feature-item">
          <div class="feature-dot">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <span>{{ $f }}</span>
        </div>
        @endforeach
      </div>
    </div>
    <div class="left-version">POS Toko Waris v1.0 · Laravel 11</div>
  </div>

  {{-- Right --}}
  <div class="login-right">
    <div class="form-eyebrow">Selamat datang kembali</div>
    <h2 class="form-title">Masuk ke sistem</h2>
    <p class="form-sub">Pilih peran dan masukkan kredensial Anda</p>

    <div id="error-box" class="error-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span id="error-msg">Username atau password salah.</span>
    </div>

    <div class="role-tabs">
      <button type="button" class="role-tab active" data-role="kasir" onclick="selectRole(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Kasir
      </button>
      <button type="button" class="role-tab" data-role="pemilik" onclick="selectRole(this)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Pemilik
      </button>
    </div>
    <input type="hidden" id="role-val" value="kasir">

    <form id="form-login" onsubmit="doLogin(event)">
      <div class="field">
        <label>Username / Email</label>
        <div class="field-wrap">
          <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input class="finput" type="text" id="f-user" placeholder="Masukkan username" autocomplete="username" required>
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="field-wrap">
          <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <input class="finput" type="password" id="f-pw" placeholder="••••••••" autocomplete="current-password" required>
          <button type="button" class="pw-toggle" onclick="togglePw()">
            <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="submit-btn" id="submit-btn">
        <span id="btn-label">Masuk ke Sistem</span>
        <span id="btn-spin" style="display:none" class="spinner"></span>
      </button>
    </form>

    <p class="form-footer">
      {{ config('pos.store_name', 'Toko Waris') }} &copy; {{ date('Y') }} · Sistem Point of Sale
    </p>
  </div>
</div>

<script>
function selectRole(btn) {
  document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('role-val').value = btn.dataset.role;
}

function togglePw() {
  const inp = document.getElementById('f-pw');
  const icon = document.getElementById('eye-icon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
  } else {
    inp.type = 'password';
    icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  }
}

async function doLogin(e) {
  e.preventDefault();
  const username = document.getElementById('f-user').value.trim();
  const password = document.getElementById('f-pw').value;
  const role     = document.getElementById('role-val').value;
  const errBox   = document.getElementById('error-box');
  const btnLabel = document.getElementById('btn-label');
  const btnSpin  = document.getElementById('btn-spin');
  const submitBtn= document.getElementById('submit-btn');

  errBox.style.display = 'none';
  btnLabel.style.display = 'none';
  btnSpin.style.display = '';
  submitBtn.disabled = true;

  try {
    const res = await fetch('/api/auth/login', {
    method: 'POST',
    credentials: 'same-origin', // WAJIB kalau pakai session Laravel
    headers: { 
        'Content-Type': 'application/json', 
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ username, password, role }),
});
    const data = await res.json();

    if (data.success) {
      localStorage.setItem('pos_token', data.data.token);
      localStorage.setItem('pos_user',  JSON.stringify(data.data.user));
      // Post to server to set session then redirect
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = document.createElement('form');
form.method = 'POST';
form.action = '/auth/session';

const fields = {
    token: data.data.token,
    _token: csrf
};

for (const [k, v] of Object.entries(fields)) {
    const i = document.createElement('input');
    i.type = 'hidden';
    i.name = k;
    i.value = v;
    form.appendChild(i);
}

document.body.appendChild(form);
form.submit();
    } else {
      document.getElementById('error-msg').textContent = data.message || 'Login gagal.';
      errBox.style.display = 'flex';
      btnLabel.style.display = ''; btnSpin.style.display = 'none'; submitBtn.disabled = false;
    }
  } catch(err) {
    document.getElementById('error-msg').textContent = 'Tidak dapat terhubung ke server.';
    errBox.style.display = 'flex';
    btnLabel.style.display = ''; btnSpin.style.display = 'none'; submitBtn.disabled = false;
  }
}
</script>
</body>
</html>
