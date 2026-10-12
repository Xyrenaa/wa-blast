@extends('layouts.kampus')
@section('title', 'Data Mahasiswa')

@section('content')
@php
  $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium';
  $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-extrabold">Data Mahasiswa</h1>
    <p class="text-sm text-slate-500 mt-1">Database penerima: cukup diunggah sekali, dipakai di semua blast</p>
  </div>
  <a href="{{ route('students.create') }}" class="{{ $btn }} bg-emerald-700 text-white">+ Tambah Mahasiswa</a>
</div>

@if (session('success'))
  <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
    @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif
@if (session('import'))
  @php $imp = session('import'); $ok = $imp['done'] > 0; @endphp
  <div class="mt-4 rounded-xl border px-4 py-3 text-sm {{ $ok ? 'border-green-200 bg-green-50 text-green-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
    <div class="font-bold">{{ $imp['done'] }} mahasiswa berhasil diproses.</div>
    @if (count($imp['mapping']))
      <div class="mt-2 text-xs font-semibold opacity-80">Kolom yang terbaca: {{ implode(' | ', $imp['mapping']) }}</div>
    @endif
    @if (count($imp['skipped']))
      <div class="mt-2 font-semibold">{{ count($imp['skipped']) }} baris dilewati:</div>
      <ul class="list-disc pl-5">
        @foreach (array_slice($imp['skipped'], 0, 10) as $msg)<li>{{ $msg }}</li>@endforeach
      </ul>
    @endif
  </div>
@endif

<details class="mt-4 rounded-2xl border border-slate-200 bg-white p-5" {{ $errors->has('file') ? 'open' : '' }}>
  <summary class="cursor-pointer text-sm font-bold">Import dari Excel</summary>
  <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-center gap-3">
    @csrf
    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="text-sm">
    <button class="{{ $btn }} bg-emerald-700 text-white">Upload dan Proses</button>
    <a href="{{ route('students.template') }}" class="text-sm font-bold text-emerald-700">Unduh template Excel</a>
  </form>
  <p class="mt-3 text-xs text-slate-500">Tidak wajib memakai template. Kolom dikenali otomatis dari judulnya: nim/npm, nama, prodi/jurusan, telepon/no_wa/hp, angkatan. Kalau tidak ada kolom angkatan, diambil dari 2 digit awal NIM. NIM yang sudah ada akan diperbarui. Hanya sheet pertama yang dibaca.</p>
</details>

<form method="GET" class="mt-4 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-5">
  <label class="min-w-[200px] flex-1 text-xs font-semibold text-slate-600">Cari
    <input name="q" value="{{ request('q') }}" placeholder="Nama atau NIM" class="{{ $in }}">
  </label>
  <label class="min-w-[130px] text-xs font-semibold text-slate-600">Angkatan
    <select name="angkatan" class="{{ $in }}"><option value="">Semua</option>
      @foreach ($angkatanList as $a)<option value="{{ $a }}" @selected(request('angkatan') == $a)>{{ $a }}</option>@endforeach
    </select>
  </label>
  <label class="min-w-[170px] text-xs font-semibold text-slate-600">Jurusan
    <select name="jurusan" class="{{ $in }}"><option value="">Semua</option>
      @foreach ($jurusanList as $j)<option value="{{ $j }}" @selected(request('jurusan') == $j)>{{ $j }}</option>@endforeach
    </select>
  </label>
  <label class="min-w-[130px] text-xs font-semibold text-slate-600">Status
    <select name="status" class="{{ $in }}">
      <option value="">Semua</option>
      <option value="1" @selected(request('status') === '1')>Aktif</option>
      <option value="0" @selected(request('status') === '0')>Nonaktif</option>
    </select>
  </label>
  <button class="{{ $btn }} bg-slate-800 text-white">Terapkan</button>
</form>

<div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
  <div class="mb-3 flex flex-wrap gap-2 text-xs font-bold">
    <span class="rounded-full bg-green-100 px-3 py-1.5 text-green-800">{{ number_format($total, 0, ',', '.') }} mahasiswa</span>
    @if ($invalid)
      <a href="{{ route('students.index', ['wa' => 'invalid']) }}" class="rounded-full bg-red-100 px-3 py-1.5 text-red-700">{{ $invalid }} nomor perlu diperbaiki</a>
    @endif
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-500">
          <th class="p-3">Nama</th><th class="p-3">NIM</th><th class="p-3">Angkatan</th><th class="p-3">Jurusan</th><th class="p-3">No. WhatsApp</th><th class="p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($students as $s)
          <tr class="border-b border-slate-100">
            <td class="p-3 font-bold">{{ $s->nama }}
              @unless ($s->is_active)<span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">Nonaktif</span>@endunless
            </td>
            <td class="p-3">{{ $s->nim }}</td>
            <td class="p-3">{{ $s->angkatan }}</td>
            <td class="p-3">{{ $s->jurusan }}</td>
            <td class="p-3">{{ $s->no_wa ?? '-' }}
              @unless ($s->wa_valid)<span class="ml-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">Nomor tidak valid</span>@endunless
            </td>
            <td class="whitespace-nowrap p-3">
              <a href="{{ route('students.edit', $s) }}" class="{{ $btn }} border border-slate-300">Edit</a>
              <form method="POST" action="{{ route('students.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus data mahasiswa ini?')">
                @csrf @method('DELETE')
                <button class="{{ $btn }} bg-red-50 text-red-700">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada data. Import dari Excel atau tambah manual.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $students->links() }}</div>
</div>
@endsection
