@extends('layouts.app')

@section('title', 'Info & Panduan — Siap Siaga PMR')

@section('content')
<div class="relative overflow-hidden pt-6 pb-16">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold mb-4 animate-bounce">
                <span class="w-2 h-2 rounded-full bg-red-600"></span>
                Tanggap Darurat Sekolah Cepat & Terorganisir
            </div>
            
            <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Bantuan PMR, <span class="text-red-600">Lebih Cepat</span> Saat Sangat Dibutuhkan.
            </h1>
            
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
                Laporkan kejadian medis & darurat di lingkungan sekolah dalam hitungan detik. Terhubung langsung dengan anggota Palang Merah Remaja (PMR) terdekat dan siap siaga.
            </p>

            <!-- Quick Action Triggers -->
            <!-- <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('student.emergency.create') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-red-600 hover:bg-red-700 active:scale-95 text-white font-extrabold text-base shadow-xl shadow-red-500/30 flex items-center justify-center gap-3 transition-all">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>KIRIM EMERGENCY SEKARANG</span>
                </a>

                <a href="{{ route('student.dashboard') }}" class="w-full sm:w-auto px-6 py-4 rounded-2xl bg-white hover:bg-slate-100 text-slate-800 font-bold text-sm border border-slate-200 shadow-xs flex items-center justify-center gap-2 transition-all">
                    <span>Masuk Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div> -->
        </div>

        <!-- Role Preview Cards Demo -->
        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Student -->
            <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold text-xl mb-4">
                    🚨
                </div>
                <h3 class="font-extrabold text-lg text-slate-900">1. Pelaporan Siswa</h3>
                <p class="text-sm text-slate-600 mt-2">Kirim sinyal SOS, deteksi lokasi otomatis via GPS / pilih lokasi sekolah (Lapangan, UKS, Kantin, dll).</p>
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-red-600 mt-4 hover:underline">
                    Buka Dashboard Siswa &rarr;
                </a>
            </div>

            <!-- Card 2: PMR Member -->
            <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl mb-4">
                    🚑
                </div>
                <h3 class="font-extrabold text-lg text-slate-900">2. Respon Cepat PMR</h3>
                <p class="text-sm text-slate-600 mt-2">Anggota PMR menerima notifikasi, melihat jarak, menerima tiket, dan memperbarui status penanganan secara live.</p>
                <a href="{{ route('pmr.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 mt-4 hover:underline">
                    Buka Dashboard PMR &rarr;
                </a>
            </div>

            <!-- Card 3: Admin & Logs -->
            <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-all">
                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl mb-4">
                    📊
                </div>
                <h3 class="font-extrabold text-lg text-slate-900">3. Monitoring & Rekap</h3>
                <p class="text-sm text-slate-600 mt-2">Pantau seluruh kejadian aktif secara terpusat, riwayat laporan tindakan P3K, dan data statistik evaluasi.</p>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 mt-4 hover:underline">
                    Buka Panel Admin &rarr;
                </a>
            </div>
        </div>

        <!-- How It Works Steps -->
        <div class="mt-20">
            <h2 class="text-center text-xs font-extrabold uppercase tracking-widest text-slate-400">Alur Tanggap Darurat</h2>
            <p class="text-center font-bold text-2xl text-slate-800 mt-1">Hanya 3 Langkah Cepat</p>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-slate-900 text-white font-extrabold flex items-center justify-center shrink-0 text-sm">
                        1
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">Laporkan Kejadian</h4>
                        <p class="text-xs text-slate-600 mt-1">Pilih jenis kejadian (pingsan, cedera, kecelakaan) dan tentukan lokasi.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-slate-900 text-white font-extrabold flex items-center justify-center shrink-0 text-sm">
                        2
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">Sistem Dispatch PMR</h4>
                        <p class="text-xs text-slate-600 mt-1">Sistem mencari anggota PMR terdekat yang sedang bertugas & tersedia.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-slate-900 text-white font-extrabold flex items-center justify-center shrink-0 text-sm">
                        3
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">Bantuan Tiba & Tertangani</h4>
                        <p class="text-xs text-slate-600 mt-1">Pantau pergerakan PMR dan dapatkan penanganan medis P3K segera.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
