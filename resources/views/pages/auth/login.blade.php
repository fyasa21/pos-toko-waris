<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('pos.store_name', 'Toko Waris') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #080b10; color: #e6edf3; min-height: 100vh; overflow: hidden; }
        .font-display { font-family: 'Syne', sans-serif; }

        /* Animated grid background */
        .grid-bg {
            position: fixed; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(245,158,11,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245,158,11,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black, transparent);
        }

        /* Glow orbs */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(80px); pointer-events: none; z-index: 0;
        }
        .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(245,158,11,0.08), transparent); top: -100px; left: -100px; }
        .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(56,189,248,0.05), transparent); bottom: -50px; right: -50px; }
        .orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(245,158,11,0.04), transparent); top: 50%; left: 50%; transform: translate(-50%,-50%); animation: breathe 4s ease-in-out infinite; }
        @keyframes breathe { 0%,100%{opacity:.5} 50%{opacity:1} }

        /* Card */
        .login-card {
            position: relative; z-index: 10;
            background: rgba(13,17,23,0.9);
            border: 1px solid rgba(48,54,61,0.8);
            border-radius: 20px;
            padding: 44px 44px;
            width: 100%; max-width: 440px;
            backdrop-filter: blur(20px);
            box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(245,158,11,0.04);
            animation: slideUp 0.5s ease both;
        }
        @keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }

        /* Input */
        .login-input {
            width: 100%; background: #0d1117; border: 1px solid #30363d; color: #e6edf3;
            border-radius: 10px; padding: 12px 16px; font-size: 14px; font-family: 'DM Sans', sans-serif;
            outline: none; transition: all 0.2s; -webkit-appearance: none;
        }
        .login-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
        .login-input::placeholder { color: #484f58; }

        /* Input group */
        .input-group { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #484f58; pointer-events: none; width: 16px; height: 16px; }
        .input-group .login-input { padding-left: 42px; }

        /* Role selector */
        .role-btn {
            flex: 1; padding: 10px 14px; border-radius: 8px; border: 1px solid #30363d;
            background: transparent; color: #8b949e; font-size: 13px; font-weight: 500; cursor: pointer;
            transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 7px;
            font-family: 'DM Sans', sans-serif;
        }
        .role-btn.active {
            background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(245,158,11,0.06));
            border-color: rgba(245,158,11,0.4); color: #fbbf24;
        }
        .role-btn:hover:not(.active) { border-color: #484f58; color: #c9d1d9; background: #161b22; }

        /* Submit btn */
        .submit-btn {
            width: 100%; padding: 13px; border-radius: 10px; border: none; cursor: pointer;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #080b10; font-weight: 700; font-size: 14px; font-family: 'DM Sans', sans-serif;
            letter-spacing: .02em; transition: all 0.2s; position: relative; overflow: hidden;
            box-shadow: 0 4px 16px rgba(245,158,11,0.3);
        }
        .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(245,158,11,0.4); filter: brightness(1.06); }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* Error */
        .error-box {
            background: rgba(251,113,133,0.07); border: 1px solid rgba(251,113,133,0.2);
            border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #fb7185;
            display: flex; align-items: center; gap: 9px; animation: slideUp 0.3s ease;
        }

        /* Divider lines */
        .metrics-row { display: flex; gap: 1px; margin-top: 32px; padding-top: 24px; border-top: 1px solid #161b22; }
        .metric { flex: 1; text-align: center; }
        .metric-val { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 700; color: #fbbf24; }
        .metric-label { font-size: 10px; color: #484f58; margin-top: 2px; text-transform: uppercase; letter-spacing: .06em; }

        ::-webkit-scrollbar { display: none; }
    </style>
</head>
<body>

{{-- Background --}}
<div class="grid-bg"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

{{-- Floating particles --}}
<canvas id="particles" style="position:fixed;inset:0;z-index:1;pointer-events:none;opacity:0.4"></canvas>

{{-- Center layout --}}
<div style="position:relative;z-index:10;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">

    <div class="login-card">

        {{-- Logo --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:32px;">
            <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(245,158,11,0.3)">
                <svg viewBox="0 0 24 24" fill="none" stroke="#080b10" stroke-width="2.5" style="width:22px;height:22px">
                    <path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path d="M3 9l2.45-4.9A2 2 0 017.24 3h9.52a2 2 0 011.8 1.1L21 9"/>
                    <path d="M12 3v6"/>
                </svg>
            </div>
            <div>
                <h1 class="font-display" style="font-size:20px;font-weight:800;color:#fff;line-height:1.1">{{ config('pos.store_name', 'Toko Waris') }}</h1>
                <p style="font-size:12px;color:#484f58;margin-top:2px">Sistem Point of Sale</p>
            </div>
        </div>

        <p class="font-display" style="font-size:22px;font-weight:700;color:#fff;margin-bottom:4px">Selamat datang kembali</p>
        <p style="font-size:13px;color:#8b949e;margin-bottom:28px">Masuk ke sistem kasir Anda</p>

        {{-- Error --}}
        <div id="error-box" class="error-box" style="display:none;margin-bottom:18px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="error-msg">Username atau password salah.</span>
        </div>

        {{-- Form --}}
        <form id="login-form" style="display:flex;flex-direction:column;gap:16px">

            {{-- Role selector --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:8px">Masuk sebagai</label>
                <div style="display:flex;gap:8px" id="role-selector">
                    <button type="button" class="role-btn active" data-role="kasir">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Kasir
                    </button>
                    <button type="button" class="role-btn" data-role="pemilik">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Pemilik
                    </button>
                </div>
                <input type="hidden" id="selected-role" value="kasir">
            </div>

            {{-- Username --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:8px">Username / Email</label>
                <div class="input-group">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" class="login-input" id="username" placeholder="Masukkan username" autocomplete="username" required>
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label style="font-size:12px;font-weight:600;color:#8b949e;text-transform:uppercase;letter-spacing:.07em;display:block;margin-bottom:8px">Password</label>
                <div class="input-group" style="position:relative">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <input type="password" class="login-input" id="password" placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" id="toggle-pw" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#484f58;background:none;border:none;cursor:pointer;display:flex">
                        <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <div style="margin-top:4px">
                <button type="submit" class="submit-btn" id="submit-btn">
                    <span id="btn-text">Masuk ke Sistem</span>
                    <span id="btn-loader" style="display:none;align-items:center;gap:8px;justify-content:center">
                        <span style="width:16px;height:16px;border:2px solid rgba(8,11,16,0.3);border-top-color:#080b10;border-radius:50%;animation:spin 0.7s linear infinite;display:inline-block"></span>
                        Memverifikasi...
                    </span>
                </button>
            </div>
        </form>

        {{-- Metrics --}}
        <div class="metrics-row">
            <div class="metric">
                <div class="metric-val">POS</div>
                <div class="metric-label">System</div>
            </div>
            <div style="width:1px;background:#161b22"></div>
            <div class="metric">
                <div class="metric-val">v1.0</div>
                <div class="metric-label">Version</div>
            </div>
            <div style="width:1px;background:#161b22"></div>
            <div class="metric">
                <div class="metric-val">24/7</div>
                <div class="metric-label">Uptime</div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
// ── Role selector ─────────────────────────────────────────
document.querySelectorAll('.role-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('selected-role').value = btn.dataset.role;
    });
});

// ── Toggle password ───────────────────────────────────────
document.getElementById('toggle-pw').addEventListener('click', () => {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
        pw.type = 'password';
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
});

// ── Login form ────────────────────────────────────────────
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const role     = document.getElementById('selected-role').value;
    const errorBox = document.getElementById('error-box');
    const btnText  = document.getElementById('btn-text');
    const btnLoader = document.getElementById('btn-loader');
    const submitBtn = document.getElementById('submit-btn');

    errorBox.style.display = 'none';
    btnText.style.display = 'none';
    btnLoader.style.display = 'flex';
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

        if (data.success) {
            localStorage.setItem('pos_token', data.data.token);
            localStorage.setItem('pos_user',  JSON.stringify(data.data.user));
            window.location.href = '/dashboard';
}
        
        } else {
            document.getElementById('error-msg').textContent = data.message || 'Login gagal.';
            errorBox.style.display = 'flex';
            btnText.style.display = '';
            btnLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    } catch(err) {
        document.getElementById('error-msg').textContent = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.';
        errorBox.style.display = 'flex';
        btnText.style.display = '';
        btnLoader.style.display = 'none';
        submitBtn.disabled = false;
    }
});

// ── Particle canvas ───────────────────────────────────────
const canvas = document.getElementById('particles');
const ctx = canvas.getContext('2d');
let W, H, particles = [];
function resize() { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
resize(); window.addEventListener('resize', resize);
for (let i = 0; i < 40; i++) {
    particles.push({ x: Math.random()*1920, y: Math.random()*1080, vx:(Math.random()-.5)*.3, vy:(Math.random()-.5)*.3, r:Math.random()*1.5+.5, o:Math.random()*.4+.1 });
}
function drawParticles() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
        ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
        ctx.fillStyle = `rgba(245,158,11,${p.o})`; ctx.fill();
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0) p.x = W; if (p.x > W) p.x = 0;
        if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;
    });
    requestAnimationFrame(drawParticles);
}
drawParticles();
</script>
</body>
</html>
