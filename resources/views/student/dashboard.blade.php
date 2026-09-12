@extends('layouts.app')

@section('title', 'Dashboard Siswa — Siap Siaga PMR')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-5 space-y-5">

    <!-- User Greeting Banner -->
    <div class="flex items-center justify-between bg-white p-4 rounded-xl border border-slate-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-600 text-white font-extrabold flex items-center justify-center text-base">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="font-bold text-base text-slate-900">Halo, {{ Auth::user()->name }}</h1>
                <p class="text-xs text-slate-500">Butuh bantuan medis darurat?</p>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            PMR Siap Siaga
        </span>
    </div>

    <!-- EMERGENCY Trigger Card -->
    <div class="bg-red-600 rounded-xl p-5 sm:p-6 text-white">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
            <div class="text-center sm:text-left space-y-1">
                <span class="inline-block px-2 py-0.5 bg-white/20 rounded text-[11px] font-extrabold uppercase tracking-wider">
                    Tombol Tanggap Cepat
                </span>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">Terjadi Keadaan Darurat?</h2>
                <p class="text-red-100 text-xs sm:text-sm max-w-md">
                    Tekan tombol di samping untuk segera memanggil anggota PMR yang bertugas dan tersedia.
                </p>
            </div>

            <a href="{{ route('student.emergency.create') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-white hover:bg-slate-50 text-red-600 font-black text-base sm:text-lg flex items-center justify-center gap-2.5 transition-colors shrink-0 border-2 border-white/80">
                <span>🚨 EMERGENCY</span>
            </a>
        </div>
    </div>

    <!-- Active Emergency Tracker -->
    @if($activeEmergencies->count() > 0)
    <div class="space-y-3">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-2">
            <span class="w-2 h-2 rounded-sm bg-red-600 inline-block"></span>
            Laporan Darurat Aktif Anda
        </h3>

        @foreach($activeEmergencies as $emg)
        <a href="{{ route('student.emergency.show', $emg->id) }}" class="block bg-white p-4 rounded-xl border-2 border-red-200 hover:border-red-400 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs font-extrabold uppercase tracking-wider">
                            {{ $emg->incident_type }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono">#{{ $emg->emergency_code }}</span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900 mt-1.5">{{ $emg->location_display }}</h4>
                    <p class="text-xs text-slate-600 mt-0.5 line-clamp-1">{{ $emg->description ?? 'Tidak ada catatan tambahan.' }}</p>
                </div>

                <div class="text-right shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                        {{ strtoupper(str_replace('_', ' ', $emg->status)) }}
                    </span>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">{{ $emg->reported_at->diffForHumans() }}</p>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span class="font-medium text-slate-800">
                    Petugas: <strong>{{ $emg->activeAssignment?->pmrUser?->name ?? 'Mencari Petugas PMR...' }}</strong>
                </span>
                <span class="font-bold text-red-600">Lihat Live Status →</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <!-- Recent History -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Riwayat Laporan Sebelumnya</h3>
            <span class="text-xs text-slate-400">{{ $recentEmergencies->count() }} Laporan</span>
        </div>

        @forelse($recentEmergencies as $history)
        <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm">
                    🩹
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">{{ ucfirst($history->incident_type) }} — {{ $history->location_display }}</h4>
                    <p class="text-xs text-slate-500">{{ $history->reported_at->format('d M Y, H:i') }} • <span class="text-emerald-600 font-semibold">{{ ucfirst($history->status) }}</span></p>
                </div>
            </div>
            <a href="{{ route('student.emergency.show', $history->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors shrink-0">
                Detail
            </a>
        </div>
        @empty
        <div class="bg-white p-8 rounded-xl border border-dashed border-slate-200 text-center">
            <p class="text-sm text-slate-400">Belum ada riwayat laporan darurat.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
