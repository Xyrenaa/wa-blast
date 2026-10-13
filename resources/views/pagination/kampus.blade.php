@if ($paginator->hasPages())
@php
  $base = 'inline-flex h-11 min-w-[44px] items-center justify-center rounded-lg border px-3 text-sm font-bold transition';
@endphp
<nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3">
  <p class="text-sm text-slate-500">
    Menampilkan <b class="text-slate-800">{{ $paginator->firstItem() }}</b>-<b class="text-slate-800">{{ $paginator->lastItem() }}</b>
    dari <b class="text-slate-800">{{ $paginator->total() }}</b> data
  </p>

  <div class="flex flex-wrap items-center gap-1.5">
    {{-- Sebelumnya --}}
    @if ($paginator->onFirstPage())
      <span aria-disabled="true" class="{{ $base }} cursor-not-allowed border-slate-200 bg-slate-50 text-slate-300">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="{{ $base }} border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </a>
    @endif

    {{-- Nomor halaman --}}
    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="px-1 text-slate-400">&hellip;</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span aria-current="page" class="{{ $base }} border-emerald-700 bg-emerald-700 text-white shadow-sm">{{ $page }}</span>
          @else
            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}" class="{{ $base }} border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    {{-- Berikutnya --}}
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="{{ $base }} border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </a>
    @else
      <span aria-disabled="true" class="{{ $base }} cursor-not-allowed border-slate-200 bg-slate-50 text-slate-300">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </span>
    @endif
  </div>
</nav>
@endif
