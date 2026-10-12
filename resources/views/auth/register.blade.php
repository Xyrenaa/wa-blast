@extends('layouts.auth')
@section('title', 'Buat Akun')

{{-- Catatan: halaman ini hanya bisa dibuka kalau route register diaktifkan kembali di routes/auth.php --}}
@section('content')
@php $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 px-3 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200'; @endphp

<h1 class="text-2xl font-extrabold">Buat Akun Staf</h1>
<p class="mt-1 text-sm text-slate-500">Isi data di bawah untuk membuat akun baru.</p>

<form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
  @csrf

  @foreach ([
    ['name', 'Nama lengkap', 'text', 'name'],
    ['email', 'Email', 'email', 'username'],
    ['password', 'Password', 'password', 'new-password'],
    ['password_confirmation', 'Ulangi password', 'password', 'new-password'],
  ] as [$field, $label, $type, $auto])
    <div>
      <label for="{{ $field }}" class="text-sm font-semibold">{{ $label }}</label>
      <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" required autocomplete="{{ $auto }}"
             @if (! str_starts_with($field, 'password')) value="{{ old($field) }}" @endif class="{{ $in }}">
      @error($field)<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    </div>
  @endforeach

  <button class="min-h-[48px] w-full rounded-lg bg-[#0f7a4e] text-sm font-bold text-white hover:bg-[#0c6a43]">Buat Akun</button>
</form>

<p class="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-emerald-700 underline">Masuk</a></p>
@endsection
