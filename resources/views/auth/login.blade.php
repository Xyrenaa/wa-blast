@extends('layouts.auth')
@section('title', 'Masuk')

@section('content')
@php $in = 'mt-1 w-full min-h-[44px] rounded-lg border border-slate-300 px-3 text-sm font-medium focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200'; @endphp

<h1 class="text-2xl font-extrabold">Masuk</h1>
<p class="mt-1 text-sm text-slate-500">Gunakan akun staf kampus untuk melanjutkan.</p>

@if (session('status'))
  <div class="mt-4 rounded-lg bg-green-50 px-3 py-2 text-sm font-semibold text-green-800">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
  @csrf

  <div>
    <label for="email" class="text-sm font-semibold">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
           placeholder="nama@kampus.ac.id" class="{{ $in }}">
    @error('email')
      <p class="mt-1 text-xs font-semibold text-red-600">{{ str_contains($message, 'credentials') ? 'Email atau password salah.' : $message }}</p>
    @enderror
  </div>

  <div>
    <label for="password" class="text-sm font-semibold">Password</label>
    <input id="password" type="password" name="password" required autocomplete="current-password" class="{{ $in }}">
    @error('password')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
    <label class="mt-2 flex items-center gap-2 text-xs font-semibold text-slate-500">
      <input type="checkbox" onchange="document.getElementById('password').type = this.checked ? 'text' : 'password'"> Tampilkan password
    </label>
  </div>

  <label class="flex items-center gap-2 text-sm font-semibold">
    <input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya di perangkat ini
  </label>

  <button class="min-h-[48px] w-full rounded-lg bg-[#0f7a4e] text-sm font-bold text-white hover:bg-[#0c6a43]">Masuk</button>
</form>

<p class="mt-6 text-center text-xs text-slate-500">Lupa password atau belum punya akun? Hubungi admin kampus.</p>
@endsection
