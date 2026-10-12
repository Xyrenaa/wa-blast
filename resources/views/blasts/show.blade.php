@extends('layouts.kampus')
@section('title', $blast->judul)

@section('content')
@php $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold'; @endphp

<a href="{{ route('blasts.index') }}" class="text-sm font-bold text-emerald-700">&larr; Riwayat Blast</a>
<h1 class="mt-1 text-2xl font-extrabold">{{ $blast->judul }}</h1>

@if (session('success'))
  <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
@endif

<div id="progress" data-status="{{ $blast->status }}" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      @include('blasts._status', ['status' => $blast->status])
      <span class="text-xs text-slate-500">Dibuat {{ $blast->created_at->format('d M Y H:i') }} - jeda acak {{ $blast->delay_min }}-{{ $blast->delay_max }} detik</span>
    </div>
    <div class="flex gap-2">
      @if ($blast->status === 'paused')
        <form method="POST" action="{{ route('blasts.resume', $blast) }}">@csrf<button class="{{ $btn }} bg-emerald-700 text-white">Lanjutkan</button></form>
      @endif
      @if (in_array($blast->status, ['queued', 'running', 'paused']))
        <form method="POST" action="{{ route('blasts.cancel', $blast) }}" onsubmit="return confirm('Batalkan sisa pengiriman?')">@csrf<button class="{{ $btn }} bg-red-50 text-red-700">Batalkan</button></form>
      @endif
    </div>
  </div>

  @if ($blast->status === 'paused')
    <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">Pengiriman dijeda karena WhatsApp tidak terhubung atau terjadi masalah. Pastikan status Connected di Dashboard, lalu klik Lanjutkan.</div>
  @elseif (in_array($blast->status, ['queued', 'running']))
    <div class="mt-3 text-xs text-slate-500">Berjalan di latar belakang, halaman ini boleh ditutup. Pastikan terminal <b>php artisan queue:work</b> menyala.</div>
  @endif

  <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full bg-emerald-600" style="width: {{ $stats['percent'] }}%"></div></div>
  <div class="mt-2 text-xs text-slate-500">{{ $stats['percent'] }}% selesai
    @if (in_array($blast->status, ['queued', 'running']) && $stats['pending'] > 0) - sisa sekitar {{ $stats['eta_min'] }} menit @endif
  </div>

  <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
    <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs font-semibold text-slate-500">Total</div><div class="text-2xl font-extrabold">{{ $blast->total }}</div></div>
    <div class="rounded-xl bg-green-50 p-4"><div class="text-xs font-semibold text-green-800">Terkirim</div><div class="text-2xl font-extrabold text-green-800">{{ $stats['sent'] }}</div></div>
    <div class="rounded-xl bg-red-50 p-4"><div class="text-xs font-semibold text-red-700">Gagal</div><div class="text-2xl font-extrabold text-red-700">{{ $stats['failed'] }}</div></div>
    <div class="rounded-xl bg-amber-50 p-4"><div class="text-xs font-semibold text-amber-800">Menunggu</div><div class="text-2xl font-extrabold text-amber-800">{{ $stats['pending'] }}</div></div>
  </div>
</div>

<div id="log" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5">
  <div class="mb-3 flex flex-wrap gap-2 text-xs font-bold">
    @foreach (['' => 'Semua', 'sent' => 'Terkirim', 'failed' => 'Gagal', 'pending' => 'Menunggu'] as $key => $label)
      <a href="{{ route('blasts.show', array_filter(['blast' => $blast->id, 'status' => $key])) }}"
         class="rounded-full px-3 py-1.5 {{ (request('status') ?? '') === $key ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $label }}</a>
    @endforeach
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-500">
          <th class="p-3">Nama</th><th class="p-3">No. WhatsApp</th><th class="p-3">Status</th><th class="p-3">Keterangan</th><th class="p-3">Waktu kirim</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($messages as $m)
          <tr class="border-b border-slate-100">
            <td class="p-3 font-bold">{{ $m->nama }}</td>
            <td class="p-3">{{ $m->no_wa ?? '-' }}</td>
            <td class="p-3">@include('blasts._status', ['status' => $m->status])</td>
            <td class="p-3 text-slate-600">{{ $m->error ?? '' }}</td>
            <td class="p-3">{{ $m->sent_at?->format('H:i:s') ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="p-8 text-center text-slate-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $messages->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
// Selama blast berjalan, perbarui kartu progres dan log tiap 3 detik tanpa memuat ulang halaman
async function tick() {
  const html = await (await fetch(location.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})).text();
  const doc = new DOMParser().parseFromString(html, 'text/html');
  for (const id of ['progress', 'log']) {
    document.getElementById(id).innerHTML = doc.getElementById(id).innerHTML;
  }
  const st = doc.getElementById('progress').dataset.status;
  if (st === 'running' || st === 'queued') setTimeout(tick, 3000);
}
const first = document.getElementById('progress').dataset.status;
if (first === 'running' || first === 'queued') setTimeout(tick, 3000);
</script>
@endpush
