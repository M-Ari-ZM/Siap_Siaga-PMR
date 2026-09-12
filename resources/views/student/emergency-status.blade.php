@extends('layouts.app')

@section('title', 'Status Emergency #' . $emergency->emergency_code . ' — Siap Siaga PMR')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-5 space-y-5">
    @php
        $backUrl = route('student.dashboard');
        if (Auth::check()) {
            if (Auth::user()->role === 'pmr') {
                $backUrl = route('pmr.dashboard');
            } elseif (Auth::user()->role === 'admin') {
                $backUrl = route('admin.dashboard');
            }
        }
    @endphp

    <!-- Top Header -->
    <div class="flex items-center justify-between">
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-slate-400">#{{ $emergency->emergency_code }}</span>
            <span class="px-2 py-0.5 rounded text-xs font-extrabold bg-red-100 text-red-700 uppercase">
                {{ strtoupper(str_replace('_', ' ', $emergency->status)) }}
            </span>
        </div>
    </div>

    <!-- Live Status Banner -->
    <div class="bg-slate-900 rounded-xl p-5 text-white space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-sm bg-emerald-400 inline-block"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Live Status Pelacakan</span>
            </div>
            <span class="text-xs text-slate-400">{{ $emergency->reported_at->format('H:i') }} WIB</span>
        </div>

        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                @if($emergency->status === 'reported' || $emergency->status === 'searching_pmr')
                    🔍 Sedang Menghubungi Petugas PMR Terdekat...
                @elseif($emergency->status === 'pmr_assigned')
                    🚑 Petugas PMR Telah Ditugaskan
                @elseif($emergency->status === 'on_the_way')
                    🏃 Petugas PMR Sedang Menuju Lokasi Anda
                @elseif($emergency->status === 'handling')
                    🩹 Sedang Dalam Penanganan Medis P3K
                @elseif($emergency->status === 'resolved')
                    ✅ Penanganan Telah Selesai
                @else
                    ℹ️ Status: {{ ucfirst($emergency->status) }}
                @endif
            </h2>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">
                Lokasi: <strong class="text-white">{{ $emergency->location_display }}</strong> • Jenis: <strong class="text-white uppercase">{{ $emergency->incident_type }}</strong>
            </p>
        </div>

        <!-- Assigned PMR Member Card -->
        @if($emergency->activeAssignment && $emergency->activeAssignment->pmrUser)
        <div class="p-4 rounded-lg bg-white/10 border border-white/15 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-600 text-white font-black flex items-center justify-center text-xs">
                    PMR
                </div>
                <div>
                    <h4 class="font-bold text-sm text-white">{{ $emergency->activeAssignment->pmrUser->name }}</h4>
                    <p class="text-[11px] text-slate-300">{{ $emergency->activeAssignment->pmrUser->pmrProfile->class_grade ?? 'Petugas PMR' }} • Jarak ~{{ $emergency->activeAssignment->distance_meters ?? '40' }} meter</p>
                </div>
            </div>
            <a href="tel:{{ $emergency->activeAssignment->pmrUser->phone_number }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1.5 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>Hubungi</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Map -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">
                🗺️ Peta Lokasi Kejadian & Petugas PMR
            </h3>
            <span class="text-[11px] font-bold text-slate-400">OpenStreetMap</span>
        </div>

        <div id="emergency_map" class="w-full h-64 rounded-lg border border-slate-200 overflow-hidden"></div>

        <div class="flex items-center justify-around text-[11px] font-bold text-slate-600">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-red-600 inline-block"></span> Lokasi Kejadian</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-600 inline-block"></span> Posisi PMR (~{{ $emergency->activeAssignment?->distance_meters ?? '45' }}m)</span>
        </div>
    </div>

    <!-- Timeline Tracking -->
    <div class="bg-white rounded-xl p-5 border border-slate-200 space-y-4">
        <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">
            Riwayat Kronologi Penanganan
        </h3>

        <div class="space-y-5 relative before:absolute before:inset-0 before:left-3.5 before:w-px before:bg-slate-200">
            @forelse($emergency->timelines as $timeline)
            <div class="relative flex items-start gap-4">
                <div class="w-7 h-7 rounded-lg bg-red-600 text-white font-bold flex items-center justify-center shrink-0 z-10 text-xs">
                    ✓
                </div>
                <div class="flex-1 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-slate-900">{{ $timeline->title }}</h4>
                        <span class="text-[11px] font-mono text-slate-400">{{ $timeline->created_at->format('H:i') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-0.5">{{ $timeline->description }}</p>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 italic">Belum ada pembaruan aktivitas timeline.</p>
            @endforelse
        </div>
    </div>

    <!-- Handling Report Card (If Resolved) -->
    @if($emergency->handlingReport)
    <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-200 space-y-3">
        <div class="flex items-center gap-2 text-emerald-800 font-extrabold text-sm uppercase border-b border-emerald-200 pb-3">
            <span>📋 Laporan Penanganan Medis PMR</span>
        </div>
        <div class="space-y-3 text-xs text-emerald-950">
            <div>
                <span class="font-bold">Tindakan Dilakukan:</span>
                <p class="text-slate-700 bg-white p-2.5 rounded-lg border border-emerald-200 mt-1">{{ $emergency->handlingReport->action_taken }}</p>
            </div>
            <div>
                <span class="font-bold">Catatan Akhir:</span>
                <p class="text-slate-700 bg-white p-2.5 rounded-lg border border-emerald-200 mt-1">{{ $emergency->handlingReport->notes ?? 'Tidak ada catatan tambahan.' }}</p>
            </div>
            <div>
                <span class="font-bold">Kondisi Terakhir Korban:</span>
                <span class="inline-block px-2 py-0.5 rounded bg-emerald-200 text-emerald-900 font-bold ml-1">{{ $emergency->handlingReport->final_condition }}</span>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    const emgLat = {{ $emergency->latitude ?? -6.20000000 }};
    const emgLng = {{ $emergency->longitude ?? 106.81666600 }};
    const locName = "{{ $emergency->location_display }}";

    const pmrLat = {{ $emergency->activeAssignment?->pmrUser?->pmrProfile?->current_latitude ?? ($emergency->latitude ? $emergency->latitude + 0.0003 : -6.19970000) }};
    const pmrLng = {{ $emergency->activeAssignment?->pmrUser?->pmrProfile?->current_longitude ?? ($emergency->longitude ? $emergency->longitude + 0.0003 : 106.81696600) }};
    const pmrName = "{{ $emergency->activeAssignment?->pmrUser?->name ?? 'Petugas PMR' }}";

    const map = L.map('emergency_map').setView([emgLat, emgLng], 18);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const emergencyIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color:#dc2626; width:16px; height:16px; border-radius:3px; border:2px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });
    L.marker([emgLat, emgLng], { icon: emergencyIcon }).addTo(map)
        .bindPopup(`<strong>🚨 Kejadian: {{ ucfirst($emergency->incident_type) }}</strong><br>${locName}`)
        .openPopup();

    const pmrIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color:#16a34a; width:16px; height:16px; border-radius:3px; border:2px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });
    L.marker([pmrLat, pmrLng], { icon: pmrIcon }).addTo(map)
        .bindPopup(`<strong>🩹 ${pmrName}</strong><br>Status: Menuju Lokasi (~{{ $emergency->activeAssignment?->distance_meters ?? '45' }}m)`);

    L.polyline([[emgLat, emgLng], [pmrLat, pmrLng]], {
        color: '#dc2626', weight: 2, dashArray: '5, 7', opacity: 0.7
    }).addTo(map);

    map.fitBounds([[emgLat, emgLng], [pmrLat, pmrLng]], { padding: [40, 40] });

    // Auto-Polling Status
    const currentStatus = "{{ $emergency->status }}";
    const pollUrl = "{{ route('emergency.live-status', $emergency->id) }}";

    if (currentStatus !== 'resolved' && currentStatus !== 'cancelled') {
        setInterval(async () => {
            try {
                const response = await fetch(pollUrl, { headers: { 'Accept': 'application/json' } });
                if (response.ok) {
                    const data = await response.json();
                    if (data.status !== currentStatus) window.location.reload();
                }
            } catch (err) {
                console.warn('Polling error:', err);
            }
        }, 3000);
    }
</script>
@endpush
@endsection
