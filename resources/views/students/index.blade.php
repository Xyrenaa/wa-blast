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

<details class="group mt-4 rounded-2xl border border-slate-200 bg-white" {{ $errors->has('file') ? 'open' : '' }}>
  <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-5 [&::-webkit-details-marker]:hidden">
    <span class="flex items-center gap-3">
      <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
      </span>
      <span>
        <span class="block text-sm font-extrabold">Import dari Excel</span>
        <span class="block text-xs text-slate-500">Unggah data mahasiswa sekali, dipakai di semua blast</span>
      </span>
    </span>
    <svg class="h-5 w-5 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
  </summary>

  <form id="import-form" method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" class="border-t border-slate-100 p-5">
    @csrf
    <label id="drop" for="file" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 px-4 py-8 text-center transition hover:bg-emerald-50">
      <svg class="h-9 w-9 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
      <span id="file-title" class="max-w-full truncate text-sm font-bold">Tarik file ke sini atau klik untuk memilih</span>
      <span id="file-sub" class="text-xs text-slate-500">Excel (.xlsx, .xls) atau CSV, maksimal 5 MB</span>
    </label>
    <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" class="sr-only" required>

    <div class="mt-4 flex flex-wrap items-center gap-3">
      <button id="import-btn" disabled class="{{ $btn }} gap-2 bg-emerald-700 text-white disabled:cursor-not-allowed disabled:opacity-40">Upload dan Proses</button>
      <a href="{{ route('students.template') }}" class="{{ $btn }} gap-2 border border-slate-300">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
        Unduh template Excel
      </a>
    </div>
    <p class="mt-3 text-xs leading-relaxed text-slate-500">Tidak wajib memakai template: kolom dikenali otomatis dari judulnya (nim/npm, nama, prodi/jurusan, telepon/no_wa/hp, angkatan). Angkatan yang kosong diambil dari 2 digit awal NIM. NIM yang sudah ada diperbarui. Hanya sheet pertama yang dibaca.</p>
  </form>
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
  <button class="{{ $btn }} border border-emerald-200 bg-emerald-50 text-emerald-800">Terapkan</button>
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
              <form method="POST" action="{{ route('students.destroy', $s) }}" class="inline" data-confirm="Data mahasiswa ini akan dihapus permanen." data-confirm-title="Hapus mahasiswa?" data-confirm-ok="Hapus" data-confirm-danger>
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
  <div class="mt-4">{{ $students->links('pagination.kampus') }}</div>
</div>
@endsection

@push('scripts')
<script>
const fileInput = document.getElementById('file'), drop = document.getElementById('drop');
const fTitle = document.getElementById('file-title'), fSub = document.getElementById('file-sub');
const importBtn = document.getElementById('import-btn'), importForm = document.getElementById('import-form');
const idle = {t: fTitle.textContent, s: fSub.textContent};
const MAX = 5 * 1048576;

function showFile() {
  const f = fileInput.files[0];
  fSub.classList.remove('text-red-600', 'font-bold');
  if (!f) { fTitle.textContent = idle.t; fSub.textContent = idle.s; importBtn.disabled = true; return; }
  fTitle.textContent = f.name;
  if (f.size > MAX) {
    fSub.textContent = 'File terlalu besar (maksimal 5 MB)';
    fSub.classList.add('text-red-600', 'font-bold');
    importBtn.disabled = true;
    return;
  }
  fSub.textContent = (f.size < 1048576 ? Math.max(1, Math.round(f.size / 1024)) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB') + ' - siap diupload';
  importBtn.disabled = false;
}

fileInput.addEventListener('change', showFile);
['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => {
  e.preventDefault(); drop.classList.add('bg-emerald-100', 'border-emerald-500');
}));
['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => {
  e.preventDefault(); drop.classList.remove('bg-emerald-100', 'border-emerald-500');
}));
drop.addEventListener('drop', e => {
  if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; showFile(); }
});
importForm.addEventListener('submit', () => {
  importBtn.disabled = true;
  importBtn.innerHTML = '<svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg> Memproses...';
});
</script>
@endpush
