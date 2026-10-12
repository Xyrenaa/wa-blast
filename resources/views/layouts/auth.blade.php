<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') - KampusBlast</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
{{-- Tailwind CDN untuk development. Untuk produksi pindahkan ke Vite. --}}
<script src="https://cdn.tailwindcss.com"></script>
<style>body{font-family:'Plus Jakarta Sans',system-ui,sans-serif}</style>
@if (file_exists(public_path('hot')))
  @vite(['resources/js/app.js'])
@endif
</head>
<body class="bg-white text-[#14231c]">
<div class="grid min-h-screen lg:grid-cols-2">

  {{-- Panel kiri: identitas aplikasi (disembunyikan di layar kecil) --}}
  <aside class="relative hidden flex-col justify-between overflow-hidden bg-[#0c3b2c] p-12 text-white lg:flex">
    <div class="absolute -bottom-28 -right-28 h-96 w-96 rounded-full bg-[#104a37]"></div>
    <div class="absolute -bottom-10 right-40 h-40 w-40 rounded-full bg-[#17513c]"></div>

    <div class="relative flex items-center gap-3 text-xl font-extrabold">
      <span class="h-10 w-10 rounded-xl bg-[#25d366]"></span>KampusBlast
    </div>

    <div class="relative max-w-md">
      <h2 class="text-4xl font-extrabold leading-tight">Pengumuman kampus, langsung ke WhatsApp mahasiswa.</h2>
      <ul class="mt-8 space-y-3 text-[#b9d3c6]">
        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-[#25d366]"></span>Kirim ke banyak mahasiswa sekaligus</li>
        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-[#25d366]"></span>Template pesan dan penjadwalan</li>
        <li class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-[#25d366]"></span>Laporan pengiriman yang jelas</li>
      </ul>

      <div class="mt-10 max-w-xs rounded-2xl rounded-bl-sm bg-white p-4 text-sm leading-relaxed text-[#14231c] shadow-lg">
        Halo Ahmad, pengumuman KRS semester ini sudah dibuka. Silakan isi sebelum tanggal yang ditentukan.
        <div class="mt-1 text-right text-[11px] text-slate-500">08.00 &#10003;&#10003;</div>
      </div>
    </div>

    <div class="relative text-xs text-[#9fc4b2]">&copy; {{ date('Y') }} KampusBlast</div>
  </aside>

  {{-- Panel kanan: form --}}
  <main class="flex items-center justify-center bg-[#f3f6f3] p-6">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <div class="mb-6 flex items-center gap-2.5 text-lg font-extrabold lg:hidden">
        <span class="h-8 w-8 rounded-lg bg-[#25d366]"></span>KampusBlast
      </div>
      @yield('content')
    </div>
  </main>
</div>
</body>
</html>
