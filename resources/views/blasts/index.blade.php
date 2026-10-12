@extends('layouts.kampus')
@section('title', 'Riwayat Blast')

@section('content')
@php $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold'; @endphp
<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-extrabold">Riwayat Blast</h1>
    <p class="text-sm text-slate-500 mt-1">Semua pengiriman yang pernah dibuat</p>
  </div>
  <a href="{{ route('blasts.create') }}" class="{{ $btn }} bg-emerald-700 text-white">+ Buat Blast Baru</a>
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-500">
          <th class="p-3">Judul</th><th class="p-3">Status</th><th class="p-3">Pesan</th><th class="p-3">Terkirim</th><th class="p-3">Gagal</th><th class="p-3">Dibuat</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($blasts as $b)
          <tr class="border-b border-slate-100">
            <td class="p-3 font-bold"><a href="{{ route('blasts.show', $b) }}" class="text-emerald-800 underline">{{ $b->judul }}</a></td>
            <td class="p-3">@include('blasts._status', ['status' => $b->status])</td>
            <td class="p-3">{{ $b->total }}</td>
            <td class="p-3">{{ $b->sent_count }}</td>
            <td class="p-3">{{ $b->failed_count }}</td>
            <td class="p-3">{{ $b->created_at->format('d M Y H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada blast. Klik "Buat Blast Baru".</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $blasts->links() }}</div>
</div>
@endsection
