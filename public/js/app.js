/* ============================================================
 POS TOKO WARIS — GLOBAL UTILITIES
 ============================================================ */
'use strict';
// ── Config ──────────────────────────────────────────────────
const POS = {
 API_BASE : '/api',
 getToken : () => window.POS_SESSION?.token || localStorage.getItem('pos_token'),
 getUser : () => {
 if (window.POS_SESSION?.user) return window.POS_SESSION.user;
 try { return JSON.parse(localStorage.getItem('pos_user') || 'null'); }
 catch { return null; }
 },
 // Baca CSRF token dari meta tag di <head> layout
 getCsrf : () => document.querySelector('meta[name="csrf-token"]')?.content || '',
};
let isHandlingUnauthorized = false;

async function handleUnauthorized() {
 if (isHandlingUnauthorized) return;

 isHandlingUnauthorized = true;

 try {
  localStorage.removeItem('pos_token');
  localStorage.removeItem('pos_user');
 } catch (_) {}

 try {
  await fetch('/logout', {
   method: 'POST',
   credentials: 'same-origin',
   headers: {
    'Accept': 'application/json',
    ...(POS.getCsrf() ? { 'X-CSRF-TOKEN': POS.getCsrf() } : {}),
   },
  });
 } catch (_) {}

 window.location.replace('/login');
}
// ── API fetch helper ─────────────────────────────────────────
// Otomatis menyertakan:
// - Authorization: Bearer <token> → untuk Sanctum
// - X-CSRF-TOKEN → untuk semua request mutasi (POST/PUT/PATCH/DELETE)
async function api(path, opts = {}) {
 const token = POS.getToken();
 const csrf = POS.getCsrf();
 const method = (opts.method || 'GET').toUpperCase();
 const headers = {
 'Content-Type': 'application/json',
 'Accept' : 'application/json',
 ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
 // CSRF wajib untuk semua method yang mengubah data
 ...(['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) && csrf
 ? { 'X-CSRF-TOKEN': csrf }
 : {}),
 // Header custom dari pemanggil (override terakhir)
 ...opts.headers,
 };
 try {
 const res = await fetch(POS.API_BASE + path, { ...opts, method, headers, credentials: 'same-origin' });
 if (res.status === 401) {
 await handleUnauthorized();
 return null;
 }
 // Response kosong (HTTP 204 No Content)
 if (res.status === 204 || res.headers.get('content-length') === '0') {
 return { success: true };
 }
 return await res.json();
 } catch (err) {
 console.error('[API Error]', path, err);
 toast('Koneksi ke server gagal.', 'error');
 return null;
 }
}
const apiFetch = api;
// ── Download file (PDF / Excel) via fetch + Blob ─────────────
// Menggunakan fetch agar Bearer token bisa dikirim.
// window.open() TIDAK bisa mengirim Authorization header,
// sehingga endpoint API akan menolak dengan 401.
async function apiDownload(path, namaFile) {
 const token = POS.getToken();
 const csrf = POS.getCsrf();
 toast('Menyiapkan file, harap tunggu…', 'info');
 try {
 const res = await fetch(POS.API_BASE + path, {
 method : 'GET',
 credentials: 'same-origin',
 headers: {
 'Accept': '*/*',
 ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
 ...(csrf ? { 'X-CSRF-TOKEN' : csrf } : {}),
 },
 });
 if (res.status === 401) {
 await handleUnauthorized();
 return;
 }
 if (!res.ok) {
 const errJson = await res.json().catch(() => null);
 toast(errJson?.message || 'Gagal mengunduh file. Coba lagi.', 'error');
 return;
 }
 // Konversi response ke Blob lalu trigger klik link download
 const blob = await res.blob();
 const objectUrl = URL.createObjectURL(blob);
 const anchor = document.createElement('a');
 anchor.href = objectUrl;
 anchor.download = namaFile;
 document.body.appendChild(anchor);
 anchor.click();
 anchor.remove();
 URL.revokeObjectURL(objectUrl);
 toast(`${namaFile} berhasil diunduh!`, 'success');
 } catch (err) {
 console.error('[Download Error]', path, err);
 toast('Gagal mengunduh file.', 'error');
 }
}
// ── Format rupiah ────────────────────────────────────────────
function rupiah(n, withPrefix = true) {
 const val = parseFloat(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
 return withPrefix ? 'Rp\u00a0' + val : val;
}
// ── Format tanggal ───────────────────────────────────────────
function tanggal(iso, withTime = false) {
 if (!iso) return '—';
 const d = new Date(iso);
 const opts = { day: '2-digit', month: 'short', year: 'numeric' };
 if (withTime) { opts.hour = '2-digit'; opts.minute = '2-digit'; }
 return d.toLocaleDateString('id-ID', opts);
}
// ── Toast notification ───────────────────────────────────────
function toast(msg, type = 'success') {
 const icons = {
  success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
    <polyline points="20 6 9 17 4 12"/>
  </svg>`,
  error: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
    <line x1="18" y1="6" x2="6" y2="18"/>
    <line x1="6" y1="6" x2="18" y2="18"/>
  </svg>`,
  info: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
    <circle cx="12" cy="12" r="1"/>
    <path d="M12 8v1M12 12v4"/>
  </svg>`
};
 const stack = document.getElementById('toast-stack');
 if (!stack) return;
 const el = document.createElement('div');
 el.className = `toast ${type}`;
el.innerHTML = `<div class="toast-icon">${icons[type] || icons.info}</div><span>${msg}</span>`;
 stack.appendChild(el);
 setTimeout(() => {
 el.classList.add('out');
 setTimeout(() => el.remove(), 220);
 }, 3500);
}
const showToast = toast;
// ── Clock ────────────────────────────────────────────────────
function initClock(elId = 'clock-display') {
 const el = document.getElementById(elId);
 if (!el) return;
 const tick = () => {
 el.textContent = new Date().toLocaleTimeString('id-ID', {
 hour: '2-digit', minute: '2-digit', second: '2-digit',
 });
 };
 tick();
 setInterval(tick, 1000);
}
// ── Debounce ─────────────────────────────────────────────────
function debounce(fn, ms = 320) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), ms);
  };
}

// ── Confirm dialog ───────────────────────────────────────────
function confirmDialog(msg) {
  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';

    overlay.innerHTML = `
      <div class="modal" style="max-width:380px">
        <div class="modal-body" style="text-align:center;padding:28px 24px">
          <div style="width:44px;height:44px;background:#fef2f2;border-radius:12px;
                      display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"
                 style="width:22px;height:22px">
              <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>

          <p style="font-weight:700;font-size:15px;margin-bottom:8px;color:#0f1729">
            Konfirmasi Tindakan
          </p>

          <p style="font-size:13.5px;color:#4a5568;line-height:1.6">
            ${msg}
          </p>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" id="cd-no">Batal</button>
          <button class="btn btn-danger" id="cd-yes">Ya, Lanjutkan</button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);

    overlay.querySelector('#cd-no').onclick = () => {
      overlay.remove();
      resolve(false);
    };

    overlay.querySelector('#cd-yes').onclick = () => {
      overlay.remove();
      resolve(true);
    };

    overlay.addEventListener('click', e => {
      if (e.target === overlay) {
        overlay.remove();
        resolve(false);
      }
    });
  });
}

// ── Modal helpers ─────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = 'flex';
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.style.display = 'none';
}

// ── Button loading state ──────────────────────────────────────
function setLoading(btn, loading) {
  if (loading) {
    btn._text = btn.innerHTML;
    btn.innerHTML = `<span class="spinner"></span> Memproses...`;
    btn.disabled = true;
  } else {
    btn.innerHTML = btn._text || 'Simpan';
    btn.disabled = false;
  }
}

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initClock();

  document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => {
      if (e.target === el) el.style.display = 'none';
    });
  });
});
