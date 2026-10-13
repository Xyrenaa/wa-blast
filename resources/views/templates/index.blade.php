@extends('layouts.kampus')
@section('title', 'Template Pesan')

@section('content')
@php
  $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200';
  $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold';
  $openModal = $editing->exists || request()->boolean('baru') || $errors->any();
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-extrabold">Template Pesan</h1>
    <p class="mt-1 text-sm text-slate-500">Tulis pesan sekali, pakai berkali-kali di Buat Blast</p>
  </div>
  <a id="btn-new" href="{{ route('templates.index', ['baru' => 1]) }}" class="{{ $btn }} bg-emerald-700 text-white">+ Template Baru</a>
</div>

@if (session('success'))
  <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ session('success') }}</div>
@endif

<div class="mt-6 rounded-2xl border border-slate-200 bg-white">
  <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
    <form method="GET" class="w-full sm:max-w-sm">
      <label class="sr-only" for="q">Cari template</label>
      <input id="q" name="q" value="{{ request('q') }}" placeholder="Cari nama atau isi template..." class="{{ $in }} mt-0">
    </form>
    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
      <span class="font-bold text-slate-700">{{ $templates->total() }} template</span>
      <span class="rounded-full bg-blue-100 px-2 py-0.5 font-bold text-blue-700">biru</span> otomatis dari data mahasiswa
      <span class="rounded-full bg-amber-100 px-2 py-0.5 font-bold text-amber-800">kuning</span> diisi saat blast
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-500">
          <th class="px-5 py-3">Nama template</th>
          <th class="px-5 py-3">Isi pesan</th>
          <th class="whitespace-nowrap px-5 py-3">Terakhir diubah</th>
          <th class="px-5 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($templates as $t)
          <tr class="border-b border-slate-100 align-top {{ $editing->id === $t->id ? 'bg-emerald-50' : '' }}">
            <td class="px-5 py-4">
              <div class="font-bold">{{ $t->nama }}</div>
              @if ($t->kategori)
                <span class="mt-1 inline-block rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">{{ $t->kategori }}</span>
              @endif
            </td>
            <td class="px-5 py-4">
              <div class="line-clamp-2 max-w-xl text-slate-600">{{ $t->isi }}</div>
              <div class="mt-2">
                @foreach (\App\Support\TemplateRenderer::variables($t->isi) as $v)
                  <span class="mb-1 mr-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-bold {{ in_array($v, $studentVars) ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800' }}">{{ '{' . $v . '}' }}</span>
                @endforeach
              </div>
            </td>
            <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $t->updated_at->format('d M Y, H:i') }}</td>
            <td class="whitespace-nowrap px-5 py-4 text-right">
              <a href="{{ route('templates.edit', $t) }}" class="{{ $btn }} border border-slate-300">Edit</a>
              <form method="POST" action="{{ route('templates.destroy', $t) }}" class="inline"
                    data-confirm="Template ini akan dihapus permanen. Blast lama yang memakainya tidak terpengaruh." data-confirm-title="Hapus template?" data-confirm-ok="Hapus" data-confirm-danger>
                @csrf @method('DELETE')
                <button class="{{ $btn }} bg-red-50 text-red-700">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="px-5 py-14 text-center">
              <div class="text-base font-extrabold">{{ request('q') ? 'Tidak ada template yang cocok' : 'Belum ada template' }}</div>
              <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500">
                {{ request('q') ? 'Coba kata kunci lain.' : 'Template membuat staf tidak perlu mengetik pesan dari nol setiap kali mengirim pengumuman.' }}
              </p>
              @unless (request('q'))
                <a href="{{ route('templates.index', ['baru' => 1]) }}" class="{{ $btn }} mt-4 bg-emerald-700 text-white">Buat template pertama</a>
              @endunless
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if ($templates->hasPages())
    <div class="border-t border-slate-100 p-5">{{ $templates->links('pagination.kampus') }}</div>
  @endif
</div>
@endsection

@push('scripts')
{{-- Editor template (pop up lebar): sengaja di luar <main> --}}
<div id="tpl-modal" data-mode="{{ $editing->exists ? 'edit' : 'create' }}"
     class="fixed inset-0 z-40 {{ $openModal ? 'flex' : 'hidden' }} items-center justify-center bg-black/40 p-4 opacity-0 transition-opacity duration-150">
  <div id="tpl-box" class="max-h-[92vh] w-full max-w-4xl scale-95 overflow-y-auto rounded-2xl bg-white opacity-0 shadow-xl transition duration-150">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
      <div>
        <h2 class="text-lg font-extrabold">{{ $editing->exists ? 'Edit template' : 'Template baru' }}</h2>
        <p class="text-xs text-slate-500">Isi di kiri, lihat hasilnya di kanan.</p>
      </div>
      <button type="button" id="tpl-close" aria-label="Tutup" class="grid h-10 w-10 place-items-center rounded-lg text-slate-500 hover:bg-slate-100">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>

    <form method="POST" action="{{ $editing->exists ? route('templates.update', $editing) : route('templates.store') }}" class="p-6">
      @csrf
      @if ($editing->exists) @method('PUT') @endif

      @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
          @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <div class="grid gap-6 md:grid-cols-5">
        <div class="space-y-4 md:col-span-3">
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold">Nama template
              <input id="nama" name="nama" value="{{ old('nama', $editing->nama) }}" placeholder="mis. Pengingat UKT" class="{{ $in }}">
            </label>
            <label class="block text-sm font-semibold">Kategori
              <select name="kategori" class="{{ $in }}">
                <option value="">Tanpa kategori</option>
                @foreach ($categories as $c)<option value="{{ $c }}" @selected(old('kategori', $editing->kategori) === $c)>{{ $c }}</option>@endforeach
              </select>
            </label>
          </div>

          <div>
            <div class="text-xs font-semibold text-slate-500">Klik untuk menyisipkan data mahasiswa ke dalam pesan</div>
            <div class="mt-2 flex flex-wrap gap-2">
              @foreach ($studentVars as $v)
                <button type="button" data-var="{{ $v }}" class="min-h-[36px] rounded-full bg-blue-100 px-3 text-xs font-bold text-blue-700">{{ '{' . $v . '}' }}</button>
              @endforeach
            </div>
          </div>

          <label class="block text-sm font-semibold">Isi pesan
            <textarea id="isi" name="isi" rows="7" placeholder="Halo {nama_mahasiswa}, pengingat bahwa pembayaran UKT semester ini paling lambat tanggal {tanggal_batas}. Terima kasih." class="{{ $in }} py-2">{{ old('isi', $editing->isi) }}</textarea>
          </label>
          <p class="text-xs leading-relaxed text-slate-500">Butuh data lain, misalnya tanggal? Tulis sendiri dengan kurung kurawal, contoh <b>{{ '{tanggal_batas}' }}</b> (huruf kecil, angka, dan garis bawah). Nilainya diisi saat membuat blast.</p>
        </div>

        <div class="md:col-span-2">
          <div class="mb-2 text-sm font-semibold">Pratinjau di WhatsApp mahasiswa</div>
          <div class="rounded-xl bg-[#efeae2] p-4">
            <div id="preview" class="max-w-[300px] whitespace-pre-line rounded-br-xl rounded-tr-xl rounded-bl-xl bg-white px-3.5 py-2.5 text-sm leading-relaxed shadow-sm"></div>
          </div>
          <div class="mt-2 text-xs text-slate-500">Contoh data: {{ $sample['nama_mahasiswa'] }}</div>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
        <button type="button" id="tpl-cancel" class="{{ $btn }} border border-slate-300">Batal</button>
        <button class="{{ $btn }} min-w-[160px] bg-emerald-700 text-white">{{ $editing->exists ? 'Simpan Perubahan' : 'Simpan Template' }}</button>
      </div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('tpl-modal'), box = document.getElementById('tpl-box');
const area = document.getElementById('isi'), prev = document.getElementById('preview');
const sample = @json($sample);
const indexUrl = @json(route('templates.index'));

function render() {
  const t = area.value.replace(/\{([a-z0-9_]+)\}/g, (m, k) => sample[k] ?? m);
  prev.textContent = t.trim() === '' ? 'Pratinjau pesan muncul di sini' : t;
}
area.addEventListener('input', render);
render();

document.querySelectorAll('[data-var]').forEach(b => b.addEventListener('click', () => {
  area.setRangeText('{' + b.dataset.var + '}', area.selectionStart, area.selectionEnd, 'end');
  area.focus();
  render();
}));

function openModal() {
  modal.classList.remove('hidden'); modal.classList.add('flex');
  document.body.style.overflow = 'hidden';
  requestAnimationFrame(() => requestAnimationFrame(() => {
    modal.classList.remove('opacity-0');
    box.classList.remove('opacity-0', 'scale-95');
    box.classList.add('opacity-100', 'scale-100');
  }));
}
function closeModal() {
  box.classList.remove('opacity-100', 'scale-100');
  box.classList.add('opacity-0', 'scale-95');
  modal.classList.add('opacity-0');
  document.body.style.overflow = '';
  setTimeout(() => {
    modal.classList.add('hidden'); modal.classList.remove('flex');
    history.replaceState(null, '', indexUrl);
  }, 150);
}

document.getElementById('tpl-close').addEventListener('click', closeModal);
document.getElementById('tpl-cancel').addEventListener('click', closeModal);
modal.addEventListener('mousedown', e => { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && modal.classList.contains('flex') && !document.getElementById('dlg').classList.contains('flex')) closeModal();
});
// Kalau pop up sedang mode "baru", tombol + Template Baru cukup membuka pop up tanpa memuat ulang halaman
document.getElementById('btn-new').addEventListener('click', e => {
  if (modal.dataset.mode === 'create') { e.preventDefault(); openModal(); }
});

@if ($openModal) openModal(); @endif
</script>
@endpush
