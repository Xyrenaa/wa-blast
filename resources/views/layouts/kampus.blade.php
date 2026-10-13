<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') - KampusBlast</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
{{-- Tailwind CDN untuk development. Untuk produksi pindahkan ke Vite. --}}
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
main>*{animation:fadeUp .45s ease both}
main>*:nth-child(2){animation-delay:.06s}
main>*:nth-child(3){animation-delay:.12s}
main>*:nth-child(4){animation-delay:.18s}
main>*:nth-child(5){animation-delay:.24s}
main>*:nth-child(n+6){animation-delay:.3s}
aside a{transition:background-color .15s,transform .15s}
aside a:hover{transform:translateX(4px)}
main button,main a[class*="min-h-"]{transition:transform .12s,filter .15s}
main button:hover,main a[class*="min-h-"]:hover{filter:brightness(.95)}
main button:active,main a[class*="min-h-"]:active{transform:scale(.97)}
main tbody tr{transition:background-color .15s}
main tbody tr:hover{background:#f3f8f4}
.lift{transition:transform .2s,box-shadow .2s}
.lift:hover{transform:translateY(-3px);box-shadow:0 10px 24px -12px rgba(12,59,44,.35)}
input,select,textarea{transition:border-color .15s,box-shadow .15s}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}
</style>
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
      <form method="POST" action="{{ route('logout') }}" data-confirm="Kamu akan keluar dari KampusBlast." data-confirm-title="Keluar dari akun?" data-confirm-ok="Keluar">
        @csrf
        <button class="min-h-[44px] w-full rounded-lg px-3 text-left text-sm font-semibold text-[#b9d3c6] hover:bg-[#104a37]">Keluar</button>
      </form>
    </div>
  </aside>
  <main class="flex-1 min-w-0 p-6 md:p-9">@yield('content')</main>
</div>
{{-- Pop up konfirmasi bergaya KampusBlast (pengganti confirm bawaan browser) --}}
<div id="dlg" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 opacity-0 transition-opacity duration-150">
  <div id="dlg-box" class="w-full max-w-sm scale-95 rounded-2xl bg-white p-6 opacity-0 shadow-xl transition duration-150">
    <h3 id="dlg-title" class="text-lg font-extrabold"></h3>
    <p id="dlg-msg" class="mt-2 text-sm leading-relaxed text-slate-600"></p>
    <div class="mt-6 flex justify-end gap-2">
      <button type="button" id="dlg-cancel" class="min-h-[44px] rounded-lg border border-slate-300 px-5 text-sm font-bold">Batal</button>
      <button type="button" id="dlg-ok" class="min-h-[44px] rounded-lg bg-[#0f7a4e] px-5 text-sm font-bold text-white"></button>
    </div>
  </div>
</div>
<script>
(function () {
  const dlg = document.getElementById('dlg'), box = document.getElementById('dlg-box');
  const title = document.getElementById('dlg-title'), msg = document.getElementById('dlg-msg');
  const ok = document.getElementById('dlg-ok'), cancel = document.getElementById('dlg-cancel');
  let resolver = null;

  function close(result) {
    box.classList.remove('opacity-100', 'scale-100');
    box.classList.add('opacity-0', 'scale-95');
    dlg.classList.add('opacity-0');
    setTimeout(() => { dlg.classList.add('hidden'); dlg.classList.remove('flex'); }, 150);
    if (resolver) { resolver(result); resolver = null; }
  }

  // confirmDialog('pesan', {title, okText, danger}) -> Promise<boolean>
  window.confirmDialog = (message, o = {}) => new Promise(resolve => {
    resolver = resolve;
    title.textContent = o.title || 'Konfirmasi';
    msg.textContent = message;
    ok.textContent = o.okText || 'Ya, lanjutkan';
    ok.className = 'min-h-[44px] rounded-lg px-5 text-sm font-bold text-white ' + (o.danger ? 'bg-red-600 hover:bg-red-700' : 'bg-[#0f7a4e] hover:bg-[#0c6a43]');
    dlg.classList.remove('hidden'); dlg.classList.add('flex');
    requestAnimationFrame(() => requestAnimationFrame(() => {
      dlg.classList.remove('opacity-0');
      box.classList.remove('opacity-0', 'scale-95');
      box.classList.add('opacity-100', 'scale-100');
      cancel.focus();
    }));
  });

  ok.addEventListener('click', () => close(true));
  cancel.addEventListener('click', () => close(false));
  dlg.addEventListener('click', e => { if (e.target === dlg) close(false); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && resolver) close(false); });

  // Form dengan atribut data-confirm otomatis memakai pop up ini
  document.addEventListener('submit', async e => {
    const f = e.target;
    if (!f.dataset || !f.dataset.confirm || f.dataset.confirmed) return;
    e.preventDefault();
    const yes = await window.confirmDialog(f.dataset.confirm, {
      title: f.dataset.confirmTitle, okText: f.dataset.confirmOk, danger: f.dataset.confirmDanger !== undefined,
    });
    if (yes) { f.dataset.confirmed = '1'; f.submit(); }
  });

  // Angka berhitung naik pelan-pelan (elemen dengan data-count)
  if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('[data-count]').forEach(el => {
      const end = Number(el.dataset.count);
      if (!end) return;
      const t0 = performance.now(), dur = 800;
      const step = t => {
        const p = Math.min((t - t0) / dur, 1);
        el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    });
  }
})();
</script>
@stack('scripts')
</body>
</html>
