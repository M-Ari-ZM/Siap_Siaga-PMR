@extends('layouts.app')

@section('title', 'Daftar Akun Baru — Siap Siaga PMR')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-white rounded-xl p-6 sm:p-8 border border-slate-200 space-y-6">
        
        <div class="text-center space-y-2">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Daftar Akun Baru</h1>
            <p class="text-xs text-slate-500">Buat akun untuk melapor atau bergabung sebagai PMR</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama Anda" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                @error('name')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@sch.id" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                @error('email')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="081234567890" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                @error('phone_number')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Daftar Sebagai Peran</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 rounded-lg border-2 border-slate-200 hover:border-red-500 cursor-pointer text-center has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                        <input type="radio" name="role" value="student" class="sr-only" checked>
                        <span class="text-lg">🎒</span>
                        <span class="text-xs font-bold block text-slate-800 mt-1">Siswa Pelapor</span>
                    </label>
                    <label class="p-3 rounded-lg border-2 border-slate-200 hover:border-emerald-500 cursor-pointer text-center has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="role" value="pmr" class="sr-only">
                        <span class="text-lg">🩹</span>
                        <span class="text-xs font-bold block text-slate-800 mt-1">Anggota PMR</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                @error('password')
                <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi password" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm transition-colors">
                Daftar Sekarang
            </button>
        </form>

        <p class="text-center text-xs text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-red-600 hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
