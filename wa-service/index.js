require('dotenv').config();
const fs = require('fs');
const express = require('express');
const QRCode = require('qrcode');
const { Client, LocalAuth } = require('whatsapp-web.js');

const { WA_API_KEY, PORT = 3001, HOST = '127.0.0.1' } = process.env;
if (!WA_API_KEY) {
  console.error('WA_API_KEY belum diisi di .env');
  process.exit(1);
}

const AUTH_DIR = './.wwebjs_auth';

let state = 'starting'; // starting | qr | authenticated | connected | disconnected
let qr = null;
let phone = null;
let client = null;
let resetting = false;

// Jangan biarkan error dari library mematikan server
process.on('unhandledRejection', (e) => console.error('unhandledRejection:', e?.message ?? e));

function buildClient() {
  const c = new Client({
    authStrategy: new LocalAuth({ dataPath: AUTH_DIR }), // sesi tersimpan, QR cukup di-scan sekali
    puppeteer: { headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] },
  });

  c.on('qr', async (raw) => {
    state = 'qr';
    qr = await QRCode.toDataURL(raw, { width: 280, margin: 1 });
  });
  c.on('authenticated', () => { state = 'authenticated'; qr = null; });
  c.on('ready', () => {
    state = 'connected';
    qr = null;
    phone = c.info?.wid?.user ?? null;
    console.log('WhatsApp siap:', phone);
  });
  c.on('auth_failure', (m) => resetSession('auth_failure: ' + m, true));
  c.on('disconnected', (reason) => resetSession(String(reason)));
  c.on('change_state', (s) => console.log('change_state:', s));
  return c;
}

// Tutup browser, (opsional) hapus sesi lama, lalu mulai ulang supaya QR baru muncul
async function resetSession(reason, wipe = false) {
  if (resetting) return;
  resetting = true;
  console.log('Reset sesi:', reason);
  state = 'disconnected';
  qr = null;
  phone = null;
  try { await client.destroy(); } catch (_) {}
  if (wipe || /LOGOUT|UNPAIRED/i.test(reason)) {
    try {
      fs.rmSync(AUTH_DIR, { recursive: true, force: true, maxRetries: 5, retryDelay: 500 });
    } catch (e) {
      console.log('Hapus sesi gagal:', e.message);
    }
  }
  resetting = false;
  setTimeout(start, 2000);
}

async function start() {
  if (resetting) return;
  state = 'starting';
  client = buildClient();
  try {
    await client.initialize();
  } catch (e) {
    console.error('Gagal start:', e.message);
    state = 'disconnected';
    try { await client.destroy(); } catch (_) {}
    setTimeout(start, 10000);
  }
}

// Cek kesehatan tiap 5 detik: kalau di HP perangkat ditautkan dilepas, event sering tidak muncul
let bad = 0;
setInterval(async () => {
  if (state !== 'connected' || resetting) return;
  let s;
  try {
    s = await Promise.race([
      client.getState(),
      new Promise((_, rej) => setTimeout(() => rej(new Error('timeout')), 10000)),
    ]);
  } catch (e) {
    s = 'ERROR:' + e.message;
  }
  if (s === 'CONNECTED') { bad = 0; return; }
  console.log('Health check:', s);
  if (/UNPAIRED/.test(String(s)) || ++bad >= 3) {
    bad = 0;
    resetSession('state:' + s);
  }
}, 5000);

// 08123... / 8123... -> 628123...
const normalize = (n) => {
  let d = String(n).replace(/\D/g, '');
  if (d.startsWith('0')) d = '62' + d.slice(1);
  else if (d.startsWith('8')) d = '62' + d;
  return d;
};

const app = express();
app.use(express.json());
app.use((req, res, next) =>
  req.get('x-api-key') === WA_API_KEY ? next() : res.status(401).json({ ok: false, error: 'Unauthorized' })
);

app.get('/status', (_req, res) => res.json({ state, qr, phone }));

app.post('/send', async (req, res) => {
  if (state !== 'connected') return res.status(503).json({ ok: false, error: 'WhatsApp belum terhubung' });
  const { number, message } = req.body ?? {};
  if (!number || !message) return res.status(422).json({ ok: false, error: 'number dan message wajib diisi' });
  try {
    const id = await client.getNumberId(normalize(number));
    if (!id) return res.status(404).json({ ok: false, error: 'Nomor tidak terdaftar di WhatsApp' });
    const sent = await client.sendMessage(id._serialized, message);
    res.json({ ok: true, id: sent.id._serialized });
  } catch (e) {
    res.status(500).json({ ok: false, error: e.message });
  }
});

app.post('/logout', async (_req, res) => {
  try { await client.logout(); } catch (e) { console.log('logout():', e.message); }
  await resetSession('LOGOUT', true); // paksa: status berubah dan sesi lama dihapus
  res.json({ ok: true });
});

app.listen(PORT, HOST, () => console.log(`WA service di http://${HOST}:${PORT}`));
start();