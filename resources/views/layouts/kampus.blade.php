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
<body class="bg-[#f3f6f3] text-[#14231c]">
<div class="flex min-h-screen flex-wrap">
  <aside class="w-full md:w-60 bg-[#0c3b2c] text-white p-4 flex flex-col gap-1">
    <div class="flex items-center gap-2.5 px-2 pb-5 text-lg font-extrabold">
      <span class="w-8 h-8 rounded-lg bg-[#25d366]"></span>KampusBlast
    </div>
    @php
      $nav = [
        ['Dashboard', 'dashboard', ['dashboard']],
        ['Buat Blast', 'blasts.create', ['blasts.create']],
        ['Template Pesan', 'templates.index', ['templates.*']],
        ['Data Mahasiswa', 'students.index', ['students.*']],
        ['Laporan Pengiriman', 'blasts.index', ['blasts.index', 'blasts.show']],
      ];
    @endphp
    @foreach ($nav as [$label, $route, $patterns])
      <a href="{{ route($route) }}"
         class="px-3 py-3 rounded-lg text-sm font-semibold {{ request()->routeIs(...$patterns) ? 'bg-[#17513c] text-white' : 'text-[#b9d3c6] hover:bg-[#104a37]' }}">{{ $label }}</a>
    @endforeach
      <div class="mt-auto border-t border-white/10 pt-4">
      <div class="px-3 pb-2 text-xs text-[#9fc4b2]">Masuk sebagai<br><span class="text-sm font-bold text-white">{{ auth()->user()?->name }}</span></div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="min-h-[44px] w-full rounded-lg px-3 text-left text-sm font-semibold text-[#b9d3c6] hover:bg-[#104a37]">Keluar</button>
      </form>
    </div>
  </aside>
  <main class="flex-1 min-w-0 p-6 md:p-9">@yield('content')</main>
</div>
@stack('scripts')
</body>
</html>