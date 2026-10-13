@php
  $map = [
    'draft' => ['Draf', 'bg-slate-100 text-slate-700'],
    'scheduled' => ['Terjadwal', 'bg-amber-100 text-amber-800'],
    'queued' => ['Menunggu', 'bg-amber-100 text-amber-800'],
    'running' => ['Berjalan', 'bg-blue-100 text-blue-700'],
    'paused' => ['Dijeda', 'bg-red-100 text-red-700'],
    'done' => ['Selesai', 'bg-green-100 text-green-800'],
    'cancelled' => ['Dibatalkan', 'bg-red-200 text-red-700'],
    'pending' => ['Menunggu', 'bg-amber-100 text-amber-800'],
    'sending' => ['Mengirim', 'bg-blue-100 text-blue-700'],
    'sent' => ['Terkirim', 'bg-green-100 text-green-800'],
    'failed' => ['Gagal', 'bg-red-100 text-red-700'],
  ];
  [$label, $cls] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-700'];
  $pulse = in_array($status, ['running', 'sending'], true) ? ' animate-pulse' : '';
@endphp
<span class="inline-block rounded-full px-2.5 py-1 text-xs font-bold {{ $cls . $pulse }}">{{ $label }}</span>
