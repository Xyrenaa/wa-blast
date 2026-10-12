@extends('layouts.kampus')
@section('title', 'Template Pesan')

@section('content')
@php
  $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium';
  $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-extrabold">Template Pesan</h1>
    <p class="text-sm text-slate-500 mt-1">Simpan pesan yang sering dipakai agar staf tidak mengetik dari nol</p>
  </div>
</div>

@if (session('success'))
  <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
    @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif

<div class="mt-6 grid items-start gap-5 lg:grid-cols-2">
  {{-- Daftar template --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5">
    <form method="GET" class="mb-4">
      <label class="text-xs font-semibold text-slate-600">Cari template
        <input name="q" value="{{ request('q') }}" placeholder="Ketik nama atau isi, lalu Enter" class="{{ $in }}">
      </label>
    </form>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-200 text-xs text-slate-500"><th class="p-3">Nama</th><th class="p-3">Variabel</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($templates as $t)
            <tr class="border-b border-slate-100 {{ $editing->id === $t->id ? 'bg-emerald-50' : '' }}">
              <td class="p-3">
                <div class="font-bold">{{ $t->nama }}</div>
                <div class="text-xs text-slate-500">{{ $t->kategori ?? 'Tanpa kategori' }} - diubah {{ $t->updated_at->diffForHumans() }}</div>
              </td>
              <td class="p-3">
                @foreach (\App\Support\TemplateRenderer::variables($t->isi) as $v)
                  <span class="mb-1 mr-1 inline-block rounded-full px-2.5 py-1 text-xs font-bold {{ in_array($v, $studentVars) ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800' }}">{{ '{' . $v . '}' }}</span>
                @endforeach
              </td>
              <td class="whitespace-nowrap p-3">
                <a href="{{ route('templates.edit', $t) }}" class="{{ $btn }} border border-slate-300">Edit</a>
                <form method="POST" action="{{ route('templates.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus template ini?')">
                  @csrf @method('DELETE')
                  <button class="{{ $btn }} bg-red-50 text-red-700">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="p-8 text-center text-slate-500">Belum ada template. Buat yang pertama di sebelah kanan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $templates->links() }}</div>
    <p class="mt-3 text-xs text-slate-500"><span class="rounded-full bg-blue-100 px-2 py-0.5 font-bold text-blue-700">biru</span> = diisi otomatis dari data mahasiswa. <span class="rounded-full bg-amber-100 px-2 py-0.5 font-bold text-amber-800">kuning</span> = variabel isian, diminta saat membuat blast.</p>
  </div>

  {{-- Form template --}}
  <form method="POST" action="{{ $editing->exists ? route('templates.update', $editing) : route('templates.store') }}"
        class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5">
    @csrf
    @if ($editing->exists) @method('PUT') @endif

    <div class="flex items-center justify-between">
      <h2 class="text-base font-extrabold">{{ $editing->exists ? 'Edit template' : 'Template baru' }}</h2>
      @if ($editing->exists)<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Sedang diedit</span>@endif
    </div>

    <label class="block text-sm font-semibold">Nama template
      <input name="nama" value="{{ old('nama', $editing->nama) }}" placeholder="mis. Pengingat UKT" class="{{ $in }}">
    </label>
    <label class="block text-sm font-semibold">Kategori
      <select name="kategori" class="{{ $in }}">
        <option value="">Tanpa kategori</option>
        @foreach ($categories as $c)<option value="{{ $c }}" @selected(old('kategori', $editing->kategori) === $c)>{{ $c }}</option>@endforeach
      </select>
    </label>

    <div>
      <div class="text-xs font-semibold text-slate-500">Klik untuk menyisipkan variabel (diisi otomatis dari data mahasiswa)</div>
      <div class="mt-2 flex flex-wrap gap-2">
        @foreach ($studentVars as $v)
          <button type="button" data-var="{{ $v }}" class="min-h-[36px] rounded-full bg-blue-100 px-3 text-xs font-bold text-blue-700">{{ '{' . $v . '}' }}</button>
        @endforeach
      </div>
      <div class="mt-2 text-xs text-slate-500">Mau variabel lain? Tulis sendiri, mis. <b>{{ '{tanggal_batas}' }}</b> (huruf kecil, angka, dan garis bawah). Nilainya diisi saat membuat blast.</div>
    </div>

    <label class="block text-sm font-semibold">Isi pesan
      <textarea id="isi" name="isi" rows="6" placeholder="Halo {nama_mahasiswa}, pengingat bahwa pembayaran UKT semester ini paling lambat tanggal {tanggal_batas}. Terima kasih." class="{{ $in }} py-2">{{ old('isi', $editing->isi) }}</textarea>
    </label>

    <div>
      <div class="mb-1 text-xs font-semibold text-slate-500">Pratinjau di WhatsApp mahasiswa</div>
      <div class="rounded-xl bg-[#efeae2] p-4">
        <div id="preview" class="max-w-[300px] whitespace-pre-line rounded-br-xl rounded-tr-xl rounded-bl-xl bg-white px-3.5 py-2.5 text-sm leading-relaxed shadow-sm"></div>
      </div>
      <div class="mt-1 text-xs text-slate-500">Contoh data: {{ $sample['nama_mahasiswa'] }}</div>
    </div>

    <div class="flex gap-3 pt-1">
      <button class="{{ $btn }} flex-1 bg-emerald-700 text-white">{{ $editing->exists ? 'Simpan Perubahan' : 'Simpan Template' }}</button>
      @if ($editing->exists)<a href="{{ route('templates.index') }}" class="{{ $btn }} border border-slate-300">Batal</a>@endif
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
const sample = @json($sample);
const box = document.getElementById('isi');
const prev = document.getElementById('preview');

function render() {
  const text = box.value.replace(/\{([a-z0-9_]+)\}/g, (m, k) => sample[k] ?? m);
  prev.textContent = text.trim() === '' ? 'Pratinjau pesan muncul di sini' : text;
}
box.addEventListener('input', render);
render();

document.querySelectorAll('[data-var]').forEach(b => b.addEventListener('click', () => {
  box.setRangeText('{' + b.dataset.var + '}', box.selectionStart, box.selectionEnd, 'end');
  box.focus();
  render();
}));
</script>
@endpush
