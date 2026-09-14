@extends('layouts.app')

@section('title', 'Daftar Akun Baru — Siap Siaga PMR')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-white rounded-xl p-6 sm:p-8 border border-slate-200 space-y-6">
        
        <div class="text-center space-y-2">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Daftar Akun Warga Sekolah</h1>
            <p class="text-xs text-slate-500">Pendaftaran akun pelapor darurat untuk Siswa, Guru, dan Staf Sekolah</p>
        </div>

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Induk (NIS / NIP / NIK)</label>
                <input type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required placeholder="Contoh: 20241001 atau 19850101..." class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none @error('nomor_induk') border-red-500 @enderror">
                <span class="text-[11px] text-slate-400 block mt-1">Gunakan NIS (Siswa) atau NIP (Guru/Karyawan)</span>
                @error('nomor_induk')
                <p class="text-[11px] text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama Lengkap Anda" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                @error('name')
                <p class="text-[11px] text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP Aktif</label>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" required placeholder="081234567890" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                <span class="text-[11px] text-slate-400 block mt-1">Untuk konfirmasi darurat oleh petugas PMR</span>
                @error('phone_number')
                <p class="text-[11px] text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                @error('password')
                <p class="text-[11px] text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi password di atas" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
            </div>

            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-500 space-y-1">
                <p class="font-bold text-slate-700">ℹ️ Informasi Anggota PMR:</p>
                <p>Akun resmi Petugas PMR didaftarkan oleh Pembina / Administrator PMR sekolah.</p>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm transition-colors">
                Daftar Akun Sekarang
            </button>
        </form>

        <p class="text-center text-xs text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-red-600 hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
