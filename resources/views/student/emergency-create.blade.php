@extends('layouts.app')

@section('title', 'Lapor Emergency — Siap Siaga PMR')

@section('content')
<div class="max-w-xl mx-auto px-4 py-5">
    <!-- Header Back -->
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Dashboard
        </a>
        <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-[11px] font-extrabold uppercase">
            Form Darurat
        </span>
    </div>

    <div class="bg-white rounded-xl p-5 sm:p-6 border border-slate-200 space-y-5">
        <!-- Safety Alert Note -->
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 flex items-start gap-3">
            <div class="w-7 h-7 rounded-lg bg-red-600 text-white flex items-center justify-center shrink-0 text-sm font-bold">
                ⚠️
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-red-900">Konfirmasi Darurat</h4>
                <p class="text-xs text-red-700 mt-0.5 leading-relaxed">
                    Sistem akan langsung mendispatch anggota PMR terdekat ke lokasi yang Anda tentukan. Pastikan informasi akurat.
                </p>
            </div>
        </div>

        <form action="{{ route('student.emergency.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- 1. Jenis Kejadian -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    1. Jenis Kejadian Medis <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @php
                        $types = [
                            'cedera'     => ['icon' => '🩹', 'label' => 'Cedera / Luka'],
                            'pingsan'    => ['icon' => '😵', 'label' => 'Pingsan'],
                            'sakit'      => ['icon' => '🤒', 'label' => 'Sakit / Lemas'],
                            'kecelakaan' => ['icon' => '🚨', 'label' => 'Kecelakaan'],
                            'lainnya'    => ['icon' => '❓', 'label' => 'Lainnya'],
                        ];
                    @endphp

                    @foreach($types as $key => $item)
                    <label class="relative flex flex-col items-center justify-center p-3 rounded-lg border-2 border-slate-200 hover:border-red-500 cursor-pointer text-center transition-colors has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                        <input type="radio" name="incident_type" value="{{ $key }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                        <span class="text-2xl mb-1">{{ $item['icon'] }}</span>
                        <span class="text-xs font-bold text-slate-800">{{ $item['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- 2. Lokasi Kejadian -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    2. Lokasi Kejadian <span class="text-red-500">*</span>
                </label>

                <select name="location_id" id="location_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Titik Lokasi Sekolah --</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->building ?? 'Area Sekolah' }})</option>
                    @endforeach
                    <option value="custom">📍 Lokasi Lainnya / Manual</option>
                </select>

                <!-- Custom location name input -->
                <div id="custom_location_wrapper" class="mt-2 hidden">
                    <input type="text" name="custom_location_name" placeholder="Tuliskan nama lokasi spesifik (misal: Tangga Gedung D lt 3)" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <!-- GPS Status -->
                <div class="mt-2 flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <div class="flex items-center gap-2">
                        <span id="gps_indicator" class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                        <span id="gps_status_text" class="font-bold text-slate-700">Mendeteksi titik GPS perangkat...</span>
                    </div>
                    <button type="button" onclick="detectGPS()" class="px-2 py-1 rounded-md bg-slate-200 hover:bg-slate-300 font-bold text-[11px] text-slate-700 transition-colors">
                        🔄 Refresh GPS
                    </button>
                    <input type="hidden" name="latitude" id="input_latitude" value="">
                    <input type="hidden" name="longitude" id="input_longitude" value="">
                </div>
            </div>

            <!-- 3. Deskripsi Singkat -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    3. Keterangan Singkat / Kondisi Korban (Opsional)
                </label>
                <textarea name="description" rows="2" placeholder="Contoh: Korban tidak sadarkan diri, butuh tandu dan pertolongan segera..." class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-base transition-colors flex items-center justify-center gap-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <span>KIRIM LAPORAN SEKARANG</span>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('location_id').addEventListener('change', function() {
        const customWrapper = document.getElementById('custom_location_wrapper');
        if (this.value === 'custom') {
            customWrapper.classList.remove('hidden');
        } else {
            customWrapper.classList.add('hidden');
        }
    });

    function detectGPS() {
        const indicator = document.getElementById('gps_indicator');
        const statusText = document.getElementById('gps_status_text');
        const inputLat = document.getElementById('input_latitude');
        const inputLng = document.getElementById('input_longitude');

        indicator.classList.remove('bg-emerald-500', 'bg-red-500');
        indicator.classList.add('bg-amber-500');
        statusText.textContent = 'Mendeteksi GPS...';

        if (!navigator.geolocation) {
            statusText.textContent = 'GPS tidak didukung di perangkat ini.';
            indicator.classList.replace('bg-amber-500', 'bg-red-500');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                inputLat.value = pos.coords.latitude;
                inputLng.value = pos.coords.longitude;
                indicator.classList.replace('bg-amber-500', 'bg-emerald-500');
                statusText.textContent = `GPS aktif (±${Math.round(pos.coords.accuracy)}m akurasi)`;
            },
            () => {
                indicator.classList.replace('bg-amber-500', 'bg-red-500');
                statusText.textContent = 'GPS tidak dapat diakses. Pilih lokasi manual.';
            },
            { timeout: 8000, enableHighAccuracy: true }
        );
    }

    // Auto-detect GPS on load
    detectGPS();
</script>
@endpush
@endsection
