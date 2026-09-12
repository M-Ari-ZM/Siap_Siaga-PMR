@extends('layouts.app')

@section('title', 'Laporan Penanganan Medis #' . $emergency->emergency_code . ' — Siap Siaga PMR')

@section('content')
<div class="max-w-xl mx-auto px-4 py-5">
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('pmr.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Dashboard PMR
        </a>
        <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 text-[11px] font-extrabold uppercase">
            Form Laporan Penanganan
        </span>
    </div>

    <div class="bg-white rounded-xl p-5 sm:p-6 border border-slate-200 space-y-5">
        <!-- Emergency Reference Card -->
        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-bold text-slate-400">#{{ $emergency->emergency_code }}</span>
                <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 text-[11px] font-extrabold uppercase">{{ $emergency->incident_type }}</span>
            </div>
            <h3 class="font-bold text-slate-900 text-sm">Lokasi: {{ $emergency->location_display }}</h3>
            <p class="text-xs text-slate-500">Korban / Pelapor: {{ $emergency->reporter->name }}</p>
        </div>

        <form action="{{ route('pmr.emergency.report.store', $emergency->id) }}" method="POST" class="space-y-5">
            @csrf

            <!-- 1. Tindakan -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    1. Tindakan Medis / P3K yang Diberikan <span class="text-red-500">*</span>
                </label>
                <textarea name="action_taken" rows="3" required placeholder="Contoh: Diberikan kompres dingin, pembalutan elastic bandage pada pergelangan kaki, diistirahatkan di UKS..." class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>

            <!-- 2. Catatan -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    2. Catatan Tambahan / Keterangan Medis
                </label>
                <textarea name="notes" rows="2" placeholder="Contoh: Pembengkakan sudah berkurang, korban sudah bisa berjalan perlahan..." class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
            </div>

            <!-- 3. Status Akhir Korban -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    3. Kondisi Akhir Korban <span class="text-red-500">*</span>
                </label>
                <select name="final_condition" required class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-bold bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="Istirahat di Ruang UKS">Istirahat di Ruang UKS</option>
                    <option value="Kembali ke Kelas (Pulih)">Kembali ke Kelas (Pulih)</option>
                    <option value="Dijemput Orang Tua / Pulang">Dijemput Orang Tua / Pulang</option>
                    <option value="Dirujuk ke Puskesmas / Rumah Sakit">Dirujuk ke Puskesmas / Rumah Sakit</option>
                </select>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>SIMPAN LAPORAN & RESOLVE EMERGENCY</span>
            </button>
        </form>
    </div>
</div>
@endsection
