@extends('layouts.kampus')
@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-extrabold">Dashboard</h1>
<p class="text-sm text-slate-500 mt-1">Ringkasan pengiriman pesan WhatsApp kampus</p>

<div class="mt-6 bg-white border border-slate-200 rounded-2xl p-5 flex flex-wrap items-center gap-6">
  <div class="flex items-center gap-4">
    <span id="wa-dot" class="w-4 h-4 rounded-full bg-slate-300"></span>
    <div>
      <div class="text-xs font-semibold text-slate-500">Status WhatsApp</div>
      <div id="wa-label" class="text-xl font-extrabold">Memeriksa...</div>
      <div id="wa-desc" class="text-sm text-slate-500"></div>
    </div>
  </div>
  <div class="ml-auto flex items-center gap-4">
    <img id="wa-qr" class="hidden w-40 h-40 rounded-xl border border-slate-200" alt="QR Code WhatsApp">
    <button id="wa-logout" class="hidden min-h-[44px] px-4 rounded-lg border border-slate-300 text-sm font-bold">Putuskan Koneksi</button>
  </div>
</div>

<div class="mt-5 grid gap-4 sm:grid-cols-3">
  <div class="lift rounded-2xl border border-slate-200 bg-white p-5"><div class="text-xs font-semibold text-slate-500">Pesan terkirim bulan ini</div><div class="my-1 text-3xl font-extrabold" data-count="{{ $sentThisMonth }}">{{ number_format($sentThisMonth, 0, ',', '.') }}</div></div>
  <div class="lift rounded-2xl border border-slate-200 bg-white p-5"><div class="text-xs font-semibold text-slate-500">Pesan gagal bulan ini</div><div class="my-1 text-3xl font-extrabold text-red-700" data-count="{{ $failedThisMonth }}">{{ number_format($failedThisMonth, 0, ',', '.') }}</div></div>
  <div class="lift rounded-2xl border border-slate-200 bg-white p-5"><div class="text-xs font-semibold text-slate-500">Total Mahasiswa</div><div class="my-1 text-3xl font-extrabold" data-count="{{ $totalStudents }}">{{ number_format($totalStudents, 0, ',', '.') }}</div></div>
</div>

<div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
  <div class="mb-2 flex items-center justify-between">
    <h2 class="text-base font-extrabold">Riwayat Blast Terakhir</h2>
    <a href="{{ route('blasts.index') }}" class="text-sm font-bold text-emerald-700">Lihat semua</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead><tr class="border-b border-slate-200 text-xs text-slate-500"><th class="p-3">Judul</th><th class="p-3">Pesan</th><th class="p-3">Gagal</th><th class="p-3">Status</th><th class="p-3">Waktu</th></tr></thead>
      <tbody>
        @forelse ($recent as $b)
          <tr class="border-b border-slate-100">
            <td class="p-3 font-bold"><a href="{{ route('blasts.show', $b) }}" class="text-emerald-800 underline">{{ $b->judul }}</a></td>
            <td class="p-3">{{ $b->total }}</td>
            <td class="p-3">{{ $b->failed_count }}</td>
            <td class="p-3">@include('blasts._status', ['status' => $b->status])</td>
            <td class="p-3">{{ ($b->scheduled_at ?? $b->created_at)->format('d M Y H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada blast.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
const ui = {
  connected:     {dot: 'bg-green-500', label: 'Connected',      desc: s => `Nomor ${s.phone ?? '-'} siap mengirim pesan`},
  qr:            {dot: 'bg-amber-400', label: 'Scan QR Code',   desc: () => 'WhatsApp di HP > Perangkat tertaut > Tautkan perangkat'},
  authenticated: {dot: 'bg-amber-400', label: 'Menghubungkan...', desc: () => 'Tunggu sebentar'},
  starting:      {dot: 'bg-slate-300', label: 'Memulai...',     desc: () => 'WA service sedang menyala'},
  disconnected:  {dot: 'bg-red-500',   label: 'Terputus',       desc: () => 'Menunggu QR Code baru'},
  offline:       {dot: 'bg-red-500',   label: 'WA service mati', desc: () => 'Jalankan "node index.js" di folder wa-service'},
};
const $ = id => document.getElementById(id);

async function refresh() {
  let s;
  try { s = await (await fetch('{{ route('wa.status') }}', {headers: {Accept: 'application/json'}})).json(); }
  catch { s = {state: 'offline'}; }
  const u = ui[s.state] ?? ui.offline;
  $('wa-dot').className = 'w-4 h-4 rounded-full ' + u.dot + (s.state === 'connected' ? ' animate-pulse' : '');
  $('wa-label').textContent = u.label;
  $('wa-desc').textContent = u.desc(s);
  const qr = $('wa-qr');
  if (s.state === 'qr' && s.qr) { qr.src = s.qr; qr.classList.remove('hidden'); } else { qr.classList.add('hidden'); }
  $('wa-logout').classList.toggle('hidden', s.state !== 'connected');
}

$('wa-logout').addEventListener('click', async () => {
  if (!(await confirmDialog('Koneksi WhatsApp akan diputus, dan kamu perlu scan QR lagi untuk menghubungkannya.', {title: 'Putuskan koneksi?', okText: 'Putuskan', danger: true}))) return;
  await fetch('{{ route('wa.logout') }}', {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}});
  refresh();
});

refresh();
setInterval(refresh, 3000);
</script>
@endpush
