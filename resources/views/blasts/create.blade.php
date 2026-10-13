@extends('layouts.kampus')
@section('title', 'Buat Blast')

@section('content')
@php
  $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200';
  $num = 'min-h-[44px] w-24 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200';
  $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold';
  $card = 'rounded-2xl border border-slate-200 bg-white p-6';
  $badge = 'grid h-8 w-8 shrink-0 place-items-center rounded-full bg-emerald-700 text-sm font-extrabold text-white';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-extrabold">Buat Blast Baru</h1>
    <p class="mt-1 text-sm text-slate-500">Tiga langkah: tulis pesan, pilih penerima, atur waktu kirim</p>
  </div>
  <a href="{{ route('blasts.index') }}" class="{{ $btn }} border border-slate-300">Laporan Pengiriman</a>
</div>

@if ($errors->any())
  <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
    @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif

<form id="blast-form" method="POST" action="{{ route('blasts.store') }}" class="mt-6 space-y-5">
  @csrf

  {{-- Langkah 1 --}}
  <section class="{{ $card }}">
    <div class="flex items-start gap-3">
      <span class="{{ $badge }}">1</span>
      <div>
        <h2 class="text-base font-extrabold">Tulis pesan</h2>
        <p class="text-sm text-slate-500">Pilih template atau tulis sendiri. Klik variabel biru untuk menyisipkan data mahasiswa secara otomatis.</p>
      </div>
    </div>

    <div class="mt-5 grid gap-6 lg:grid-cols-5">
      <div class="space-y-4 lg:col-span-3">
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="block text-sm font-semibold">Judul blast <span class="font-normal text-slate-400">(catatan internal)</span>
            <input name="judul" value="{{ old('judul') }}" placeholder="mis. Pengingat UKT Semester Ganjil" class="{{ $in }}">
          </label>
          <label class="block text-sm font-semibold">Template
            <select id="tpl" name="message_template_id" class="{{ $in }}">
              <option value="">Tulis manual</option>
              @foreach ($templates as $t)<option value="{{ $t->id }}" @selected(old('message_template_id') == $t->id)>{{ $t->nama }}</option>@endforeach
            </select>
          </label>
        </div>

        <div>
          <div class="text-xs font-semibold text-slate-500">Sisipkan data mahasiswa</div>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach ($studentVars as $v)
              <button type="button" data-var="{{ $v }}" class="min-h-[36px] rounded-full bg-blue-100 px-3 text-xs font-bold text-blue-700">{{ '{' . $v . '}' }}</button>
            @endforeach
          </div>
        </div>

        <label class="block text-sm font-semibold">Isi pesan
          <textarea id="isi" name="isi_pesan" rows="6" class="{{ $in }} py-2">{{ old('isi_pesan') }}</textarea>
        </label>
        <div id="custom-vars" class="grid gap-3 sm:grid-cols-2"></div>
      </div>

      <div class="lg:col-span-2">
        <div class="lg:sticky lg:top-6">
          <div class="mb-2 text-sm font-semibold">Pratinjau di WhatsApp mahasiswa</div>
          <div class="rounded-xl bg-[#efeae2] p-4">
            <div id="preview" class="max-w-[300px] whitespace-pre-line rounded-br-xl rounded-tr-xl rounded-bl-xl bg-white px-3.5 py-2.5 text-sm leading-relaxed shadow-sm"></div>
          </div>
          <div class="mt-2 text-xs text-slate-500">Contoh data: {{ $sample['nama_mahasiswa'] }}</div>
        </div>
      </div>
    </div>
  </section>

  <div class="grid gap-5 lg:grid-cols-2">
    {{-- Langkah 2 --}}
    <section class="{{ $card }}">
      <div class="flex items-start gap-3">
        <span class="{{ $badge }}">2</span>
        <div>
          <h2 class="text-base font-extrabold">Pilih penerima</h2>
          <p class="text-sm text-slate-500">Biarkan "Semua" untuk mengirim ke seluruh mahasiswa aktif.</p>
        </div>
      </div>
      <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label class="block text-sm font-semibold">Angkatan
          <select id="angkatan" name="angkatan" class="{{ $in }}"><option value="">Semua</option>
            @foreach ($angkatanList as $a)<option value="{{ $a }}" @selected(old('angkatan') == $a)>{{ $a }}</option>@endforeach
          </select>
        </label>
        <label class="block text-sm font-semibold">Jurusan
          <select id="jurusan" name="jurusan" class="{{ $in }}"><option value="">Semua</option>
            @foreach ($jurusanList as $j)<option value="{{ $j }}" @selected(old('jurusan') == $j)>{{ $j }}</option>@endforeach
          </select>
        </label>
      </div>
      <div id="recipients" class="mt-4 rounded-lg bg-green-50 px-3 py-2.5 text-sm font-bold text-green-800">Menghitung...</div>
      <p class="mt-2 text-xs text-slate-500">Hanya mahasiswa aktif. Nomor yang tidak valid dilewati dan tercatat gagal di laporan.</p>
    </section>

    {{-- Langkah 3 --}}
    <section class="{{ $card }}">
      <div class="flex items-start gap-3">
        <span class="{{ $badge }}">3</span>
        <div>
          <h2 class="text-base font-extrabold">Atur waktu kirim</h2>
          <p class="text-sm text-slate-500">Kirim langsung, atau jadwalkan untuk nanti.</p>
        </div>
      </div>

      <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <label class="cursor-pointer">
          <input type="radio" name="kirim" value="sekarang" class="peer sr-only" @checked(old('kirim', 'sekarang') === 'sekarang')>
          <div class="rounded-xl border-2 border-slate-200 p-4 transition peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-300">
            <div class="text-sm font-extrabold">Kirim sekarang</div>
            <div class="mt-0.5 text-xs text-slate-500">Mulai segera setelah tombol ditekan</div>
          </div>
        </label>
        <label class="cursor-pointer">
          <input type="radio" name="kirim" value="jadwal" class="peer sr-only" @checked(old('kirim') === 'jadwal')>
          <div class="rounded-xl border-2 border-slate-200 p-4 transition peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-300">
            <div class="text-sm font-extrabold">Jadwalkan</div>
            <div class="mt-0.5 text-xs text-slate-500">Pilih tanggal dan jam pengiriman</div>
          </div>
        </label>
      </div>

      <label id="jadwal-wrap" class="mt-4 block text-sm font-semibold">Tanggal dan jam kirim
        <input id="jadwal-input" type="datetime-local" name="jadwal" value="{{ old('jadwal') }}" class="{{ $in }}">
      </label>

      <details class="mt-4 rounded-lg border border-slate-200 px-4 py-3" {{ $errors->has('delay_min') || $errors->has('delay_max') ? 'open' : '' }}>
        <summary class="cursor-pointer text-sm font-bold">Pengaturan lanjutan: jeda antar pesan</summary>
        <p class="mt-2 text-xs leading-relaxed text-slate-500">Pesan dikirim satu per satu dengan jeda acak agar nomor WhatsApp lebih aman dari pemblokiran. Biarkan bawaan kalau ragu. Data penerima dan nomor diambil saat blast dibuat.</p>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm font-semibold">
          Antara
          <input id="delay_min" type="number" name="delay_min" value="{{ old('delay_min', 5) }}" min="3" max="120" class="{{ $num }}">
          dan
          <input id="delay_max" type="number" name="delay_max" value="{{ old('delay_max', 15) }}" min="3" max="255" class="{{ $num }}">
          detik
        </div>
      </details>
    </section>
  </div>

  {{-- Ringkasan dan tombol kirim (menempel di bawah layar saat menggulir) --}}
  <section class="sticky bottom-4 z-10 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg">
    <div class="flex flex-wrap gap-x-10 gap-y-2">
      <div><div class="text-xs text-slate-500">Akan dikirim ke</div><div id="sum-send" class="text-sm font-extrabold">-</div></div>
      <div><div class="text-xs text-slate-500">Dilewati (nomor tidak valid)</div><div id="sum-skip" class="text-sm font-extrabold">-</div></div>
      <div><div class="text-xs text-slate-500">Estimasi selesai</div><div id="estimate" class="text-sm font-extrabold">-</div></div>
    </div>
    <button id="submit-btn" class="{{ $btn }} min-w-[200px] bg-emerald-700 text-white">Mulai Kirim</button>
  </section>
</form>
@endsection

@push('scripts')
<script>
const tplMap = @json($templates->pluck('isi', 'id'));
const studentVars = @json($studentVars);
const sample = @json($sample);
const values = Object.assign({}, @json(old('vars', [])));
const $ = id => document.getElementById(id);
const box = $('isi'), prev = $('preview'), cv = $('custom-vars');
const inCls = @json($in);

function customVars() {
  const names = [...box.value.matchAll(/\{([a-z0-9_]+)\}/g)].map(m => m[1]);
  return [...new Set(names)].filter(v => !studentVars.includes(v));
}

function renderCustom() {
  cv.querySelectorAll('input').forEach(i => { values[i.dataset.name] = i.value; });
  cv.innerHTML = '';
  customVars().forEach(n => {
    const l = document.createElement('label');
    l.className = 'block text-sm font-semibold';
    l.textContent = 'Isi {' + n + '}';
    const i = document.createElement('input');
    i.name = 'vars[' + n + ']';
    i.dataset.name = n;
    i.value = values[n] ?? '';
    i.required = true;
    i.className = inCls;
    i.addEventListener('input', renderPreview);
    l.appendChild(i);
    cv.appendChild(l);
  });
}

function renderPreview() {
  const vals = Object.assign({}, sample);
  cv.querySelectorAll('input').forEach(i => { if (i.value) vals[i.dataset.name] = i.value; });
  const t = box.value.replace(/\{([a-z0-9_]+)\}/g, (m, k) => vals[k] ?? m);
  prev.textContent = t.trim() === '' ? 'Pratinjau pesan muncul di sini' : t;
}

let total = 0, invalid = 0;
function renderSummary() {
  const send = total - invalid;
  const avg = (Number($('delay_min').value) + Number($('delay_max').value)) / 2;
  $('recipients').textContent = total + ' mahasiswa terpilih' + (invalid ? ' (' + invalid + ' nomor tidak valid akan dilewati)' : '');
  $('sum-send').textContent = send + ' mahasiswa';
  $('sum-skip').textContent = invalid;
  $('estimate').textContent = send > 0 ? 'sekitar ' + Math.ceil(send * avg / 60) + ' menit' : '-';
}

async function loadRecipients() {
  const p = new URLSearchParams({angkatan: $('angkatan').value, jurusan: $('jurusan').value});
  const d = await (await fetch(@json(route('blasts.recipients')) + '?' + p, {headers: {Accept: 'application/json'}})).json();
  total = d.total; invalid = d.invalid;
  renderSummary();
}

$('tpl').addEventListener('change', e => {
  const t = tplMap[e.target.value];
  if (t !== undefined) { box.value = t; renderCustom(); renderPreview(); }
});
box.addEventListener('input', () => { renderCustom(); renderPreview(); });
['angkatan', 'jurusan'].forEach(id => $(id).addEventListener('change', loadRecipients));
['delay_min', 'delay_max'].forEach(id => $(id).addEventListener('input', renderSummary));
document.querySelectorAll('[data-var]').forEach(b => b.addEventListener('click', () => {
  box.setRangeText('{' + b.dataset.var + '}', box.selectionStart, box.selectionEnd, 'end');
  box.focus(); renderCustom(); renderPreview();
}));
const mode = () => document.querySelector('input[name="kirim"]:checked').value;
function renderMode() {
  const jadwal = mode() === 'jadwal';
  $('jadwal-wrap').classList.toggle('hidden', !jadwal);
  $('jadwal-input').required = jadwal;
  $('submit-btn').textContent = jadwal ? 'Jadwalkan Blast' : 'Mulai Kirim';
}
document.querySelectorAll('input[name="kirim"]').forEach(r => r.addEventListener('change', renderMode));
$('blast-form').addEventListener('submit', async e => {
  if (e.target.dataset.confirmed) return;
  e.preventDefault();
  const n = total - invalid;
  const jadwal = mode() === 'jadwal';
  const msg = jadwal
    ? 'Pesan akan dikirim ke ' + n + ' mahasiswa pada ' + $('jadwal-input').value.replace('T', ' ') + '.'
    : 'Pesan akan langsung dikirim ke ' + n + ' mahasiswa.';
  const yes = await confirmDialog(msg, {title: jadwal ? 'Jadwalkan blast?' : 'Mulai kirim sekarang?', okText: jadwal ? 'Ya, jadwalkan' : 'Ya, kirim'});
  if (yes) { e.target.dataset.confirmed = '1'; e.target.submit(); }
});

renderCustom(); renderPreview(); loadRecipients(); renderMode();
</script>
@endpush
