@extends('layouts.kampus')
@section('title', 'Buat Blast')

@section('content')
@php
  $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium';
  $btn = 'inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 text-sm font-bold';
  $card = 'rounded-2xl border border-slate-200 bg-white p-5';
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <h1 class="text-2xl font-extrabold">Buat Blast Baru</h1>
    <p class="text-sm text-slate-500 mt-1">Tulis pesan, pilih penerima, lalu kirim</p>
  </div>
  <a href="{{ route('blasts.index') }}" class="{{ $btn }} border border-slate-300">Riwayat Blast</a>
</div>

@if ($errors->any())
  <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
    @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
  </div>
@endif

<form id="blast-form" method="POST" action="{{ route('blasts.store') }}" class="mt-6 grid items-start gap-5 lg:grid-cols-2">
  @csrf
  <div class="space-y-4">
    <div class="{{ $card }} space-y-3">
      <h2 class="text-base font-extrabold">1. Pesan</h2>
      <label class="block text-sm font-semibold">Judul blast
        <input name="judul" value="{{ old('judul') }}" placeholder="mis. Pengingat UKT Semester Ganjil" class="{{ $in }}">
      </label>
      <label class="block text-sm font-semibold">Template
        <select id="tpl" name="message_template_id" class="{{ $in }}">
          <option value="">Tulis manual</option>
          @foreach ($templates as $t)<option value="{{ $t->id }}" @selected(old('message_template_id') == $t->id)>{{ $t->nama }}</option>@endforeach
        </select>
      </label>
      <div>
        <div class="text-xs font-semibold text-slate-500">Klik untuk menyisipkan variabel (diisi otomatis dari data mahasiswa)</div>
        <div class="mt-2 flex flex-wrap gap-2">
          @foreach ($studentVars as $v)
            <button type="button" data-var="{{ $v }}" class="min-h-[36px] rounded-full bg-blue-100 px-3 text-xs font-bold text-blue-700">{{ '{' . $v . '}' }}</button>
          @endforeach
        </div>
      </div>
      <label class="block text-sm font-semibold">Isi pesan
        <textarea id="isi" name="isi_pesan" rows="6" class="{{ $in }} py-2">{{ old('isi_pesan') }}</textarea>
      </label>
      <div id="custom-vars" class="space-y-2"></div>
    </div>

    <div class="{{ $card }} space-y-3">
      <h2 class="text-base font-extrabold">2. Penerima</h2>
      <div class="grid gap-3 sm:grid-cols-2">
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
      <div id="recipients" class="rounded-lg bg-green-50 px-3 py-2 text-sm font-bold text-green-800">Menghitung...</div>
      <p class="text-xs text-slate-500">Hanya mahasiswa aktif. Nomor tidak valid otomatis dilewati dan tercatat gagal di log.</p>
    </div>

    <div class="{{ $card }} space-y-3">
      <h2 class="text-base font-extrabold">3. Pengiriman</h2>
      <div class="grid gap-3 sm:grid-cols-2">
        <label class="block text-sm font-semibold">Jeda minimum (detik)
          <input id="delay_min" type="number" name="delay_min" value="{{ old('delay_min', 5) }}" min="3" max="120" class="{{ $in }}">
        </label>
        <label class="block text-sm font-semibold">Jeda maksimum (detik)
          <input id="delay_max" type="number" name="delay_max" value="{{ old('delay_max', 15) }}" min="3" max="255" class="{{ $in }}">
        </label>
      </div>
      <p class="text-xs text-slate-500">Jeda diacak di antara dua angka ini supaya nomor WhatsApp lebih aman dari pemblokiran. Penjadwalan ditambahkan di tahap berikutnya.</p>
    </div>
  </div>

  <div class="space-y-4">
    <div class="{{ $card }}">
      <h2 class="mb-2 text-base font-extrabold">Pratinjau di WhatsApp mahasiswa</h2>
      <div class="rounded-xl bg-[#efeae2] p-4">
        <div id="preview" class="max-w-[300px] whitespace-pre-line rounded-br-xl rounded-tr-xl rounded-bl-xl bg-white px-3.5 py-2.5 text-sm leading-relaxed shadow-sm"></div>
      </div>
      <div class="mt-1 text-xs text-slate-500">Contoh data: {{ $sample['nama_mahasiswa'] }}</div>
    </div>
    <div class="{{ $card }} space-y-3">
      <h2 class="text-base font-extrabold">Ringkasan</h2>
      <div class="flex justify-between text-sm"><span>Akan dikirim ke</span><b id="sum-send">-</b></div>
      <div class="flex justify-between text-sm"><span>Dilewati (nomor tidak valid)</span><b id="sum-skip">-</b></div>
      <div class="flex justify-between text-sm"><span>Estimasi selesai</span><b id="estimate">-</b></div>
      <div class="text-xs text-slate-500">Berjalan di latar belakang. Halaman boleh ditutup setelah pengiriman dimulai.</div>
      <button class="{{ $btn }} w-full bg-emerald-700 text-white">Mulai Kirim</button>
    </div>
  </div>
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
$('blast-form').addEventListener('submit', e => {
  if (!confirm('Kirim pesan ke ' + (total - invalid) + ' mahasiswa sekarang?')) e.preventDefault();
});

renderCustom(); renderPreview(); loadRecipients();
</script>
@endpush
