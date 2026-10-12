@extends('layouts.kampus')
@section('title', $student->exists ? 'Edit Mahasiswa' : 'Tambah Mahasiswa')

@section('content')
<h1 class="text-2xl font-extrabold">{{ $student->exists ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' }}</h1>

<form method="POST" action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}"
      class="mt-6 max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
  @csrf
  @if ($student->exists) @method('PUT') @endif

  @php
    $fields = [
      'nim' => ['NIM', 'text'], 'nama' => ['Nama lengkap', 'text'], 'angkatan' => ['Angkatan', 'number'],
      'jurusan' => ['Jurusan', 'text'], 'no_wa' => ['No. WhatsApp', 'text'],
    ];
  @endphp
  @foreach ($fields as $name => [$label, $type])
    <label class="block text-sm font-semibold">{{ $label }}
      <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $student->$name) }}"
             class="mt-1 min-h-[44px] w-full rounded-lg border border-slate-300 px-3 font-medium">
      @error($name)<span class="text-xs font-semibold text-red-600">{{ $message }}</span>@enderror
    </label>
  @endforeach
  <p class="text-xs text-slate-500">Nomor boleh ditulis 0812..., 812..., atau +62812... dan otomatis disimpan sebagai 62812...</p>

  <input type="hidden" name="is_active" value="0">
  <label class="flex items-center gap-2 text-sm font-semibold">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $student->is_active))> Mahasiswa aktif
  </label>

  <div class="flex gap-3 pt-2">
    <button class="inline-flex min-h-[44px] items-center rounded-lg bg-emerald-700 px-5 text-sm font-bold text-white">Simpan</button>
    <a href="{{ route('students.index') }}" class="inline-flex min-h-[44px] items-center rounded-lg border border-slate-300 px-5 text-sm font-bold">Batal</a>
  </div>
</form>
@endsection
