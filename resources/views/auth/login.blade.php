@extends('layouts.app')

@section('title', 'Masuk ke Siap Siaga PMR')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-white rounded-xl p-6 sm:p-8 border border-slate-200 space-y-6">

        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-10 h-10 rounded-lg bg-red-600 text-white flex items-center justify-center font-bold mx-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Masuk ke Siap Siaga</h1>
            <p class="text-xs text-slate-500">Gunakan akun Siswa, Petugas PMR, atau Administrator</p>
        </div>

        @if(session('error'))
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs font-bold text-red-700">
            {{ session('error') }}
        </div>
        @endif

        @if(session('status'))
        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700">
            {{ session('status') }}
        </div>
        @endif

        <!-- Form Login -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Induk (NIS / NIP / ID Anggota)</label>
                <input type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required placeholder="Contoh: 20241001 atau PMR-2024-001" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none @error('nomor_induk') border-red-500 @enderror">
                @error('nomor_induk')
                <p class="text-[11px] text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
            </div>

            <div class="flex items-center text-xs">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded text-red-600 focus:ring-red-500">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm transition-colors">
                Masuk Sekarang
            </button>
        </form>

        <!-- Akun Demo Cepat -->
        <div class="pt-4 border-t border-slate-100">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2.5">
                ⚡ Demo Cepat (Klik untuk Isi Otomatis)
            </p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillDemo('20241001', 'password')" class="px-2 py-2 rounded-lg border border-slate-200 hover:border-slate-400 hover:bg-slate-50 text-center transition-all">
                    <span class="block text-xs font-bold text-slate-800">👤 Siswa</span>
                    <span class="block text-[10px] text-slate-400 truncate">NIS: 20241001</span>
                </button>
                <button type="button" onclick="fillDemo('PMR-2024-001', 'password')" class="px-2 py-2 rounded-lg border border-slate-200 hover:border-slate-400 hover:bg-slate-50 text-center transition-all">
                    <span class="block text-xs font-bold text-slate-800">⛑️ PMR</span>
                    <span class="block text-[10px] text-slate-400 truncate">ID: PMR-001</span>
                </button>
                <button type="button" onclick="fillDemo('ADMIN-001', 'password')" class="px-2 py-2 rounded-lg border border-slate-200 hover:border-slate-400 hover:bg-slate-50 text-center transition-all">
                    <span class="block text-xs font-bold text-slate-800">🛡️ Admin</span>
                    <span class="block text-[10px] text-slate-400 truncate">ID: ADMIN-001</span>
                </button>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500">
            Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-red-600 hover:underline">Daftar Akun Baru</a>
        </p>
    </div>
</div>

<script>
    function fillDemo(nomorInduk, password) {
        const idInput = document.querySelector('input[name="nomor_induk"]');
        const passwordInput = document.querySelector('input[name="password"]');
        if (idInput && passwordInput) {
            idInput.value = nomorInduk;
            passwordInput.value = password;
            idInput.classList.add('ring-2', 'ring-red-500');
            passwordInput.classList.add('ring-2', 'ring-red-500');
            setTimeout(() => {
                idInput.classList.remove('ring-2', 'ring-red-500');
                passwordInput.classList.remove('ring-2', 'ring-red-500');
            }, 600);
        }
    }
</script>
@endsection
